<?php 

//Functions ////////////////////////////////////////////////////


//Define autoloader 
function __autoload($className) {
	
	$className = explode('\\', $className);
	$filePath = './classes/' . end($className) . '.class.php';

    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    } 

    return false;
} 

//Peventing access for unregistered users
function redirectIfGuest() {

	if(empty($_SESSION['AdminID'])) {
		header("location: /index.php?msg=timeout");
		exit;
	} 
}


function getIncomingJson() {

	$jsonData = json_decode(trim(file_get_contents('php://input')), true);

	return filter_var_array($jsonData, FILTER_SANITIZE_STRING); 
}


function getFromRequest($name) {

	$value = !empty($_GET[$name]) ? $_GET[$name] : '';

	if (empty($value)) {
		$value = !empty($_POST[$name]) ? $_POST[$name] : '';
	}
	return $value;
}


function getIncomingString($name, $default = "") {

	$value = getFromRequest($name);

	if(!is_string($value) || empty(trim($value))) {
		return $default;
	}
	return htmlspecialchars($value, ENT_QUOTES);
}


function getIncomingInt($name, $default = 0) {

	$value = getFromRequest($name);

	if (!is_numeric($value) || empty($value)) {
		return $default;
	}
	return (int)$value;	
}



function getShipperLocationID($locationID = 0) {
	$db = new Canpar\Database();

	$result = $db->query("SELECT LocationsID FROM Admin WHERE ShippingAccess = 1 AND AdminID = " . $_SESSION['AdminID']);
	
	if($result) {
		$row = $result->fetch_assoc();
		$locationID = $row['LocationsID'];
	}

	return $locationID;
}



