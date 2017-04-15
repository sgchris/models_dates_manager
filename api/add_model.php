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
$stmt = $db->prepare('select * from models where name like :name limit 1');
$result = $stmt->execute(['name' => $params['name']]);
if (!$result) {
	_exit($stmt->errorInfo());
}
if ($stmt->fetch()) {
	_exit('the model already exists');
}

// get the max display order
$maxDisplayOrder = $db->query('select max(display_order) as max_display_order from models')->fetch();
if (!$maxDisplayOrder) {
	_exit('cannot get the max display order');
}
$maxDisplayOrder = $maxDisplayOrder['max_display_order'] ?? 0;

// add the new model
$stmt = $db->prepare('insert into models (name, display_order) values (:name, :display_order)');
$result = $stmt->execute([
	'name' => $params['name'],
	'display_order' => $maxDisplayOrder + 1,
]);

if (!$result) {
	_exit($stmt->errorInfo());
}

_success();