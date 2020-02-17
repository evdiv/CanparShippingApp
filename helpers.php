<?php 

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