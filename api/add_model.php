<?php

require_once __DIR__.'/init.php';

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

// add the new model
$stmt = $db->prepare('insert into models (name) values (:name)');
$result = $stmt->execute(['name' => $params['name']]);
if (!$result) {
	_exit($stmt->errorInfo());
}

_success();