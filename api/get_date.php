<?php 

// [{"date":1491583999,"excluded_models":[1,3,5]},{"date":1489513999,"excluded_models":[2,3]}]

require_once __DIR__.'/init.php';

requestShouldBe('GET');

$params = receiveParams(['hash'], ['hash']);

$dateData = dbRow('select * from dates_list where hash = :hash', ['hash' => $params['hash']]);
if (!$dateData) {
	_exit('date not found');
}

// convert "excluded_models" to array
$dateData['excluded_models'] = !empty($row['excluded_models']) ? 
	array_filter(explode(',', $row['excluded_models']), 'strlen') :	[];

_success([
	'date' => $dateData,
]);