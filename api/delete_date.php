<?php

require_once __DIR__.'/init.php';

// receive parameters 
$params = receiveParams(['date'], ['date']);

if (!is_numeric($params['date'])) {
	_exit('date parameter has to be a timestamp');
}

// check if the date already exists
$stmt = $db->prepare('select * from dates_list where date_ts = :date_ts');

$result = $stmt->execute(array(
	':date_ts' => $params['date']
));
if (!$result) {
	_exit(json_encode($stmt->errorInfo()));
}
if (!$stmt->fetch()) {
	_exit('the date does not exists');
}

// insert the new date
$stmt = $db->prepare('delete from dates_list where date_ts = :date_ts');
$result = $stmt->execute(array(
	':date_ts' => $params['date']
));
if (!$result) {
	_exit(json_encode($stmt->errorInfo()));
}

_success();


