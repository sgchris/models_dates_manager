<?php 

require_once __DIR__.DIRECTORY_SEPARATOR.'init.php';

requestShouldBe('POST');

$params = receiveParams(['id'], ['id']);

// valudate the parameter
if (!is_numeric($params['id'])) {
	_exit('id parameter is not valid');
}

$modelsCategories = dbRow('select * from models_categories where id = :id', ['id' => $params['id']]);

if (empty($modelsCategories)) {
	_exit('The models category was not found');
}

dbExec('delete from models_categories where id = :id', ['id' => $params['id']]);

_success();