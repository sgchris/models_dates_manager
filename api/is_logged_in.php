<?php

// include helper functions
require_once __DIR__.'/init.php';

// validate the request
requestShouldBe('GET');

_success([
    "result" => "ok", 
    "name" => $_SESSION['user']['name'], 
    "isLoggedIn" => isset($_SESSION['user']['access_token'])
]);
