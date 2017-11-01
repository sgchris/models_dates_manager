<?php 

// {"date":1491583999,"excluded_models":[1,3,5]},{"date":1489513999,"excluded_models":[2,3]}

require_once __DIR__.'/init.php';

requestShouldBe('GET');

// guest user is not allowed to get list of all the lists
setRestrictedAccess();

$lists = dbQuery('select * from lists order by date_created asc');

foreach ($lists as $i => $list) {
	$lists[$i]['models'] = json_decode($list['models'], true);
}

_success(['lists' => $lists]);
