<?php
	
// include helper functions
require_once __DIR__.'/init.php';

// validate the request
requestShouldBe('POST');
if (isset($_SESSION['user'])) {
    _exit('already logged in as '.$_SESSION['user']['name']);
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    _exit("username or password are not provided");
}

if (strlen($_POST['username']) > 255 || strlen($_POST['username']) < 3 ||
    strlen($_POST['password']) > 255 || strlen($_POST['password']) < 3) {
    _exit("Invalid input");
}

$newAccessToken = signIn($_POST['username'], $_POST['password']);
if ($newAccessToken !== false) {
    _success(["name" => $_SERSSION['user']['name'], ACCESS_TOKEN_KEY => $newAccessToken]);
} else {
    _exit('authentication failed');
}
