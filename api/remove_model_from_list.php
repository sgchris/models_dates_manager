<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['list_id', 'model_id'], ['list_id', 'model_id']);

if (!is_numeric($params['list_id'])) {
	_exit('bad list_id parameter');
}

if (!is_numeric($params['model_id'])) {
	_exit('bad model_id parameter');
}

$listId = $params['list_id'];
$modelId = $params['model_id'];

// check if the model exists
$model = dbRow('select * from models where id = :id', ['id' => $modelId]);
if (!$model) {
	_exit('the model does not exist');
}

// check if the list already exists
$list = dbRow(
	'select * from lists where id = :id',
	['id' => $listId]
);
if (!$list) {
	_exit('the list does not exist');
}

// get current models list
$models = json_decode($list['models']) ?? array();

if (!in_array($modelId, $models)) {
	_exit('The model is not in the list');
}

// remove the model from the list
array_splice($models, array_search($modelId, $models), 1);

dbExec('
	UPDATE lists 
	SET models = :models 
	WHERE id = :id', 
	['models' => json_encode($models), 'id' => $listId]
);

_success();
