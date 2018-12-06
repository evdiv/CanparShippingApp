<?php

//*************************************
//Main Configuration

define("APP_NAME", "Canpar Web Client");
define("APP_URL", "/canpar");
define("COMPANY_NAME", "Your Company Name");
define("DEFAULT_LOCATION_ID", "1");


//************************************
//DB Connection

define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');


//************************************
//Canpar Credentials

define("CANPAR_USER_ID", '');
define("CANPAR_SHIPPER_NUMBER", '');
define("CANPAR_PASSWORD", ''); 


//************************************
//Canpar Shipping Options

define("CANPAR_COUNTRY_CODE", "CA");
define("CANPAR_COUNTRY_NAME", "Canada");
define("CANPAR_PAYMENT_INFO_TYPE", "C");
define("CANPAR_MANIFEST_TYPE", "S");
define("CANPAR_BILLED_WEIGHT_UNIT", "K"); // K/L
define("CANPAR_DIMENTION_UNIT", "C"); // C/I
define("CANPAR_CONSOLIDATION_TYPE", 0);
define("CANPAR_DG", 0); // Dangerous Goods
define("CANPAR_REPORTED_WEIGHT_UNIT", "K");
define("CANPAR_COD_TYPE", "N");
define("CANPAR_COLLECT", 0);
define("CANPAR_NSR", 0); //No Signature required


//*************************************
//Canpar Added Charges

define("CANPAR_RESIDENTIAL_CHARGES", 3.25);
define("CANPAR_FUEL_SURCHARGE", 0.24);
define("CANPAR_APPLY_DISCOUNTS", '0');


//*************************************
//Canpar Production URLs

//define("CANPAR_RATING_URL", "https://canship.canpar.com/canshipws/services/CanparRatingService?wsdl");
//define("CANPAR_BUSINESS_SERVICES_URL", "https://canship.canpar.com/canshipws/services/CanshipBusinessService?wsdl");
//define("CANPAR_BUSINESS_SERVICES_END_POINT", "https://canship.canpar.com/canshipws/services/CanshipBusinessService");
//define("CANPAR_BUSINESS_SERVICES_URI", "http://ws.business.canshipws.canpar.com");


//*************************************
//Canpar Development URLs

define("CANPAR_RATING_URL", "https://sandbox.canpar.com/canshipws/services/CanparRatingService?wsdl");
define("CANPAR_BUSINESS_SERVICES_URL", "https://sandbox.canpar.com/canshipws/services/CanshipBusinessService?wsdl");
define("CANPAR_BUSINESS_SERVICES_END_POINT", "https://sandbox.canpar.com/canshipws/services/CanshipBusinessService");
define("CANPAR_BUSINESS_SERVICES_URI", "http://ws.business.canshipws.canpar.com");


//****************************************
// Autoloader and helper functions

require_once "./helpers.php";


//******************************************
// Incoming Parameters
$ordersID = getIncomingInt('OrdersID');