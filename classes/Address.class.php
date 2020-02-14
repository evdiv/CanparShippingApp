<?php 

namespace Canpar;

class Address 
{

	public static function getPhoneAreaCode($phone = '') {

		$phone = preg_replace('/[^0-9]/', '', $phone);
		return substr($phone, 0, 3);
	}


	public static function getPhone($phone = '') {

		if(stripos($phone, 'ext') !== false) {
			$phone = substr($phone, 0, strpos($phone, 'ext'));
		}

		$phone = preg_replace('/[^0-9]/', '', $phone);
		return substr($phone, 3);
	}


	public static function getStreetNumber($address = '') {

		$strToRemove = array("-", "_", "#", ")", "(");
		$address = trim(str_replace($strToRemove, " ", $address));

		$addressArray = explode(' ', $address);

		return  $addressArray[0];
	}


	public static function getStreetName($address = '') {

		$strToRemove = array("-", "_", "#", ")", "(");
		$address = trim(str_replace($strToRemove, " ", $address));

		$addressArray = explode(' ', $address);
		array_shift($addressArray);

		return ucfirst(implode(' ', $addressArray));
	}


	public static function cleanAddressLine($address = '') {
		$strToRemove = array("-", "_", "#", ")", "(", ",");
		$address = trim(str_replace($strToRemove, " ", $address));

		return $address;
	}


	public static function splitAddress($name = '') {
	  $separator = strpos(trim($name), ' ', 15);

	  if ($separator === false) {
		return array($name, '');
	  }

	  $firstname = substr($name, 0, $separator + 1);
	  $surname = substr($name, $separator);

	  return array($firstname, $surname);
	}


	public static function getPostalCode($postalCode = '') {
		$strToRemove = array("-", "_", "#", ")", "(", ",", " ");
		$postalCode = trim(str_replace($strToRemove, "", $postalCode));

		return $postalCode;
	}


	public static function cleanCityName($city = ''){
		$city = Common::fixAccents($city);
		$city = str_replace(","," ", $city);
		
		return ucfirst($city);
	}
	
}