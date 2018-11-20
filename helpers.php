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


function getIncomingJson() {
	$jsonData = json_decode(trim(file_get_contents('php://input')), true);

	return filter_var_array($jsonData, FILTER_SANITIZE_STRING); 
}


function getPhoneAreaCode($phone) {
	$phone = preg_replace('/[^0-9]/', '', $phone); 

	return substr($phone, 0, 3);
}


function getPhone($phone) {

	if(stripos($phone, 'ext') !== false) {
		$phone = substr($phone, 0, strpos($phone, 'ext'));
	}

	$phone = preg_replace('/[^0-9]/', '', $phone);

	return substr($phone, 3);
}


function getStreetNumber($address) {

	$strToRemove = array("-", "_", "#", ")", "(");
	$address = trim(str_replace($strToRemove, " ", $address));

	$addressArray = explode(' ', $address);

	return  $addressArray[0];
}


function getStreetName($address) {

	$strToRemove = array("-", "_", "#", ")", "(");
	$address = trim(str_replace($strToRemove, " ", $address));

	$addressArray = explode(' ', $address);
	array_shift($addressArray);

	return implode(' ', $addressArray);
}

function getAdditionalAddressLine($address) {
	$strToRemove = array("-", "_", "#", ")", "(", ",");
	$address = trim(str_replace($strToRemove, " ", $address));

	return $address;
}

function getPostalCode($postalCode) {
	$strToRemove = array("-", "_", "#", ")", "(", ",", " ");
	$postalCode = trim(str_replace($strToRemove, "", $postalCode));

	return $postalCode;
}


function getCorrectCityName($city) {
	$strToRemove = array("Head Office", 
						"Distribution Centre",
						"Warehouse", 
						"Band Repair Office", 
						")", 
						"("
					);

	$city = str_replace($strToRemove, "", $city);

	$city = str_replace("Web Store", "Pickering", $city);

	return trim($city);
}


function getAdminLocationID($locationID = 0) {

	if(empty($_SESSION['AdminID'])) {
		return $locationID;
	}

	$db = new Canpar\Database();

	$result = $db->query("SELECT LocationsID FROM Admin WHERE AdminID = " . $_SESSION['AdminID']);
	if($result) {
		$row = $result->fetch_assoc();
		$locationID = $row['LocationsID'];
	}

	//Set Location to WebStore if it is Pickering (Head Office)
	$locationID = ($locationID == 64) ? 76 : $locationID;

	return $locationID;
}


function getShipperLocationID($locationID = 0) {

	$db = new Canpar\Database();

	$result = $db->query("SELECT LocationsID FROM Admin WHERE ShippingAccess = 1 AND AdminID = " . $_SESSION['AdminID']);
	if($result) {
		$row = $result->fetch_assoc();
		$locationID = $row['LocationsID'];
	}

	//Set Location to WebStore if it is Pickering (Head Office)
	$locationID = ($locationID == 64) ? 76 : $locationID;

	return $locationID;
}




function redirectIfGuest() {

	if(empty($_SESSION['AdminID'])) {
		header("location: //manager.long-mcquade.com/index.php?msg=timeout");
		exit;
	} 
}


function isAxeAccount() {
	
	$Admin = new Admin();
	$Admin->AdminID = ForceInt($_SESSION['AdminID'], 0); 
	$Admin->GetMe();

	return $Admin->AxeAccount;
}


function setShipmentVoidedInDB($ShipmentData) {

	$pin = !empty($ShipmentData['pin']) ? $ShipmentData['pin'] : '';


	if(!empty($pin)) {
		
		$db = new Canpar\Database();
		$db->query("UPDATE TrackingInfo SET  Void = 1 WHERE TrackingCode = '" . $pin . "' LIMIT 1");
	}
}


function sanitize($txt) {

    $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
    
    $txt = str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
    return $txt;
}


function getFilePathOnServer($label64BaseSring = '', $type = 'label', $package = '1'){
	if(empty($label64BaseSring)) {
		return '';
	}

	$labelName = explode(' ', microtime());
	$labelPath = "./labels/" . $package . "_" . $labelName[1] . ".png";

	if($type == 'manifest') {
		$labelPath = "./manifests/" . $labelName[1] . ".pdf";
	}

    file_put_contents($labelPath, base64_decode($label64BaseSring));

    return $labelPath;
}


function getServiceNameById($id) {
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


// Helper function dump and die, for debugging.
function dd($data) {
	echo '<pre>', var_dump($data), '</pre>';
	exit;
}