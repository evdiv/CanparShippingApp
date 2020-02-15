<?php 

namespace Canpar;

class Shipment {

	private $db;

	private $incomingData;
	private $soap;
	private $request;
	private $response;	

	public $labels = array();
	public $voided = '';  

	public $manifest_num = '';
	public $manifest = '';	

	public $errors = array();


	public function __construct($incomingData = '') {

		$this->db = new Database();

		$this->incomingData = $incomingData;
		$this->soap = $this->createClient();

		$this->request = new \stdClass();
		$this->response = new \stdClass();
	}


	// Create new Shipment 
	public function create() {

		$this->populateRequest();

		try {
			$this->response = $this->soap->processShipment( new \SoapVar($this->request, SOAP_ENC_OBJECT) );

		} catch(Exception $e) {
			$this->errors[] = "Exception:" . $e;
		}

		$this->getErrors();
	}


	// Return labels based on shipment details
	public function getLabels() {

		$request = $this->response->processShipmentResult->shipment;
		$request->password = CANPAR_PASSWORD;
		$request->thermal = 1;

		try {
			$response = $this->soap->getLabels($request);

			//Store Labels on server
			$this->labels = $this->extractFiles($response->labels);

		} catch (Exception $e) {
			$this->errors[] = "Exception:" . $e;
		}

		$this->getErrors($response);
	}


	//Store Shipments in the DB
	public function store() {

		if(empty($this->response->processShipmentResult->shipment)) {
			return;
		}

		$orderID = !empty($this->incomingData['orderID']) ? $this->incomingData['orderID'] : 0;
		$locationID = !empty($this->incomingData['senderLocationID']) ? $this->incomingData['senderLocationID'] : 0;
		$shipment = $this->response->processShipmentResult->shipment;
		$courierService = self::getServiceNameById($shipment->service_type);


		foreach ($shipment->packages as $key => $package) {
			
			$barcode = !empty($package->barcode) ? $package->barcode : '';

			$height = !empty($package->height) ? $package->height : 0;
			$length = !empty($package->length) ? $package->length : 0;
			$width = !empty($package->width) ? $package->width : 0;
			$weight = !empty($package->reported_weight) ? $package->reported_weight : 0;


			//Add Tracking Number
			$this->db->query("INSERT INTO TrackingInfo SET 
				OrderID = '" . intval($orderID) . "', 
				LocationID = '" . intval($locationID) . "',  
				TrackingCarrierID = 3, 
				TrackingCode = '" . $this->db->escape($barcode) . "', 
				Length = " . floatval($length) . ", 
				Width = " . floatval($width) . ", 
				Height = ". floatval($height) . ", 
				Weight = " . floatval($weight) . ", 
				Label = '" . $this->db->escape($this->labels[$key]) . "',
				CourierService = '" . $courierService . "'");	
		}
	}


	// Void Existing Shipment
	public function void() {

        $request = new \stdClass();

        $request->id = $this->incomingData['id'];
        $request->password = CANPAR_PASSWORD;        
        $request->user_id = CANPAR_USER_ID;     

        try {
            $response = $this->soap->voidShipment($request);

        } catch(Exception $e) {
            $this->errors[] = "Exception:" . $e;
        }

        if(empty($response->error)) {

        	$this->voided = $request->id;
        	$this->updateAsVoidedinDB();
       
        } else {
        	$this->getErrors($response);
        }
	}


	// This method generates a manifest file that is sent to Canpar for
	// billing and tracking purposes. 

	public function endOfDay() {
		$request = new \stdClass();

        $request->date = $this->getShippingDate();
        $request->password = CANPAR_PASSWORD;        
        $request->user_id = CANPAR_USER_ID;
        $request->shipper_num = CANPAR_SHIPPER_NUMBER;   

		$request->payment_info->type = "C";

		try {
            $response = $this->soap->endOfDay($request);

        } catch(Exception $e) {
            $this->errors[] = "Exception:" . $e;
        }

       	if(empty($response->error)) {
        	$this->manifest_num = $response->manifest_num;
       
        } else {
        	$this->getErrors($response);
        }
	}


	// This method returns a manifest summarizing the shipments
	// and charges related to the manifest number, a copy of which will be provided to the driver at the
	// time of pickup. 

	public function getManifest() { 
		$request = new \stdClass();

        $request->manifest_num = $this->incomingData['manifestNumber'];
        $request->password = CANPAR_PASSWORD;        
        $request->shipper_num = CANPAR_SHIPPER_NUMBER;   
        $request->user_id = CANPAR_USER_ID;
        $request->type = "S";

        try {
            $response = $this->soap->getManifest($request);

        } catch(Exception $e) {
            $this->errors[] = "Exception:" . $e;
        }

       	if(empty($response->error)) {

        	//Store Manifest on server
			$this->manifest = $this->extractFiles($response->manifest, 'manifest');
        	$this->updateAsProccessedInDB();
       
        } else {
        	$this->getErrors($response);
        }
	}


	//Consolidate Shipments before printing Manifest
	public function consolidate() {

		try {
			$this->response = $this->soap->Consolidate($this->request);
		} catch(Exception $e) { 

			$this->errors[] = "Exception:" . $e; 
		}
	}


	public function updateAsProccessedInDB() {
		if (empty($this->manifest_num)) {
			return;
		}

		$this->db->query("UPDATE TrackingInfo 
						SET Status = 1 
						WHERE TrackingCarrierID = 3
						AND DATE(DateAdded) = '" . date('Y-m-d') . "'");
	}


	//Get all Shipments by selected Date
	public function getByDate($date = '') {
		$shipments = array();

		// If the date is empty or incorrect use the current date
		if(empty($date) || stripos($date, 'invalid') === true) {
			$date = date('Y-m-d');
		}

		//If it is Shipper display only orders that have been shipped from the shipper's location.
		$shipperLocationID = getShipperLocationID();
		$shipperLocationSQL = !empty($shipperLocationID) ? " AND l.LocationsID = " . $shipperLocationID . " " : "";


		// Since the Pickering(Distribution Centre) has the same Location Code as Yorkville (l000) 
		// exclude it for preventing duplication in history view
		$rows = $this->db->select("SELECT t.*, l.LocationsID FROM TrackingInfo AS t, Locations AS l 
								WHERE t.LocationCode = l.LocationCode
								" . $shipperLocationSQL . "
								AND t.TrackingCarrierID = 3
								AND DATE(t.DateAdded) = '" . $date . "'
								ORDER BY t.OrderID DESC, t.TrackingCode");

		if(empty($rows)) {
			$this->errors[] = 'Can not find Shipment for the Selected Date';
			return array();
		}

		foreach ($rows as $row) {
			$shipments[] = array(
				'Id' => $row['TrackingInfoID'],
				'canparShipmentId' => $row['TrackingIdentifier'],
				'orderId' => $row['OrderID'],
				'locationId' => $row['LocationsID'],
				'pin' => $row['TrackingCode'],
				'date' => $row['DateAdded'],
				'void' => $row['Void'],
				'label' => $row['Label'],
				'locationCode' => $row['LocationCode']
			);
		}

		return $shipments;
	}



	//Get Shipment details by Tracking Number
	public function getByTrackingNumber($pin = '') {

		$shipment = array();

		if(empty($pin)) {
			$this->errors[] = "'Canpar PIN can not be empty'";
		}

		$row = $this->db->selectFirst("SELECT t.*, l.City, l.SteetAddress, l.PostalCode, l.LocationsID  
											FROM TrackingInfo AS t
											LEFT JOIN Locations AS l ON t.LocationCode = l.LocationCode
											WHERE t.TrackingCode =  '" . $this->escape($pin) . "'
											LIMIT 1");
		if(empty($rows)) {
			$this->errors[] = 'Can not find Shipment Details for this Purolator PIN';
			return array();
		}

		$shipment['date'] = $row['DateAdded'];
		$shipment['orderId'] = $row['OrderID'];
		$shipment['service'] = $row['CourierService'];
		$shipment['adminId'] = $row['AdminID'];
		$shipment['adminName'] = $row['Username'];
		$shipment['senderLocationId'] = $row['LocationsID'];
		$shipment['senderCity'] = $row['ActualCityName'];
		$shipment['senderAddress'] = $row['SteetAddress'];
		$shipment['senderPostalCode'] = $row['PostalCode'];
		$shipment['storedLabel'] = $row['Label'];
		$shipment['voided'] = $row['Void'];
		
		return $shipment;
	}


	public function getPackagesByOrderId($id) {
		$packages = array();

		$rows = $this->db->select("SELECT t.Length, t.Width, t.Height, t.Weight, t.Reference, t.Note
								FROM TrackingInfo AS t
								WHERE t.TrackingCarrierID = 2 
								AND t.Length IS NOT NULL 
								AND t.Width IS NOT NULL 
								AND t.Height IS NOT NULL
								AND t.Weight IS NOT NULL 
								AND t.OrderID = '" . $id ."'");

		foreach ($rows as $row) {
			$packages[] = array(
				'length' => $row['Length'],
				'width' => $row['Width'],
				'height' => $row['Height'],
				'weight' => $row['Weight'],
				'reference' => $row['Reference'],
				'note' => $row['Note']
			);
		}

		return $packages;
	}


	public function getShippingBoxes() {
		$boxes = array();
		$rows = $this->db->select("SELECT * FROM ProductsBoxes");

		foreach ($rows as $row) {
			$boxes[] = array(
				'id' => $row['ProductsBoxesID'],
				'description' => $row['Description'],
				'weightLimit' => $row['WeightLimit'],
				'length' => $row['Length'],
				'width' => $row['Width'],
				'height' => $row['Height']
			);
		}
		return $boxes;
	}


	public static function getServiceNameById($id = 0) {
		$serviceLabels = array(
	        '1' => 'Ground',
	        '2' => 'USA Ground',
	        '3' => 'Select Letter',
	        '4' => 'Select Pak',
	        '5' => 'Select Parcel',
	        'C' => 'Express Letter',
	        'D' => 'Express Pak',
	        'E' => 'Express Parcel',
	        'F' => 'USA Select Letter',
	        'G' => 'USA Select Pak',
	        'H' => 'USA Select Parcel',
	        'I' => 'International'
	    );

	    return isset($serviceLabels[$id]) ? $serviceLabels[$id] : 'N/A';
	}


	private function createClient() {
        $client = null;
        $SOAP_OPTIONS = array(
				        	'location' => APP_CANPAR_BUSINESS_SERVICES_END_POINT, 
							'uri' => APP_CANPAR_BUSINESS_SERVICES_URI, 
				            'soap_version' => SOAP_1_2,
				            'exceptions' => false,
				            'trace' => true,
							'connection_timeout'=> 5,
				            'features' => SOAP_SINGLE_ELEMENT_ARRAYS
        );

		try{
			$client = new \SoapClient(null, $SOAP_OPTIONS);
        
        } catch(SoapFault $e){
            $this->errors[] = $e;
		}
        return $client;
	}



    private function populateRequest() {

    	$this->request = array();

    	$this->request[] = new \SoapVar(APP_CANPAR_USER_ID, XSD_STRING, null, null, 'user_id' );
    	$this->request[] = new \SoapVar(APP_CANPAR_PASSWORD, XSD_STRING, null, null, 'password' );
    	$this->request[] = new \SoapVar($this->createShipment(), SOAP_ENC_OBJECT, null, null, 'shipment' );

        return $this->request;
    }


    private function createShipment() {

    	$shipment = array();

        $shipment['billed_weight'] = $this->incomingData['totalWeight'];
        $shipment['service_type'] = $this->incomingData['serviceID'];
        $shipment['delivery_address'] = $this->getDeliveryAddress();
        $shipment['pickup_address'] = $this->getPickupAddress();
        $shipment['shipping_date'] = $this->getShippingDate();
        $shipment['shipper_num'] = APP_CANPAR_SHIPPER_NUMBER;
        $shipment['user_id'] = APP_CANPAR_USER_ID;
        $shipment['billed_weight_unit'] = 'K';
        $shipment['consolidation_type'] = 0;
        $shipment['dg'] = 0;
        $shipment['dimention_unit'] = 'C';
        $shipment['reported_weight_unit'] = 'K';
        $shipment['cod_type'] = 'N';
        $shipment['collect'] = 0;
        $shipment['nsr'] = 0;

        foreach ($this->incomingData['packages'] as $package) {
        	$shipment[] = $this->populatePackage($package);
        }

        return $shipment;
    }


    private function getShippingDate ($lead_time = '0') {
        // Get the current date, plus lead time specified
        $time = strtotime("+" . $lead_time . " weekdays");

        return date('Y-m-d\T00:00:00.000\Z', $time);
    }


    private function getDeliveryAddress() {

    	$addr = new \stdClass();
    	$addressLine = Common::fixAccents($this->incomingData['receiverStreetNumber'] . ' ' . $this->incomingData['receiverStreetName']);

        $addr->address_line_1 = $addressLine;
       	$addr->city = Common::fix($this->incomingData['receiverCity']);
        $addr->country = 'CA';
        $addr->name = 'Canada';
        $addr->postal_code = str_replace(' ' , '', $this->incomingData['receiverPostalCode']);
        $addr->province = $this->incomingData['receiverProvince'];  

        return $addr;
    }


    private function getPickupAddress() {

    	$addr = new \stdClass();
    	$addressLine = Common::fix($this->incomingData['senderStreetNumber'] . ' ' . $this->incomingData['senderStreetName']);

    	$addr->address_line_1 = $addressLine;
    	$addr->city = $this->incomingData['senderCity'];
    	$addr->country = "CA";
        $addr->name = "Canada";
        $addr->postal_code = str_replace(' ', '', $this->incomingData['senderPostalCode']);
        $addr->province = $this->incomingData['senderProvince'];

        return $addr; 
    }


    private function populatePackage($item) {

		$packages = new \stdClass();

		$packages->length = isset($item['length']) ? floatval($item['length']) : 0.0;
		$packages->height = isset($item['height']) ? floatval($item['height']) : 0.0;
		$packages->width = isset($item['width']) ? floatval($item['width']) : 0.0;
		$packages->reported_weight = isset($item['weight']) ? floatval($item['weight']) : 0.0;

		return new \SoapVar($packages, SOAP_ENC_OBJECT, null, null, 'packages' );
    }


	private function getErrors($response = null){

		$response = !empty($response) ? $response : $this->response; 

		if(!empty($response->error)) {
			$this->errors[] = $response->error;
		}
		return count($this->errors);
	}


	private function updateAsVoidedinDB() {
		if(!empty($this->voided)) {
			$this->db->query("UPDATE TrackingInfo SET  Void = 1 WHERE TrackingIdentifier = '" . $this->voided . "' LIMIT 1");
		}
		
	}


	private function extractFiles($labels, $type = 'label') {
		$files = array();

		if(is_array($labels) && $type == 'label') {

			$package = 1;
			foreach ($labels as $label) {
			 	$files[] = Common::getFilePath($label, $type, $package);
			 	$package++;
			}

		} else {
			$files[] = Common::getFilePath($labels, $type);
		}

		return $files;
	}

}