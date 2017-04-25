<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

// receive parameters 
$params = receiveParams(['name'], ['name']);

// basic validation
if (!between(strlen($params['name']), 1, 100)) {
	_exit('invalid name parameter');
}

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
	'insert into models (name, display_order) values (:name, :display_order)', 
	[
		'name' => $params['name'],
		'display_order' => $maxDisplayOrder + 1,
	]
);

_success();