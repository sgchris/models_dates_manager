<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once __DIR__.DIRECTORY_SEPARATOR.'config.php';

// include vendors
require_once __DIR__.DS.'..'.DS.'vendor'.DS.'autoload.php';

// include helper functions
require_once __DIR__.DS.'tools'.DS.'functions.php';

// include helper functions
require_once __DIR__.DS.'tools'.DS.'auth.php';

// include helper functions
require_once __DIR__.DS.'tools'.DS.'logs.php';

session_start();

// check if the user is logged in + load auth functions
checkAuth();

// all the responses should be JSONs
header('Content-Type: application/json');

// Check if the API call was to that file
$isCheckDbApi = !isCommandLineInterface() && (stripos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false);

// check the request method if the API is to that file
if ($isCheckDbApi) {
	requestShouldBe('GET');
}

require __DIR__.DS.'tools'.DS.'check_db.php';

// return success if the API is to that file
if ($isCheckDbApi) {
	_success();
}
