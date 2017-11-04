<?php 

require_once __DIR__.'/init.php';

requestShouldBe('GET');

setRestrictedAccess();

// get all the models from the archive
$models = dbQuery('
	SELECT * 
	FROM models 
	WHERE is_archive = "1" 
	ORDER by CAST(display_order AS INTEGER) DESC, id DESC
');

// make images as array
foreach ($models as $i => $model) {
	$models[$i]['images'] = json_decode($model['images'], true);
}

_success(['models' => $models]);

