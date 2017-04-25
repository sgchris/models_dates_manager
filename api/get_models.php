<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['date']);

// get all the models
$allModels = dbQuery('
	SELECT * 
	FROM models 
	ORDER by 
		CAST(display_order AS INTEGER) DESC, 
		id DESC');

// process models images
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
	$excludedModels = json_decode($dateInfo['excluded_models']) ?? [];
	
	// process the list - remove excluded models from the list
	if (!empty($excludedModels)) {
		$allModels = array_filter($allModels, function($modelRow) use ($excludedModels) {
			return !in_array($modelRow['id'], $excludedModels);
		});
	}
}

_success(['models' => $allModels]);
