<?php 

// {"date":1491583999,"excluded_models":[1,3,5]},{"date":1489513999,"excluded_models":[2,3]}

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['hash'], ['hash']);

$dateData = dbRow('select * from dates_list where hash = :hash', ['hash' => $params['hash']]);
if (!$dateData) {
	_exit('date not found');
}

// convert "available_models" to array
$dateData['available_models'] = !empty($dateData['available_models']) ? 
	array_filter(
		json_decode($dateData['available_models'], $__assoc = true), 
		'strlen'
	) : [];

// convert "excluded_models" to array
$dateData['excluded_models'] = !empty($dateData['excluded_models']) ? 
	array_filter(
		json_decode($dateData['excluded_models'], $__assoc = true), 
		'strlen'
	) : [];

// convert "chosen_models" to array
$dateData['chosen_models'] = !empty($dateData['chosen_models']) ? 
	array_filter(
		json_decode($dateData['chosen_models'], $__assoc = true), 
		'strlen'
	) : [];

_success([
	'date' => $dateData,
]);