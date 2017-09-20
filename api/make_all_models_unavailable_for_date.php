<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['date', 'category'], ['date', 'category']);

if (!is_numeric($params['date'])) {
	_exit('date parameter has to be a timestamp');
}

if (!is_numeric($params['category']) || !($params['category'] > 0)) {
	_exit('date category has to be numeric');
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

// get list of models in that category
$models = dbQuery(
	'select id from models where category = :category', 
	array(':category' => $params['category'])
);
$modelsIds = array_map(function($modelRow) {
	return $modelRow['id'];
}, $models);

if (empty($modelsIds)) {
	_exit('No models in that category');
}

// get already available models for the date
$availableModels = json_decode($date['available_models']) ?? array();

// add the models from the category to the `available models` list
foreach ($modelsIds as $modelId) {
	if (!in_array($modelId, $availableModels)) {
		$availableModels[] = $modelId;
	}
}


// update the `available models` list back to the DB
dbExec('update dates_list set available_models = :available_models where date_ts = :date_ts', array(
	':available_models' => json_encode($availableModels),
	':date_ts' => $date['date_ts'],
));

_success();
