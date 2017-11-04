<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['model_id'], ['model_id']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

// get the model from the DB
$modelRow = getModelDetails($params['model_id']);
if (!$modelRow) {
	_exit('cannot find the model');
}

$db->beginTransaction();

// remove the model from all the lists
removeModelFromLists($modelId);

// remove the model from all the dates
removeModelFromDates($modelId);

// update the model row
dbExec('UPDATE models set is_archive = 1 where id = :id', ['id' => $modelId]);

$db->commit();

_success();

////////////////////////////////////////////////////////////////////////////////////////////////////////

// find the model in all the lists, and remove her from there
function removeModelFromLists($modelId) {
	$lists = dbQuery('select * from lists');
	foreach ($lists as $list) {
		$listModels = json_decode($list['models'], true);
		if (is_array($listModels) && !empty($listModels) && ($modelIdx = array_search($modelId, $listModels)) !== false) {
			// remove the model from the list
			array_splice($listModels, $modelIdx, 1);
			dbExec(
				'update lists set models = :models where id = :id', 
				['id' => $list['id'], 'models' => json_encode($listModels)]
			);
		}
	}
}


// find the model in all the dates, and remove her from there
function removeModelFromDates($modelId) {
	$dates = dbQuery('select * from dates_list');
	foreach ($dates as $date) {
		// available models
		$availableModels = json_decode($date['available_models'], true);
		if (is_array($availableModels) && !empty($availableModels) && ($modelIdx = array_search($modelId, $availableModels )) !== false) {
			// remove the model from the list
			array_splice($availableModels, $modelIdx, 1);
			dbExec(
				'update dates_list set available_models = :available_models where date_ts = :date_ts', 
				['date_ts' => $date['date_ts'], 'available_models' => json_encode($availableModels)]
			);
		}

		// chosen models
		$chosenModels = json_decode($date['chosen_models'], true);
		if (is_array($chosenModels) && !empty($chosenModels) && ($modelIdx = array_search($modelId, $chosenModels )) !== false) {
			// remove the model from the list
			array_splice($chosenModels, $modelIdx, 1);
			dbExec(
				'update dates_list set chosen_models = :chosen_models where date_ts = :date_ts', 
				['date_ts' => $date['date_ts'], 'chosen_models' => json_encode($chosenModels)]
			);
		}

		// excluded models
		$excludedModels = json_decode($date['excluded_models'], true);
		if (is_array($excludedModels) && !empty($excludedModels) && ($modelIdx = array_search($modelId, $excludedModels )) !== false) {
			// remove the model from the list
			array_splice($excludedModels, $modelIdx, 1);
			dbExec(
				'update dates_list set excluded_models = :excluded_models where date_ts = :date_ts', 
				['date_ts' => $date['date_ts'], 'excluded_models' => json_encode($excludedModels)]
			);
		}
	}
}
