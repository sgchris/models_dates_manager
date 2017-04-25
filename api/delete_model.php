<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');
$params = receiveParams(['model_id'], ['model_id']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

// delete model's images
$modelDetails = getModelDetails($modelId);
$modelImages = json_decode($modelDetails['images']);
if (!empty($modelImages)) {
	foreach ($modelImages as $imageName) {
		$imageFullPath = IMAGES_UPLOAD_PATH.'/'.$imageName;
		if (file_exists($imageFullPath) && is_writable($imageFullPath)) {
			$result = @unlink($imageFullPath);
		}
	}
}

// delete from models table
$result = dbExec('delete from models where id = :model_id', array(
	':model_id' => $modelId
));

// delete the model from the dates excluded models list
$dates = dbQuery('select * from dates_list');
if (!empty($dates)) {
	foreach ($dates as $date) {
		
		$excludedModels = json_decode($date['excluded_models']) ?? [];
		if (!empty($excludedModels)) {
			
			// check if the model is in the exluded models list.
			$key = array_search($modelId, $excludedModels);
			if ($key !== false) {
				
				// remove the model id from the list
				array_splice($excludedModels, $key, 1);
				
				// update the record back
				dbExec('update dates_list set excluded_models = :excluded_models where date_ts = :date_ts', [
					':excluded_models' => json_encode($excludedModels),
					':date_ts' => $date['date_ts'],
				]);
			}
		}
		
	}
}

_success();