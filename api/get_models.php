<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['model_hash', 'date_hash']);

// there's no option to show all the models for unauthorized users
$model_hash = isset($params['model_hash']) ? $params['model_hash'] : false;
$date_hash = isset($params['date_hash']) ? $params['date_hash'] : false;

// validate model_hash parameter
if ($model_hash) {
	if (!preg_match('/^[\dA-Fa-f]+$/', $model_hash)) {
		_exit('bad model hash parameter');
	}
}

// validate date_hash parameter
if ($date_hash) {
	if (!preg_match('/^[\dA-Fa-f]+$/', $date_hash)) {
		_exit('bad date hash parameter');
	}
}

// *all* the models are available only to admin
if (!$date_hash && !$model_hash) {
	setRestrictedAccess();
}


// start the get models query
$query = '
	SELECT * 
	FROM models';
$queryParams = [];

// if the date was provided, load list only of the 
if ($date_hash) {
	// get all the models for the date
	$dateRow = dbRow('
		SELECT * 
		FROM dates_list 
		WHERE date_ts = :date_ts OR hash = :hash', array(
			'hash' => ($date_hash ?: '')
		)
	);
	
	if (!$dateRow) {
		_exit('date not found');
	}
	
	// get all the relevant models IDs for the date
	$dateRow['available_models'] = !empty($dateRow['available_models']) ? json_decode($dateRow['available_models'], true) : [];
	$dateRow['available_models'] = array_filter($dateRow['available_models'], 'is_numeric');
	
	$dateRow['chosen_models'] = !empty($dateRow['chosen_models']) ? json_decode($dateRow['chosen_models'], true) : [];
	$dateRow['chosen_models'] = array_filter($dateRow['chosen_models'], 'is_numeric');
	
	$dateRow['excluded_models'] = !empty($dateRow['excluded_models']) ? json_decode($dateRow['excluded_models'], true) : [];
	$dateRow['excluded_models'] = array_filter($dateRow['excluded_models'], 'is_numeric');
	
	$modelsIds = array_merge(
		$dateRow['available_models'], 
		$dateRow['chosen_models'], 
		$dateRow['excluded_models']
	);
	
	$query.= ' WHERE id IN ('. implode(',', $modelsIds). ')';
} elseif ($model_hash) {
	$query.= ' WHERE hash = :hash';
	$queryParams['hash'] = $model_hash;
}

$query.= '
	ORDER by 
		CAST(display_order AS INTEGER) DESC, 
		id DESC';

// get all the models
$allModels = dbQuery($query, $queryParams);

// decode models images
foreach ($allModels as $i => $modelRow) {
	$allModels[$i]['images'] = json_decode($modelRow['images']) ?? [];
}

_success(['models' => $allModels]);
