<?php 

namespace Canpar;

class Estimate {

    public $errors = array();
	public $services = array();
   	public $shippingDate = null;
   	public $pickupAddress = array();
    public $deliveryAddress = array();
    public $pieces = array();

    private $incomingData;
    private $soap;
    private $availableServices = array();


	public function __construct($incomingData = '') {
		$this->incomingData = $incomingData;
		$this->soap = $this->createPWSSOAPClient();
        $this->shippingDate = $this->setShippingDate();
        $this->deliveryAddress = $this->setPickupAddress();
        $this->pickupAddress = $this->setDeliveryAddress();
        $this->pieces = $this->setPieces();
	}


    public function getServicesWithRates() {
        $this->availableServices = $this->getAvailableServices();
        $this->services = $this->calculateShipping();

        return $this->services;
    }


	public function getCheapestRate() {

        $this->availableServices = $this->getAvailableServices();
        $this->services = $this->calculateShipping();

       	if(count($this->services) > 0) {
            $rate = $this->compareShippingRates($this->services);
        }

        return !empty($rate) ? $rate : 0;
    }



	private function createPWSSOAPClient() {
        $client = null;
        $SOAP_OPTIONS = array(
            'soap_version' => SOAP_1_2,
            'exceptions' => false,
            'trace' => 1,
			'connection_timeout'=> 5,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS
        );

		try{
			$client = new \SoapClient(CANPAR_RATING_URL, $SOAP_OPTIONS);
        } catch(SoapFault $e){
            $this->errors[] =$e;
		}
        return $client;
	}


    private function setShippingDate ($leadTime = '0') {
        // Get the current date, plus lead time specified
        $time = strtotime("+" . $leadTime . " weekdays");
        return date('Y-m-d\T00:00:00.000\Z', $time);

    }


    private function setDeliveryAddress() {

        $addr = array('country' => 'CA', 'name' => 'Canada');
        $addr['address_line_1'] = Common::fixAccents($this->incomingData['receiverStreetNumber'] . ' ' . $this->incomingData['receiverStreetName']);
        $addr['city'] = Common::fixAccents($this->incomingData['receiverCity']);
        $addr['postal_code'] = str_replace(' ' , '', $this->incomingData['receiverPostalCode']);
        $addr['province'] = $this->incomingData['receiverProvince'];  

        return $addr;
    }


    private function setPickupAddress() {

        $addr = array('country' => 'CA', 'name' => 'Canada');
    	$addr['address_line_1'] = Common::fixAccents($this->incomingData['senderStreetNumber'] . ' ' . $this->incomingData['senderStreetName']);
    	$addr['city'] = Common::fixAccents($this->incomingData['senderCity']);
        $addr['postal_code'] = str_replace(' ', '', $this->incomingData['senderPostalCode']);
        $addr['province'] = $this->incomingData['senderProvince'];

        return $addr;	
    }


    public function setPieces() {
        $pieces = array();
    	foreach($this->incomingData['packages'] as $package){

			$weight = round($package['weight'], 0) < 1 ? 1 : $package['weight'];
			$pieces[] = array(
    						'height' => $package['height'],
    						'width' =>  $package['width'],
    						'length' => $package['length'],
    						'reported_weight' => $weight);
		}

		return $pieces;
    }


    private function getAvailableServices() {

        // Get the available services
        $request = array(
            'delivery_country'    => 'CA',
            'shipping_date'       => $this->shippingDate,
            'delivery_postal_code'=> $this->incomingData['receiverPostalCode'],
            'pickup_postal_code'  => $this->incomingData['senderPostalCode'],
            'password'            => CANPAR_PASSWORD,
            'shipper_num'         => CANPAR_SHIPPER_NUMBER,
            'user_id'             => CANPAR_USER_ID
        );

        // Execute the request
        $result = $this->soap->getAvailableServices(array('request'=>$request));
        return $result->return->getAvailableServicesResult;
    }


    private function compareShippingRates($rates = array()) {
		
        //Remove charges equal to 0
        for($i=0; $i < count($rates); $i++) {
            $rates[$i]['charge'] = !empty($rates[$i]['charge']) ? (float) $rates[$i]['charge'] : 0;
            if(empty($rates[$i]['charge'])) { unset($rates[$i]); }
        }
        $rates = array_values($rates);

        if(count($rates) < 2) {
            $rates[0]['charge'];
        }

        // Obtain a list of columns
        $charges = array();
        foreach ($rates as $key => $row) {
            $charges[$key]  = $row['charge'];
        }

        // Sort the data in ascending order
        array_multisort($charges, SORT_ASC, $rates);

        return $rates[0]['charge'];
    }


    private function calculateShipping() {

        $servicesWithRates = array();

        // Get the rate for each service
        foreach ($this->availableServices as $service) {

            $request = $this->createShipmentRequest($service->type);
            $result = $this->soap->rateShipment(array('request'=>$request));

            //If there is an Error get the next Service
            if($this->isError($result)) {
                continue;
            }

            $rate = $result->return->processShipmentResult->shipment;

            // Convert the rate object to an array for processing
            $rate = json_decode(json_encode($rate), true);

            // Calculate the totals
            $totalCharge = 0;
            foreach ($rate AS $index=>$value) {
				//Find charges, exclude taxes
				if ((strpos($index, '_charge') || strpos($index, 'surcharge')) && strpos($index, 'tax_') === false) {
					$totalCharge += (float) $value;
				}
            }

            $totalCharge = round($totalCharge, 2);

            
            $servicesWithRates[] = array(
                'service_type' => $service->type,
                'service_name' => Shipment::getServiceNameById($service->type), 
                'charge' => $totalCharge + CANPAR_RESIDENTIAL_CHARGES + CANPAR_FUEL_SURCHARGE //Add Residential and Fuel Surcharges
            );
        } // end foreach

        return $servicesWithRates;
    }


    private function createShipmentRequest($serviceType) {
        // Build the shipment
        $shipment = array(
            'packages' => $this->pieces,
            'pickup_address' => $this->pickupAddress,
            'delivery_address' => $this->deliveryAddress,
            'service_type' => $serviceType,
            'shipping_date' => $this->shippingDate,
            'shipper_num' => CANPAR_SHIPPER_NUMBER,
            'user_id' => CANPAR_USER_ID,
            'dg' => 0, // Dangerous Goods
            'dimention_unit' => 'C', // C/I
            'reported_weight_unit' => 'K', // K/L.
            'cod_type' => 'N',
            'collect' => 0,
            'nsr' => 0
        );

        // Build the request
        $request = array(
            'apply_association_discount' => CANPAR_APPLY_DISCOUNTS,
            'apply_individual_discount' => CANPAR_APPLY_DISCOUNTS,
            'apply_invoice_discount' => CANPAR_APPLY_DISCOUNTS,
            'password' => CANPAR_PASSWORD,
            'user_id' => CANPAR_USER_ID,
            'shipment' => $shipment
        );

        return $request;
    }


   	private function isError($result) {

        $this->errors = array();

        if(!empty($result->return->error)) {
            $this->errors[] = $result->return->error;

        } elseif( isset($result->return->processShipmentResult->errors[0]) && !empty($result->return->processShipmentResult->errors[0])) {
            $this->errors[] = $result->return->processShipmentResult->errors[0];
        }

        return count($this->errors) > 0;
    }
}