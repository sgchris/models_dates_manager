<?php 

require_once __DIR__.DIRECTORY_SEPARATOR.'init.php';

requestShouldBe('POST');

$params = receiveParams(['name'], ['name']);

// valudate the parameter
if (!between(strlen($params['name']), 2, 150)) {
	_exit('name parameter is not valid');
}

$modelsCategories = dbRow('select * from models_categories where name = :name', ['name' => $params['name']]);

if (!empty($modelsCategories)) {
	_exit('The models category already exists');
}

dbExec('insert into models_categories (name) values (:name)', ['name' => $params['name']]);

_success();