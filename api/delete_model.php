<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');

$params = receiveParams(['model_id'], ['model_id']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

// delete from models table
$result = $db->prepare('delete from models where id = :model_id')->execute(array(
	':model_id' => $modelId
));
if (!$result) {
	_exit($db->errorInfo());
}

// delete the model from the dates excluded models list
$dates = $db->query('select * from dates_list')->fetchAll();
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
				$result = $db->prepare('update dates_list set excluded_models = :excluded_models where date_ts = :date_ts')->execute([
					':excluded_models' => json_encode($excludedModels),
					':date_ts' => $date['date_ts'],
				]);
				if (!$result) {
					_exit($db->errorInfo());
				}
			}
		}
		
	}
}

