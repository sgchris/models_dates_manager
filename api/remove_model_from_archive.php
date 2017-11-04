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

$modelRow = dbRow('select * from models_archive where id = :id', ['id' => $modelId]);
if (!$modelRow) {
	_exit('model not found');
}

$db->beginTransaction();

unset($modelRow['id']);

// insert the row back to "models"
$sql = 'INSERT INTO models
	('.implode(',', array_keys($modelRow)).')
	VALUES
	(:'.implode(',:', array_keys($modelRow)).')';
dbExec($sql, $modelRow);

// remove the model from the archive
dbExec('delete from models_archive where id = :id', ['id' => $modelId]);

$db->commit();

_success();

