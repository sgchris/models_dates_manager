<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['date', 'model_id'], ['date', 'model_id']);

if (!is_numeric($params['date'])) {
	_exit('date parameter has to be a timestamp');
}

if (!is_numeric($params['model_id'])) {
	_exit('date model_id has to be numeric');
}

// check that the date is +/- one year
if (!between($params['date'], strtotime('-1 year'), strtotime('+1 year'))) {
	_exit('date is too far away');
}

// check if the date already exists
$date = dbRow(
	'select * from dates_list where date_ts = :date_ts', 
	array(':date_ts' => $params['date'])
);

if (empty($date)) {
	_exit('the date does not exist');
}

$modelObj = getModelDetails($params['model_id']);

// get already excluded models for the date
$chosenModels = json_decode($date['chosen_models']) ?? array();
$availableModels = json_decode($date['available_models']) ?? array();

if (!in_array($modelObj['id'], $chosenModels)) {
	_exit('The model is already not chosen for this date');
}

// add the model back to the available models list
if (!in_array($modelObj['id'], $availableModels)) {
	$availableModels[] = $modelObj['id'];
}

// remove the model from the "excluded" array
array_splice(
	$chosenModels, 
	array_search($params['model_id'], $chosenModels), 
	1
);

dbExec('
	update dates_list 
	set 
		chosen_models = :chosen_models,
		available_models = :available_models 
	where 
		date_ts = :date_ts', 
		
	array(
		':chosen_models' => json_encode($chosenModels),
		':available_models' => json_encode($availableModels),
		':date_ts' => $date['date_ts'],
	)
);

_success();
