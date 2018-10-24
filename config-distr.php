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
//Canpar Credentials and Options

define("CANPAR_USER_ID", '');
define("CANPAR_SHIPPER_NUMBER", '');
define("CANPAR_PASSWORD", ''); 
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