<?php 

namespace Canpar;

class Estimate {

    public $errors = array();
	public $services = array();

	private $incomingData;
	private $soap;
	private $availableServiceObjects = array();

    public $residentialCharges = CANPAR_RESIDENTIAL_CHARGES;
    public $fuelSurcharge = CANPAR_FUEL_SURCHARGE;
    public $applyDiscounts = CANPAR_APPLY_DISCOUNTS;
   	public $shippingDate = null;
   	public $pickupAddress = array();
    public $deliveryAddress = array();
    public $pieces = array();

    public $debug  = false;


	public function __construct($incomingData = '') {
		$this->incomingData = $incomingData;
		$this->soap = $this->createPWSSOAPClient();

        $this->setShippingDate();
        $this->setPickupAddress();
        $this->setDeliveryAddress();
        $this->setPieces();
	}


    public function getServicesWithRates() {
    	if($this->debug) {echo '<p>In getServicesWithRates()</p>';}

        $this->getAvailableServices();
        $this->calculateShipping();

        return $this->services;
    }



	public function getCheapestRate() {
        $rate = 0;

        $this->getAvailableServices();
        $this->calculateShipping();

       	if(count($this->services) > 0) {
            $rate = $this->compareShippingRates();
        }

        return $rate;
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
            echo $e;
            exit;
		}
        return $client;
	}


    private function setShippingDate ($lead_time = '0') {
        // Get the current date, plus lead time specified
        $time = strtotime("+" . $lead_time . " weekdays");
        $this->shippingDate = date('Y-m-d\T00:00:00.000\Z', $time);

        return $this->shippingDate;
    }


    private function setDeliveryAddress() {
    	$addressLine = sanitize($this->incomingData['receiverStreetNumber'] . ' ' . $this->incomingData['receiverStreetName']);

        $this->deliveryAddress['address_line_1'] = $addressLine;
        $this->deliveryAddress['city'] = sanitize($this->incomingData['receiverCity']);
        $this->deliveryAddress['country'] = 'CA';
        $this->deliveryAddress['name'] = 'Canada';
        $this->deliveryAddress['postal_code'] = str_replace(' ' , '', $this->incomingData['receiverPostalCode']);
        $this->deliveryAddress['province'] = $this->incomingData['receiverProvince'];  

        return $this->deliveryAddress;
    }


    private function setPickupAddress() {
    	$addressLine = sanitize($this->incomingData['senderStreetNumber'] . ' ' . $this->incomingData['senderStreetName']);

    	$this->pickupAddress['address_line_1'] = $addressLine;
    	$this->pickupAddress['city'] = $this->incomingData['senderCity'];
    	$this->pickupAddress['country'] = "CA";
        $this->pickupAddress['name'] = "Canada";
        $this->pickupAddress['postal_code'] = str_replace(' ', '', $this->incomingData['senderPostalCode']);
        $this->pickupAddress['province'] = $this->incomingData['senderProvince'];

        return $this->pickupAddress;	
    }


    public function setPieces() {
    	$counter = 0;
    	foreach($this->incomingData['packages'] as $package){

			$weight = round($package['weight'], 0) < 1 ? 1 : $package['weight'];
			$this->pieces[] = array(
						'height' => $package['height'],
						'width' =>  $package['width'],
						'length' => $package['length'],
						'reported_weight' => $weight);
		}

		return $this->pieces;
    }


    private function getAvailableServices() {
    	if($this->debug) {echo '<p>In getAvailableServices()</p>';}

        // Get the available services
        $request = array(
            'delivery_country'    => 'CA',
            'delivery_postal_code'=> $this->incomingData['receiverPostalCode'],
            'password'            => CANPAR_PASSWORD,
            'pickup_postal_code'  => $this->incomingData['senderPostalCode'],
            'shipper_num'         => CANPAR_SHIPPER_NUMBER,
            'shipping_date'       => $this->shippingDate,
            'user_id'             => CANPAR_USER_ID
        );
    	if($this->debug) {echo '<pre>', print_r($request), '</pre>';}

        // Execute the request
        $result = $this->soap->getAvailableServices(array('request'=>$request));
        if($this->debug) {echo '<pre>', var_dump($result), '</pre>';}

        $this->availableServiceObjects = $result->return->getAvailableServicesResult;
        if($this->debug) {echo '<pre>', print_r($this->availableServiceObjects), '</pre>';}

        
        return $this->availableServiceObjects;
    }


    private function compareShippingRates() {

		$ratesArray = $this->services;
		
        //Remove charges equal to 0
        for($i=0; $i < count($ratesArray); $i++) {
            $ratesArray[$i]['charge'] = !empty($ratesArray[$i]['charge']) ? (float) $ratesArray[$i]['charge'] : 0;
            if(empty($ratesArray[$i]['charge'])) { unset($ratesArray[$i]); }
        }
        $ratesArray = array_values($ratesArray);


        if(count($ratesArray) > 1) {

            // Obtain a list of columns
            foreach ($ratesArray as $key => $row) {
                $charge[$key]  = $row['charge'];
            }

            // Sort the data in ascending order
            array_multisort($charge, SORT_ASC, $ratesArray);
        }

        return $ratesArray[0]['charge'];
    }


    private function calculateShipping () {

        // Get the rate for each service
        foreach ($this->availableServiceObjects as $service) {

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
            $total_charge = 0;
            foreach ($rate AS $index=>$value) {
				//Find charges, exclude taxes
				if ((strpos($index, '_charge') || strpos($index, 'surcharge')) && strpos($index, 'tax_') === false) {
					$total_charge += (float) $value;
				}
            }

            $total_charge = round($total_charge,2);
            
            $this->services[] = array(
                'service_type' => $service->type,
                'service_name' => getServiceNameById($service->type), 
                'charge' => $total_charge + $this->ResidentialCharges + $this->FuelSurcharge//Add Residential Charges
            );
        } // end foreach

        return $this->services;
    }


    private function createShipmentRequest($serviceType) {
        // Build the shipment
        $shipment = array(
            'delivery_address' => $this->deliveryAddress,
            'dg' => 0, // Dangerous Goods
            'dimention_unit' => 'C', // C/I
            'packages' => $this->pieces,
            'pickup_address' => $this->pickupAddress,
            'reported_weight_unit' => 'K', // K/L.
            'service_type' => $serviceType,
            'shipper_num' => CANPAR_SHIPPER_NUMBER,
            'shipping_date' => $this->shippingDate,
            'user_id' => CANPAR_USER_ID,
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

        $error = false;
        if(!empty($result->return->error)) {
            $this->errors[] = $result->return->error;
            $error = true;

        } elseif( isset($result->return->processShipmentResult->errors[0]) && !empty($result->return->processShipmentResult->errors[0])) {
            $this->errors[] = $result->return->processShipmentResult->errors[0];
            $error = true;
        }

        return $error;
    }
}