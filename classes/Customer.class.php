<?php 

namespace Canpar;

class Customer {

	private $db;


    public function __construct() {

		$this->db = new Database();
    }


	public function getByOrderId($id = 0) {

		if(empty($id)) {
			return array();
		}

		$customer = array();

		$row = $this->db->selectFirst("SELECT a.AccountsID, a.FirstName, a.LastName, a.Email, a.HomePhone, 
									o.Active, a.PostalCode, a.HomeCity, a.HomeAddress, a.AptUnit, 
									o.OrdersID, o.ShippingName, o.TransAmount, o.CourierSelected, o.CourierService, 
									p.ProvinceCode

								FROM Accounts AS a, Orders AS o, Provinces AS p
								WHERE a.AccountsID = o.AccountsID
								AND a.ProvincesID = p.ProvincesID
								AND o.OrdersID = " . $id . " 
								LIMIT 1");
		if(!count($row)){
			return array();
		}

		$customer['CustomerCode'] = $row['AccountsID']; 
		$customer['ShippingName'] = !empty($row['ShippingName']) ? $row['ShippingName'] : $row['FirstName'] . " " . $row['LastName']; 
		$customer['AttentionTo'] = $customer['ShippingName'];
		$customer['StreetNumber'] = Address::getStreetNumber($row['HomeAddress']);
		$customer['StreetName'] = Address::getStreetName($row['HomeAddress']);


		$Address2 = $row['AptUnit'];
		$Address3 = '';
		
		if(strlen(trim($row['AptUnit'])) > 30){

			$AddressArray = Address::splitAddress($AddressTemp);
			$Address2 = $AddressArray[0];
			$Address3 = $AddressArray[1];
		}

        $customer['AddressLine2'] =  Address::cleanAddressLine($Address2);
        $customer['AddressLine3'] =  Address::cleanAddressLine($Address3);
        $customer['City'] = Address::cleanCityName($row['HomeCity']);
    	$customer['ProvinceCode'] =  $row['ProvinceCode'];
        $customer['PostalCode'] = Address::getPostalCode($row['PostalCode']);
        $customer['Country'] = "CA"; 
        $customer['PhoneAreaCode'] = Address::getPhoneAreaCode($row['HomePhone']); 
        $customer['Phone'] = Address::getPhone($row['HomePhone']);
        $customer['Email'] = $row['Email'];


       	//Encode everything to UTF8
		foreach ($customer as &$val) {
			$val = utf8_encode($val);
		}

		return $customer;
	}
}