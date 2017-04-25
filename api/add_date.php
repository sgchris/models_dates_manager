<?php

require_once __DIR__.'/init.php';

requestShouldBe('POST');

// receive parameters 
$params = receiveParams(['date'], ['date']);

if (!is_numeric($params['date'])) {
	_exit('date parameter has to be a timestamp');
}

// check that the date is +/- one year
if (!between($params['date'], strtotime('-1 year'), strtotime('+1 year'))) {
	_exit('date is too far away');
}

// check if the date already exists
$date = dbRow(
	'select * from dates_list where date_ts = :date_ts', 
	array(':date_ts' => $params['date'])
);

if (!empty($date)) {
	_exit('the date already exists');
}


// insert the new date
dbExec(
	'insert into dates_list (date_ts, hash) values (:date_ts, :hash)',
	array(
		':date_ts' => $params['date'],
		':hash' => md5(microtime(true)),
	)
);

_success();