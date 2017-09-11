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

// get already available models for the date
$availableModels = json_decode($date['available_models']) ?? array();

if (in_array($modelObj['id'], $availableModels)) {
	_exit('The model is already available for this date');
}

$availableModels[] = $modelObj['id'];

dbExec('update dates_list set available_models = :available_models where date_ts = :date_ts', array(
	':available_models' => json_encode($availableModels),
	':date_ts' => $date['date_ts'],
));

_success();
