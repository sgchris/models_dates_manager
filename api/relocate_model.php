<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');

$params = receiveParams(['model_id', 'new_location'], ['model_id', 'new_location']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

$newLocation = strtolower($params['new_location']);
if (!in_array($newLocation, ['top', 'bottom'])) {
	_exit('bad new_location parameter - only "top" or "bottom" allowed');
}

if ($newLocation == 'top') {
	// get the max display_order
	$maxDisplayOrder = $db->query('select max(display_order) as max_display_order from models')->fetch();
	if (!$maxDisplayOrder) {
		_exit('cannot get the max display order');
	}
	$maxDisplayOrder = $maxDisplayOrder['max_display_order'] ?? 0;
	
	$newDisplayOrder = $maxDisplayOrder + 1;
} else {
	// get the max display_order
	$minDisplayOrder = $db->query('select min(display_order) as min_display_order from models')->fetch();
	if (!$minDisplayOrder) {
		_exit('cannot get the min display order');
	}
	$minDisplayOrder = $minDisplayOrder['min_display_order'] ?? 0;
	
	$newDisplayOrder = $minDisplayOrder - 1;
}

$stmt = $db->prepare('
	UPDATE models 
	SET display_order = :new_display_order 
	WHERE id = :model_id');
$result = $stmt->execute([
	':new_display_order' => $newDisplayOrder,
	':model_id' => $modelId,
]);
if (!$result) {
	_exit($stmt->errorInfo());
}

_success();