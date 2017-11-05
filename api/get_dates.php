<?php 

// [{"date":1491583999,"excluded_models":[1,3,5]},{"date":1489513999,"excluded_models":[2,3]}]

require_once __DIR__.DIRECTORY_SEPARATOR.'init.php';

requestShouldBe('GET');

setRestrictedAccess();

$results = dbQuery('select * from dates_list order by date_ts asc');

foreach ($results as $i => $row) {
	// available models
	$results[$i]['available_models'] = 
		!empty($results[$i]['available_models']) && 
		($decodedData = json_decode($results[$i]['available_models'], $__assoc = true)) !== false && 
		is_array($decodedData) ? 
		array_filter($decodedData, 'strlen') : [];

	$results[$i]['chosen_models'] = 
		!empty($results[$i]['chosen_models']) && 
		($decodedData = json_decode($results[$i]['chosen_models'], $__assoc = true)) !== false && 
		is_array($decodedData) ? 
		array_filter($decodedData, 'strlen') : [];

	$results[$i]['excluded_models'] = 
		!empty($results[$i]['excluded_models']) && 
		($decodedData = json_decode($results[$i]['excluded_models'], $__assoc = true)) !== false && 
		is_array($decodedData) ? 
		array_filter($decodedData, 'strlen') : [];
}

_success([
	'dates' => $results,
]);
