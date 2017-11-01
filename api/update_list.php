<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['list_id', 'name'], ['list_id']);

if (!is_numeric($params['list_id'])) {
	_exit('date parameter has to be a timestamp');
}

$listId = $params['list_id'];

// check if the date already exists
$list = dbRow(
	'select * from lists where id = :id', 
	['id' => $listId]
);

if (empty($list)) {
	_exit('the list does not exist');
}

// check which columns have to be updated
$updateColumns = [];

// check "name"
if (isset($params['name'])) {
	// validate 
	if (!empty($params['name']) && !between(strlen($params['name']), 1, 255)) {
		_exit('bad name parameter');
	}
	
	$updateColumns['name'] = $params['name'];
}

if (empty($updateColumns)) {
	_exit('nothing to update');
}

// generate the SQL
$sql = 'UPDATE lists SET ';
$i = 0;
foreach ($updateColumns as $key => $val) {
	if ($i++ > 0) {
		$sql.= ', ';
	}
	$sql.= $key.' = :'.$key;
}
$sql.= ' WHERE id = :id';

// add the list id to the params list
$updateColumns['id'] = $listId;

// execute
dbExec($sql, $updateColumns);

_success();
