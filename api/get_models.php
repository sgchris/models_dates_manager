<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['date']);

// there's no option to show all the models for unauthorized users
if (empty($params['date'])) {
	setRestrictedAccess();
}

// get all the models
$allModels = dbQuery('
	SELECT * 
	FROM models 
	ORDER by 
		CAST(display_order AS INTEGER) DESC, 
		id DESC');

// decode models images
foreach ($allModels as $i => $modelRow) {
	$allModels[$i]['images'] = json_decode($modelRow['images']) ?? [];
}

// remove date's excluded models
if (!empty($params['date'])) {
	$dateInfo = dbRow('
		SELECT * 
		FROM dates_list 
		WHERE date_ts = :date_ts
	', array(':date_ts' => $params['date']));
	if (!$dateInfo) {
		_exit('cannot read date info');
	}
	
	// get list of models to exclude
	$excludedModelsIds = json_decode($dateInfo['excluded_models']) ?? [];
	
	// initialize the two arrays
	$excludedModels = array();
	$includedModels = array();
	
	// process the list - remove excluded models from the list
	foreach ($allModels as $model) {
		if (in_array($model['id'], $excludedModelsIds)) {
			$excludedModels[] = $model;
		} else {
			$includedModels[] = $model;
		}
	}
	
	_success(['models' => $includedModels, 'excluded_models' => $excludedModels]);
}

_success(['models' => $allModels]);
