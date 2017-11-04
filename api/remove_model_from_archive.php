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
$modelRow = getModelDetails($modelId);
if (!$modelRow) {
	_exit('cannot find the model');
}

// remove the model from the archive
dbExec('update models set is_archive = 0 where id = :id', ['id' => $modelId]);

_success();

