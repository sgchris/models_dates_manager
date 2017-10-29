<?php 

require_once __DIR__.DIRECTORY_SEPARATOR.'init.php';

requestShouldBe('POST');

$params = receiveParams(['id', 'name', 'description'], ['id']);

// validate the parameter
if (isset($params['name']) && !between(strlen($params['name']), 2, 150)) {
	_exit('name parameter is not valid');
}

// validate the description parameter
if (isset($params['description']) && !between(strlen($params['name']), 2, 2048)) {
	_exit('description parameter is not valid');
} elseif (!isset($params['description'])) {
	$params['description'] = '';
}

// check if the model category already exists
$modelsCategories = dbRow('
	SELECT * 
	FROM models_categories 
	WHERE id = :id', 
	['id' => $params['id']]
);

if (empty($modelsCategories)) {
	_exit('The models category does not exist');
}

dbExec(
	'UPDATE models_categories set name=:name, description=:description
	WHERE id=:id', 
	[
		'id' => $params['id'],
		'name' => $params['name'],
		'description' => $params['description'],
	]
);

_success();
