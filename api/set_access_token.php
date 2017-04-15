<?php
	
// include helper functions
require_once __DIR__.'/init.php';

// validate the request
requestShouldBe('POST');

// check the parameter
if (isset($_POST['access_token'])) {
	
	$accessToken = $_POST['access_token'];
	
	$fb = new \Facebook\Facebook([
		'app_id' => FB_APP_ID,
		'app_secret' => FB_APP_SECRET,
		'default_graph_version' => 'v2.8',
	]);
	
	$user = $fb->get('/me', $accessToken);
	if (!$user) {
		_exit('cannot get logged in user details');
	}
	
	$userData = $user->getDecodedBody();
	if (!$userData || !isset($userData['name']) || !isset($userData['id'])) {
		_exit('cannot get logged in user details body');
	}
	
	// store user data in the session
	session_regenerate_id();
	
	// store access token in the session
	$_SESSION['user'] = [
		'id' => $userData['id'],
		'name' => $userData['name'],
		'access_token' => $accessToken,
	];
	
	_success();
	
} else {
	_exit('no access token parameter');
}