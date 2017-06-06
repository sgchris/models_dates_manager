<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');
setRestrictedAccess();

$params = receiveParams(['model_id', 'name', 'notes'], ['model_id']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

// get the model from the DB
$modelRow = getModelDetails($params['model_id']);

// prepare the patameters tp update
$updateParams = [];

// check "name" parameter
if (!empty($params['name'])) {
	
	// validate 
	if (!between(strlen($params['name']), 2, 255)) {
		_exit('bad name parameter');
	}
	
	$updateParams['name'] = $params['name'];
}

// check "notes" parameter
if (!empty($params['notes'])) {
	// validate 
	if (!between(strlen($params['notes']), 1, 2047)) {
		_exit('bad notes parameter');
	}
	
	$updateParams['notes'] = $params['notes'];
}

// perform the update (only one parameter is model_id)
if (empty($updateParams)) {
	_exit('nothing to update');
}

// generate the SQL
$sql = 'UPDATE models SET ';
$i = 0;
foreach ($updateParams as $key => $val) {
	if ($i++ > 0) {
		$sql.= ', ';
	}
	$sql.= $key.' = :'.$key;
}
$sql.= ' WHERE id = :model_id';

// add the model ID to the params list
$updateParams[':model_id'] = $modelId;
dbExec($sql, $updateParams);

_success();