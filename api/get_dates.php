<?php 

// [{"date":1491583999,"excluded_models":[1,3,5]},{"date":1489513999,"excluded_models":[2,3]}]

require_once __DIR__.DIRECTORY_SEPARATOR.'init.php';

$results = $db->query('select * from dates_list order by date_ts asc');
if (!$results) {
	_exit($db->errorInfo());
}

$results = $results->fetchAll();
foreach ($results as $i => $row) {
	$results[$i]['excluded_models'] = !empty($row['excluded_models']) ? 
		array_filter(explode(',', $row['excluded_models']), 'strlen') :
		[];
}

_success([
	'dates' => $results,
]);