<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'config.php';

// include vendors
require_once __DIR__.DS.'..'.DS.'vendor'.DS.'autoload.php';

// include helper functions
require_once __DIR__.DS.'tools'.DS.'functions.php';

session_start();

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
