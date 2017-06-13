<?php 

require_once __DIR__.DIRECTORY_SEPARATOR.'init.php';

requestShouldBe('GET');

$modelsCategories = dbQuery('select * from models_categories');

if (!is_array($modelsCategories)) {
	_exit('Cannot read models categories');
}

_success(['models_categories' => $modelsCategories]);