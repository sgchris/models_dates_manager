<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['date', 'description'], ['date']);

if (!is_numeric($params['date'])) {
	_exit('date parameter has to be a timestamp');
}

// check if the date already exists
$date = dbRow(
	'select * from dates_list where date_ts = :date_ts', 
	array(':date_ts' => $params['date'])
);

if (empty($date)) {
	_exit('the date does not exist');
}

// check which columns have to be updated
$updateColumns = [];

// check "description"
if (isset($params['description'])) {
	// validate 
	if (!empty($params['description']) && !between(strlen($params['description']), 1, 2047)) {
		_exit('bad description parameter');
	}
	
	$updateColumns['description'] = $params['description'];
}

if (empty($updateColumns)) {
	_exit('nothing to update');
}

// generate the SQL
$sql = 'UPDATE dates_list SET ';
$i = 0;
foreach ($updateColumns as $key => $val) {
	if ($i++ > 0) {
		$sql.= ', ';
	}
	$sql.= $key.' = :'.$key;
}
$sql.= ' WHERE date_ts = :date_ts';

// add the date to the params list
$updateColumns[':date_ts'] = $params['date'];

// execute
dbExec($sql, $updateColumns);

_success();
