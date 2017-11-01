<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['name'], ['name']);

// basic validation
if (!between(strlen($params['name']), 1, 100)) {
	_exit('invalid list parameter');
}

// check if the model already exists
$model = dbRow(
	'select * from lists where name like :name limit 1',
	['name' => $params['name']]
);
if (!empty($model)) {
	_exit('the list already exists');
}


// generate new model's hash
$newHash = md5(microtime(true));

// add the new model
$stmt = dbExec(
	'insert into lists (name, hash, date_created, models) values (:name, :hash, :date_created, :models)', 
	[
		'name' => $params['name'],
		'hash' => $newHash,
		'date_created' => time(),
		'models' => json_encode(array()),
	]
);

// get all the lists now
$lists = dbQuery('select * from lists order by date_created asc');

foreach ($lists as $i => $list) {
	$lists[$i]['models'] = json_decode($list['models'], true);
}

_success(['lists' => $lists]);

