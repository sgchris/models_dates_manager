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

// check the destination folder
$destinationFolder = __DIR__.'/../images/upload';
if (!is_dir($destinationFolder)) {
	_exit('No images folder');
}
if (!is_writable($destinationFolder)) {
	_exit('No permissions to the images folder');
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
			array_splice($modelImages, $i, 1);
			break;
		}
	}
}

if (!$foundImage) {
	_exit('Image was not found');
}

// delete the image from the disk
$imageFullPath = $destinationFolder.'/'.$imageUrl;
if (!file_exists($imageFullPath) || !is_writable($imageFullPath)) {
	_exit('Cannot delete the image "'.$imageUrl.'" from the disk - either not exists, or no permissions');
}

$deleteResult = @unlink($imageFullPath);
if (!$deleteResult) {
	_exit('Cannot delete the image "'.$imageUrl.'" from the disk - delete operation failed');
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