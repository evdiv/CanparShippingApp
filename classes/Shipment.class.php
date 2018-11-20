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
    public $debug  = false;


	public function __construct($incomingData = '') {

		$this->incomingData = $incomingData;
		$this->soap = $this->createClient();

		$this->request = new \stdClass();
		$this->response = new \stdClass();
	}


	// Create new Shipment 
	public function create() {

		$this->populateRequest();

		try {
			$this->response = $this->soap->processShipment( new SoapVar($this->request, SOAP_ENC_OBJECT) );

			if($this->debug) {  
	        	echo '<p>Request in Create</p>';
	        	echo '<pre>', print_r($this->request), '</pre>';

	        	echo '<p>Response in Create</p>';
	        	echo '<pre>', print_r($this->response), '</pre>';
	        }

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

			if($this->debug) {  
	        	echo '<p>Request in getLabels</p>';
	        	echo '<pre>', print_r($request), '</pre>';

	        	echo '<p>Response in getLabels</p>';
	        	echo '<pre>', print_r($response), '</pre>';
	        }

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

		$adminID = !empty($_SESSION['AdminID']) ? $_SESSION['AdminID'] : 0;
		$orderID = !empty($this->incomingData['orderID']) ? $this->incomingData['orderID'] : '';
		$locationCode = !empty($this->incomingData['senderLocationCode']) ? $this->incomingData['senderLocationCode'] : '';

		$shipment = $this->response->processShipmentResult->shipment;
		$courierService = getServiceNameById($shipment->service_type);


		foreach ($shipment->packages as $key => $package) {
			
			$barcode = !empty($package->barcode) ? $package->barcode : '';

			$height = !empty($package->height) ? $package->height : 0.0;
			$length = !empty($package->length) ? $package->length : 0.0;
			$width = !empty($package->width) ? $package->width : 0.0;
			$weight = !empty($package->reported_weight) ? $package->reported_weight : 0.0;


			//Add Tracking Number
			$this->db->query("INSERT INTO TrackingInfo SET 
				OrderID = '" . $orderID . "', 
				TrackingCarrierID = 3, 
				TrackingCode = '" . $barcode . "', 
				LocationCode = '" . $locationCode . "',  
				AdminID = " . $adminID . ", 
				Length = " . $length . ", 
				Width = " . $width . ", 
				Height = ". $height . ", 
				Weight = " . $weight . ", 
				Label = '" . $this->labels[$key] . "',
				CourierService = '" . $courierService . "'");	
		}

		// Add Note to the order
    	$this->db->query("INSERT INTO OrdersNotes (AdminID, OrderID, Note, NoteDate)
            VALUES ({$adminID}, {$orderID}, 'Shipment created', Now())"); 
	}


	// Void Existing Shipment
	public function void() {

        $request = new \stdClass();

        $request->id = $this->incomingData['id'];
        $request->password = CANPAR_PASSWORD;        
        $request->user_id = CANPAR_USER_ID;     

        try {
            $response = $this->soap->voidShipment($request);

			if($this->debug) {  
	        	echo '<p>Request in voidShipment</p>';
	        	echo '<pre>', print_r($request), '</pre>';

	        	echo '<p>Response in voidShipment</p>';
	        	echo '<pre>', print_r($response), '</pre>';
	        }

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

			if($this->debug) {  
	        	echo '<p>Request in endOfDay</p>';
	        	echo '<pre>', print_r($request), '</pre>';

	        	echo '<p>Response in endOfDay</p>';
	        	echo '<pre>', print_r($response), '</pre>';
	        }

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

			if($this->debug) {  
	        	echo '<p>Request in getManifest</p>';
	        	echo '<pre>', print_r($request), '</pre>';

	        	echo '<p>Response in getManifest</p>';
	        	echo '<pre>', print_r($response), '</pre>';
	        }

        } catch(Exception $e) {
            $this->errors[] = "Exception:" . $e;
        }

       	if(empty($response->error)) {

        	//Store Manifest on server
			$this->manifest = extractFiles($response->manifest, 'manifest');
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
		$date = date('Y-m-d');

		$this->db->query("UPDATE TrackingInfo 
						SET Status = 1 
						WHERE TrackingCarrierID = 3
						AND DATE(DateAdded) = '" . $date . "'");
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
		$result = $this->db->query("SELECT t.*, l.LocationsID FROM TrackingInfo AS t, Locations AS l 
								WHERE t.LocationCode = l.LocationCode
								" . $shipperLocationSQL . "
								AND t.TrackingCarrierID = 3
								AND l.LocationsID <> 65
								AND DATE(t.DateAdded) = '" . $date . "'
								ORDER BY t.OrderID DESC, t.TrackingCode");

		if($result) {
			while($row = $result->fetch_assoc()) {

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
		} else {
			$this->errors[] = 'Can not find Shipment for the Selected Date';
		}

		return $shipments;
	}



	//Get Shipment details by Tracking Number
	public function getByTrackingNumber($pin = '') {

		$shipment = array();


		if(empty($pin)) {
			$this->errors[] = "'Canpar PIN can not be empty'";
		}

		$result = $this->db->query("SELECT t.*, a.Username, l.ActualCityName, l.SteetAddress, l.PostalCode, l.LocationsID  
							FROM TrackingInfo AS t, Admin AS a, Locations AS l 
							WHERE t.TrackingCode =  '" . $pin . "' 
							AND t.AdminID = a.AdminID 
							AND t.LocationCode = l.LocationCode 
							LIMIT 1");

		if($result) {
			$row = $result->fetch_assoc();

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
		
		} else {
			$this->errors[] = 'Can not find Shipment Details for this Purolator PIN';
		}
		return $shipment;
	}


	public function getPackagesByOrderId($id) {
		$packages = array();

		$result = $this->db->query("SELECT t.Length, t.Width, t.Height, t.Weight, t.Reference, t.Note
								FROM TrackingInfo AS t
								WHERE t.TrackingCarrierID = 2 
								AND t.Length IS NOT NULL 
								AND t.Width IS NOT NULL 
								AND t.Height IS NOT NULL
								AND t.Weight IS NOT NULL 
								AND t.OrderID = '" . $id ."'");

		if($result) {
			while($row = $result->fetch_assoc()) {

				$packages[] = array(
					'length' => $row['Length'],
					'width' => $row['Width'],
					'height' => $row['Height'],
					'weight' => $row['Weight'],
					'reference' => $row['Reference'],
					'note' => $row['Note']
				);
			}
		}
		return $packages;
	}


	public function getShippingBoxes() {
		$boxes = array();

		$result = $this->db->query("SELECT * FROM ProductsBoxes");

		if($result) {
			while($row = $result->fetch_assoc()) {
				$boxes[] = array(
					'id' => $row['ProductsBoxesID'],
					'description' => $row['Description'],
					'weightLimit' => $row['WeightLimit'],
					'length' => $row['Length'],
					'width' => $row['Width'],
					'height' => $row['Height']
				);
			}
		}
		return $boxes;
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
            echo $e;
            exit;
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
        $shipment['billed_weight_unit'] = 'K';
        $shipment['consolidation_type'] = 0;
        $shipment['delivery_address'] = $this->getDeliveryAddress();
        $shipment['dg'] = 0;
        $shipment['dimention_unit'] = 'C';
        $shipment['pickup_address'] = $this->getPickupAddress();
        $shipment['reported_weight_unit'] = 'K';
        $shipment['service_type'] = $this->incomingData['serviceID'];
        $shipment['shipper_num'] = APP_CANPAR_SHIPPER_NUMBER;
        $shipment['shipping_date'] = $this->getShippingDate();
        $shipment['user_id'] = APP_CANPAR_USER_ID;
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

    	$deliveryAddress = new \stdClass();
    	$addressLine = sanitize($this->incomingData['receiverStreetNumber'] . ' ' . $this->incomingData['receiverStreetName']);

        $deliveryAddress->address_line_1 = $addressLine;
       	$deliveryAddress->city = sanitize($this->incomingData['receiverCity']);
        $deliveryAddress->country = 'CA';
        $deliveryAddress->name = 'Canada';
        $deliveryAddress->postal_code = str_replace(' ' , '', $this->incomingData['receiverPostalCode']);
        $deliveryAddress->province = $this->incomingData['receiverProvince'];  

        return $deliveryAddress;
    }


    private function getPickupAddress() {

    	$pickupAddress = new \stdClass();
    	$addressLine = sanitize($this->incomingData['senderStreetNumber'] . ' ' . $this->incomingData['senderStreetName']);

    	$pickupAddress->address_line_1 = $addressLine;
    	$pickupAddress->city = $this->incomingData['senderCity'];
    	$pickupAddress->country = "CA";
        $pickupAddress->name = "Canada";
        $pickupAddress->postal_code = str_replace(' ', '', $this->incomingData['senderPostalCode']);
        $pickupAddress->province = $this->incomingData['senderProvince'];

        return $pickupAddress; 
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

		if(empty($this->voided)) {
			return;
		}
		$this->db->query("UPDATE TrackingInfo SET  Void = 1 WHERE TrackingIdentifier = '" . $this->voided . "' LIMIT 1");
	}


	private function extractFiles($labels, $type = 'label') {
		$files = array();

		if(is_array($labels) && $type == 'label') {

			$package = 1;
			foreach ($labels as $label) {
			 	$files[] = getFilePathOnServer($label, $type, $package);
			 	$package++;
			}

		} else {
			$files[] = getFilePathOnServer($labels, $type);
		}

		return $files;
	}
	
}