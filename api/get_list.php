<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['hash'], ['hash']);

$list = dbRow('select * from lists where hash = :hash', ['hash' => $params['hash']]);
if (!$list) {
	_exit('list not found');
}

// convert "available_models" to array
$list['models'] = !empty($list['models']) ? 
	array_filter(
		json_decode($list['models'], $__assoc = true), 
		'strlen'
	) : [];

// get models hashes
$models = dbQuery('select * from models where id in ('.implode(',', $list['models']).')');
$hashes = array_map(function($model) {
	return $model['hash'];
}, $models);

_success([
	'list' => $list,
	'models_hashes' => $hashes
]);
