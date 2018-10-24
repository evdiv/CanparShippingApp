<?php

require_once "./config.php";

redirectIfGuest();


//Incoming Parameters 
$jsonData 	= getIncomingJson();


//***************************************************
// Get all Available locations from DB
 
if($jsonData['action'] == "getLocations") {

	$locations = (new Canpar\Origin())->getAll();

	echo json_encode($locations);


//***************************************************
// Get Sender details by location ID

} elseif($jsonData['action'] == "getSenderLocation") {

	$id = !empty($jsonData['Id']) ? $jsonData['Id'] : getAdminLocationID(DEFAULT_LOCATION_ID);
	$location = (new Canpar\Origin())->getById($id);

    echo json_encode(array('sender' => $location));


//***************************************************
// Get Receiver details by Order ID

} elseif($jsonData['action'] == "getReceiverByOrderId") {

	$receiver = (new Canpar\Customer())->getByOrderId($jsonData['orderID']);

   	echo json_encode(array('receiver' => $receiver));


//***************************************************
// Get Sender details by Order ID

} elseif($jsonData['action'] == "getSenderByOrderId") {

	$location = (new Canpar\Origin())->getByOrderId($jsonData['orderID']);

    echo json_encode(array('sender' => $location));


//***************************************************
// Get All Available Services with Rates

} elseif($jsonData['action'] == "getAvalableServices") {

	$Estimate = new Estimate($jsonData);
	$Estimate->getServicesWithRates();

	echo json_encode(array('services' => $Estimate->services, 'errors' => $Estimate->errors));


//***************************************************
// Get Available Shipping Boxes

} elseif($jsonData['action'] == "getShippingBoxes") {

	$Shipment = new Shipment($jsonData);
	$Shipment->getShippingBoxes();

	echo json_encode(array('boxes' => $Shipment->getShippingBoxes(), 'errors' => $Shipment->errors));


//***************************************************
// Create Canpar Shipment and receive Labels

} elseif($jsonData['action'] == "createShipment") {

	$Shipment = new Shipment($jsonData);
	$Shipment->create();

	$Shipment->getLabels();
	$Shipment->store(); 

	echo json_encode(array('labels' => $Shipment->labels, 'errors' => $Shipment->errors));


//***************************************************
// Void Shipment by Tracking PIN

} elseif($jsonData['action'] == "voidShipment") {
	
	if(empty($jsonData['id'])) { exit; }

	$Shipment = new Shipment($jsonData);
	$Shipment->void();

	echo json_encode(array('voided' => $Shipment->voided, 'errors' => $Shipment->errors));


//***************************************************
// Generate a manifest file that is sent to Canpar for billing and tracking purposes.
// Returns Manifest number

} elseif($jsonData['action'] == "endOfDay") {

	$Shipment = new Shipment();
	$Shipment->endOfDay();

	echo json_encode(array('manifestNumber' => $Shipment->manifest_num, 'errors' => $Shipment->errors));


//***************************************************
// Returns the manifest URL

} elseif($jsonData['action'] == "getManifest") {

	//Consolidation should be used as an end of day process
	$Shipment = new Shipment($jsonData);
	$Shipment->getManifest();

	echo json_encode(array('manifest' => $Shipment->manifest,  'errors' => $Shipment->errors));


//***************************************************
// Get All Shipments by selected Date

} elseif($jsonData['action'] == "getShipmentsByDate") {

	$date = (empty($jsonData['date']) || $jsonData['date'] === "Invalid date") ? date('Y-m-d') : $jsonData['date'];
	$Shipment = new Shipment();

	echo json_encode(array('shipments' => $Shipment->getByDate($date), 'errors' => $Shipment->errors));


//***************************************************
// Get Shipment Details by Tracking Code

} elseif($jsonData['action'] == "getShipmentDetails") {

	$Shipment = new Shipment();

	echo json_encode(array('shipment' => $Shipment->getByTrackingNumber($jsonData['pin']), 'errors' => $Shipment->errors));
} 