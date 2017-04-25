<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');

$params = receiveParams(['model_id'], ['model_id']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

// check the destination folder
$destinationFolder = __DIR__.'/../images/upload';
if (!is_dir($destinationFolder)) {
	
	// check if it's possible to create the folder
	if (!is_writable(dirname($destinationFolder))) {
		_exit('cannot create images folder - no permissions');
	}
	
	// create the folder
	$result = @mkdir($destinationFolder);
	if (!$result) {
		_exit('cannot create images folder - operation failed');
	}
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

$errors = [];
$successfulFiles = 0;
if (!empty($_FILES)) {
	foreach ($_FILES as $file) {
		// check errors
		if (!empty($file['error']) || empty($file['size'])) {
			$errors[] = $file['error'] ?? 'Cannot upload file '.$file['name'];
			continue;
		}
		
		// check that this is an image
		list ($width, $height) = getimagesize($file['tmp_name']);
		if (!$width || !$height) {
			$errors[] = 'The file '.$file['name'].' is not an image';
			continue;
		}
		
		// check the upload
		if (!is_uploaded_file($file['tmp_name'])) {
			$errors[] = 'Upload file '.$file['name'].' failed';
			continue;
		}
		
		// relocate the file
		$newImageFileName = uniqid('model_').$file['name'];
		if (!move_uploaded_file($file['tmp_name'], $destinationFolder.'/'.$newImageFileName)) {
			$errors[] = 'Could not relocate uploaded file '.$file['name'];
			continue;
		}
		
		// update the DB
		$modelImages = json_decode($modelRow['images']) ?? [];
		$modelImages[] = $newImageFileName;
		$modelImages = json_encode(array_values($modelImages));
		$stmt = $db->prepare('update models set images = :images where id = :model_id');
		$result = $stmt->execute(array(
			':model_id' => $modelId,
			':images' => $modelImages,
		));
		if (!$result) {
			$errors[] = $modelRow->errorInfo();
			continue;
		}
		
		$successfulFiles ++;
	}
}

if (!$successfulFiles && !empty($errors)) {
	_exit(['errors' => $errors]);
}

_success();