<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['date_ts', 'date_hash']);

// there's no option to show all the models for unauthorized users
$date_ts = isset($params['date_ts']) ? $params['date_ts'] : false;
$date_hash = isset($params['date_hash']) ? $params['date_hash'] : false;

// validate date_ts parameter
if ($date_ts) {
	if (!preg_match('/^\d+$/', $date_ts)) {
		_exit('bad date timestamp parameter');
	}
}

// validate date_ts parameter
if ($date_hash) {
	if (!preg_match('/^[\dA-Fa-f]+$/', $date_hash)) {
		_exit('bad date hash parameter');
	}
}

// set restricted access only when requesting all the models
if (!$date_ts) {
	setRestrictedAccess();
}


// start the get models query
$query = '
	SELECT * 
	FROM models';

// if the date was provided, load list only of the 
if ($date_ts || $date_hash) {
	// get all the models for the date
	$dateRow = dbRow('
		SELECT * 
		FROM dates_list 
		WHERE date_ts = :date_ts OR hash = :hash', array(
			'date_ts' => ($date_ts ?: ''),
			'date_hash' => ($date_hash ?: ''),
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
}

	
$query.= '
	ORDER by 
		CAST(display_order AS INTEGER) DESC, 
		id DESC';

// get all the models
$allModels = dbQuery($query);

// decode models images
foreach ($allModels as $i => $modelRow) {
	$allModels[$i]['images'] = json_decode($modelRow['images']) ?? [];
}

_success(['models' => $allModels]);
