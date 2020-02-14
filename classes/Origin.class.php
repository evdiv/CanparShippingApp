<?php 

namespace Canpar; 

class Origin {
	
	private $db;
    private $incomingData;

    public function __construct($incomingData = '') {

		$this->db = new Database();
        $this->incomingData = $incomingData;
    }


	public function getAll() {

		$locations = array();
		$result = $this->db->select("SELECT l.LocationsID, l.City, p.ProvinceName 
									FROM Locations AS l, Provinces AS p 
									WHERE l.ProvincesID = p.ProvincesID
									ORDER BY l.City");
		foreach ($rows as $row) {

			$locations[] = array(
				'Id' => $row['LocationsID'],
				'city' => utf8_encode($row['City']),
				'province' => utf8_encode($row['ProvinceName'])
			);
		}

		return $locations;
	}




	public function getById($id) {

		$location = array();
		$result = $this->db->selectFirst("SELECT l.*, p.ProvinceName, p.ProvinceCode 
										FROM Locations AS l, Provinces AS p
										WHERE l.ProvincesID = p.ProvincesID
										AND l.LocationsID = " . $id . "
										LIMIT 1");
		if(!count($row)){
			return array();
		}

		$location['Id'] = $row['LocationsID'];
		$location['Name'] = COMPANY_NAME;
		$location['Company'] = COMPANY_NAME;
		$location['StreetNumber'] = Address::getStreetNumber($row['SteetAddress']);
		$location['StreetName'] = Address::getStreetName($row['SteetAddress']);
		$location['PhoneAreaCode'] = Address::getPhoneAreaCode($row['Phone']);
		$location['Phone'] = Address::getPhone($row['Phone']);
		$location['City'] = $row['City'];
		$location['Province'] = $row['ProvinceCode'];
		$location['Country'] = 'CA';
		$location['PostalCode'] = $row['PostalCode'];
		$location['LocationCode'] = $row['LocationCode'];

	    //Encode everything to UTF8
		foreach ($location as &$val) {
			$val = utf8_encode($val);
		}
		return $location;
	}



	public function getByOrderId($id) {

		$location = array();
		$result = $this->db->selectFirst("SELECT l.*, p.ProvinceName, p.ProvinceCode 
								FROM Locations AS l, Provinces AS p, TrackingInfo AS t
								WHERE l.ProvincesID = p.ProvincesID
								AND l.LocationsID = t.LocationsID
								AND t.OrderID = " . $id . "
								LIMIT 1");
		if(!count($row)){
			return array();
		}

		$location['Id'] = $row['LocationsID'];
		$location['City'] = $row['City'];
		$location['Name'] = COMPANY_NAME;
		$location['Company'] = COMPANY_NAME;
		$location['StreetNumber'] = Address::getStreetNumber($row['SteetAddress']);
		$location['StreetName'] = Address::getStreetName($row['SteetAddress']);
		$location['Province'] = $row['ProvinceCode'];
		$location['Country'] = 'CA';
		$location['PostalCode'] = $row['PostalCode'];
		$location['PhoneAreaCode'] = Address::getPhoneAreaCode($row['Phone']);
		$location['Phone'] = Address::getPhone($row['Phone']);
		$location['LocationCode'] = $row['StoreName'];


	    //Encode everything to UTF8
		foreach ($location as &$val) {
			$val = utf8_encode($val);
		}
		return $location;
	}
}

