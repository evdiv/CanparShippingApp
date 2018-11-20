<?php 

namespace Canpar;

class Customer {

	private $db;


    public function __construct() {

		$this->db = new Database();
    }


	public function getByOrderId($id) {

		$customer = array();

		$result = $this->db->query("SELECT a.AccountsID, p.ProductName, sc.Qty, sc.ShippingInsurance, a.Email, a.HomePhone, a.PostalCode, a.HomeCity, 
								a.AptUnitl, a.HomeAddress, o.OrdersID, sc.ProductPrice, p.ProductPrice As ActualPrice, p.ProductsID, 
								a.ProvincesID, a.FirstName, a.LastName, o.ShippingName, o.CourierSelected, o.CourierService
								FROM Accounts a, Orders o, ShoppingCart sc, Products p
								WHERE p.ProductsID = sc.ProductsID 
								AND a.AccountsID = o.AccountsID
								AND o.OrdersID = sc.OrderID
								AND sc.Status = 0
								AND o.OrdersID = " . $id . " LIMIT 1");
		if($result) {
			$row = $result->fetch_assoc();

			$customer['CustomerCode'] = $row['AccountsID']; 
			$customer['ShippingName'] = !empty($row['ShippingName']) ? $row['ShippingName'] : $row['FirstName'] . " " . $row['LastName']; 
			$customer['AttentionTo'] = $customer['ShippingName'];
			$customer['StreetNumber'] = getStreetNumber($row['HomeAddress']);
			$customer['StreetName'] = ucfirst(getStreetName($row['HomeAddress']));


			$AddressTemp = $row['AptUnitl'];

			$Address2 = $AddressTemp;
			$Address3 = '';
			
			if(strlen(trim($AddressTemp)) > 30){

				$AddressArray = \Common::split_address($AddressTemp);
				$Address2 = $AddressArray[0];
				$Address3 = $AddressArray[1];
			}

	        $customer['AddressLine2'] =  getAdditionalAddressLine($Address2);
	        $customer['AddressLine3']  = getAdditionalAddressLine($Address3);
	        $customer['City'] = ucfirst(str_replace(","," ", sanitize($row['HomeCity'])));

	        $Provinces = new \Provinces;
	        $customer['ProvinceCode'] =  $Provinces->GetProvinceCodeByAccountsID($row['AccountsID']);
	        $customer['PostalCode'] = getPostalCode($row['PostalCode']);
	        $customer['Country'] = "CA"; 
	        $customer['PhoneAreaCode'] = getPhoneAreaCode($row['HomePhone']); 
	        $customer['Phone'] = getPhone($row['HomePhone']);
	        $customer['Email'] = $row['Email'];


	       	//Encode everything to UTF8
			foreach ($customer as &$val) {
				$val = utf8_encode($val);
			}
		}

		return $customer;
	}

}