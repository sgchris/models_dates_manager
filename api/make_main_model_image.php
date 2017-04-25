<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');

$params = receiveParams(['model_id', 'image_url'], ['model_id', 'image_url']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

$imageUrl = $params['image_url'];
if (strlen($imageUrl) < 5 || strlen($imageUrl) > 255) {
	_exit('bad image_url parameter');
}

// get the model from the DB
$modelRow = $db->prepare('select * from models where id = :model_id');
$result = $modelRow->execute(array(
	':model_id' => $modelId
));
if (!$result) {
	_exit($modelRow->errorInfo());
}

// check that she exists
$modelRow = $modelRow->fetch();
if (!$modelRow) {
	_exit('cannot locate the model');
}

$modelImages = json_decode($modelRow['images']);
$foundImage = false;
if (!empty($modelImages)) {
	foreach ($modelImages as $i => $modelImage) {
		if ($modelImage == $imageUrl) {
			$foundImage = true;
			// remove the image from the array
			array_splice($modelImages, $i, 1);
			
			// prepend the new image to the array
			array_unshift($modelImages, $imageUrl);
			
			break;
		}
	}
}



if (!$foundImage) {
	_exit('Image was not found');
}

// update model's row in the DB
$stmt = $db->prepare('update models set images = :images where id = :model_id');
$result = $stmt->execute(array(
	':images' => json_encode(array_values($modelImages)),
	':model_id' => $modelId,
));

if (!$result) {
	_exit($stmt->errorInfo());
}

_success();