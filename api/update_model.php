<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');
setRestrictedAccess();

$params = receiveParams(
	['model_id', 'name', 'category', 'phone', 'instagram', 'notes', 'private_notes', 'tags', 'color'], 
	['model_id']
);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

// get the model from the DB
$modelRow = getModelDetails($params['model_id']);

// prepare the patameters tp update
$updateParams = [];

// check "name" parameter
if (isset($params['name'])) {
	
	// validate 
	if (!empty($params['name']) && !between(strlen($params['name']), 2, 255)) {
		_exit('bad name parameter');
	}
	
	$updateParams['name'] = $params['name'];
}

// check "category" parameter
if (isset($params['category'])) {
	if (!empty($params['category'])) {
		// validate 
		if (!is_numeric($params['category'])) {
			_exit('bad category parameter');
		}
		
		$updateParams['category'] = $params['category'];
	} else {
		$updateParams['category'] = '';
	}
}

// check "category" parameter
if (isset($params['phone'])) {
	if (!empty($params['phone'])) {
		$params['phone'] = trim($params['phone']);
		
		// validate 
		if (!preg_match('/^\d+$/', $params['phone'])) {
			_exit('bad phone parameter. only numbers allowed');
		}
		
		$updateParams['phone'] = $params['phone'];
	} else {
		$updateParams['phone'] = '';
	}
}

// check "category" parameter
if (isset($params['instagram'])) {
	if (!empty($params['instagram'])) {
		$params['instagram'] = trim($params['instagram']);
		
		$updateParams['instagram'] = $params['instagram'];
	} else {
		$updateParams['instagram'] = '';
	}
}

// check "notes" parameter
if (isset($params['notes'])) {
	// validate 
	if (!empty($params['notes']) && !between(strlen($params['notes']), 1, 2047)) {
		_exit('bad notes parameter');
	}
	
	$updateParams['notes'] = $params['notes'];
}

// check "notes" parameter
if (isset($params['private_notes'])) {
	// validate 
	if (!empty($params['private_notes']) && !between(strlen($params['private_notes']), 1, 2047)) {
		_exit('bad private_notes parameter');
	}
	
	$updateParams['private_notes'] = $params['private_notes'];
}

// check "notes" parameter
if (isset($params['tags'])) {
	// validate 
	if (!empty($params['tags']) && !between(strlen($params['tags']), 1, 2047)) {
		_exit('bad tags parameter');
	}
	
	$updateParams['tags'] = $params['tags'];
}

// check "color" parameter
if (isset($params['color'])) {
	// validate 
	if (!empty($params['color']) && !between(strlen($params['color']), 1, 15)) {
		_exit('bad color parameter');
	}
	$updateParams['color'] = $params['color'];
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