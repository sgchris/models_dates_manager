<?php 

require_once __DIR__.DIRECTORY_SEPARATOR.'init.php';

requestShouldBe('POST');

$params = receiveParams(['name', 'description'], ['name']);

// validate the parameter
if (!between(strlen($params['name']), 2, 150)) {
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
	WHERE name = :name', 
	['name' => $params['name']]
);

if (!empty($modelsCategories)) {
	_exit('The models category already exists');
}

dbExec(
	'INSERT INTO models_categories (name, description) values (:name, :description)', 
	[
		'name' => $params['name'],
		'description' => $params['description'],
	]
);

_success();
