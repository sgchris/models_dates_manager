<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['name', 'category'], ['name']);

// basic validation
if (!between(strlen($params['name']), 1, 100)) {
	_exit('invalid name parameter');
}

if (isset($params['category']) && !(is_numeric($params['category']) || strlen($params['category']) == 0)) {
	_exit('bad category parameter. should be number or empty string');
}

// define model's category
$category = isset($params['category']) && is_numeric($params['category']) ? $params['category'] : NULL;

// check if the model already exists
$model = dbRow(
	'select * from models where name like :name limit 1',
	['name' => $params['name']]
);
if (!empty($model)) {
	_exit('the model already exists');
}

// get the max display order
$maxDisplayOrder = dbRow(
	'select max(display_order) as max_display_order from models'
);
if (!$maxDisplayOrder) {
	_exit('cannot get the max display order');
}
$maxDisplayOrder = $maxDisplayOrder['max_display_order'] ?? 0;

// add the new model
$stmt = dbExec(
	'insert into models (name, display_order, hash, category) values (:name, :display_order, :hash, :category)', 
	[
		'name' => $params['name'],
		'display_order' => $maxDisplayOrder + 1,
		'hash' => md5(microtime(true)),
		'category' => $category,
	]
);

_success();