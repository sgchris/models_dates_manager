<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

setRestrictedAccess();

// receive parameters 
$params = receiveParams(['date'], ['date']);

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

// insert the new date
$stmt = dbExec('delete from dates_list where date_ts = :date_ts', array(
	':date_ts' => $params['date']
));

_success();


