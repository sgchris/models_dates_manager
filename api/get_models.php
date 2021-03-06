<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['models_hashes', 'date_hash']);

// there's no option to show all the models for unauthorized users
$models_hashes = isset($params['models_hashes']) ? $params['models_hashes'] : false;
$date_hash = isset($params['date_hash']) ? $params['date_hash'] : false;

// validate models_hashes parameter
if ($models_hashes && count($models_hashes)) {
	foreach ($models_hashes as $model_hash) {
		if (!preg_match('/^[\dA-Fa-f]+$/', $model_hash)) {
			_exit('bad one of the models hashes parameters');
		}
	}
}

// validate date_hash parameter
if ($date_hash) {
	if (!preg_match('/^[\dA-Fa-f]+$/', $date_hash)) {
		_exit('bad date hash parameter');
	}
}

// *all* the models are available only to admin
if (!$date_hash && !$models_hashes) {
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
	$dateRow['available_models'] = 
		!empty($dateRow['available_models']) && 
		($decodedData = json_decode($dateRow['available_models'], $__assoc = true)) !== false && 
		is_array($decodedData) ? 
		array_filter($decodedData, 'is_numeric') : [];
	
	$dateRow['chosen_models'] = 
		!empty($dateRow['chosen_models']) && 
		($decodedData = json_decode($dateRow['chosen_models'], $__assoc = true)) !== false && 
		is_array($decodedData) ? 
		array_filter($decodedData, 'is_numeric') : [];
	
	$dateRow['excluded_models'] = 
		!empty($dateRow['excluded_models']) && 
		($decodedData = json_decode($dateRow['excluded_models'], $__assoc = true)) !== false && 
		is_array($decodedData) ? 
		array_filter($decodedData, 'is_numeric') : [];
	
	$modelsIds = array_merge(
		$dateRow['available_models'], 
		$dateRow['chosen_models'], 
		$dateRow['excluded_models']
	);

	// check who's archived model from the current list, and remove her
	$modelsIds = filterArchivedModels($modelsIds);

	
	$query.= ' WHERE id IN ('. implode(',', $modelsIds). ')';
} elseif ($models_hashes) {
	$query.= ' WHERE hash in ("'.implode('","', $models_hashes).'")';
} else {
	$query.= ' WHERE is_archive = 0';
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


// ---------------------------------

// check archived models in this list and remove them
function filterArchivedModels($modelsIds) {
	if (empty($modelsIds)) {
		return [];
	}
	
	// get archived models in the given list
	$archModelsQuery = '
		select id 
		from models 
		where id in ('.implode(',', $modelsIds).') and 
			is_archive = 1';
	$archivedModels = dbQuery($archModelsQuery);

	// convert to IDs array
	$archivedModels = array_map(function($archModelRow) {
		return $archModelRow['id'];
	}, $archivedModels);

	// remove archived models from the original list
	if (!empty($archivedModels)) {
		$newModelsIds = [];
		foreach ($modelsIds as $modelId) {
			if (!in_array($modelId, $archivedModels)) {
				$newModelsIds[] = $modelId;
			}
		}

		$modelsIds = $newModelsIds;
	}

	return $modelsIds;
}


