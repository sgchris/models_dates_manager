<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['date', 'hash']);

// there's no option to show all the models for unauthorized users
if (empty($params['date'])) {
	setRestrictedAccess();
}

// validate the hash
$hash = null;
if (!empty($params['hash'])) {
	if (!preg_match('/^[a-f0-9]{32}$/', $params['hash'])) {
		_exit('bad hash parameter');
	}
	
	$hash = $params['hash'];
}

$query = '
	SELECT * 
	FROM models ';
	
if ($hash) {
	$query.= ' 
	WHERE hash = :hash ';
}

$query.= '
	ORDER by 
		CAST(display_order AS INTEGER) DESC, 
		id DESC';

// get all the models
$queryParams = $hash ? [':hash' => $hash] : [];
$allModels = dbQuery($query, $queryParams);

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
	$chosenModelsIds = json_decode($dateInfo['chosen_models']) ?? [];
	
	// initialize the two arrays
	$excludedModels = array();
	$includedModels = array();
	$chosenModels = array();
	
	// process the list - remove excluded models from the list
	foreach ($allModels as $model) {
		// check excluded models
		if (in_array($model['id'], $excludedModelsIds)) {
			$excludedModels[] = $model;
		} elseif (in_array($model['id'], $chosenModelsIds)) {
			$chosenModels[] = $model;
		} else {
			$includedModels[] = $model;
		}
	}
	
	_success(['models' => $includedModels, 'excluded_models' => $excludedModels, 'chosen_models' => $chosenModels]);
}

_success(['models' => $allModels]);
