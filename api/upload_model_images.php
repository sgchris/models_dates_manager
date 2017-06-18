<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');

setRestrictedAccess();

$params = receiveParams(['model_id'], ['model_id']);

$modelId = $params['model_id'];
if (!is_numeric($modelId) || !($modelId > 0)) {
	_exit('bad model_id parameter');
}

// check the destination folder
$destinationFolder = IMAGES_UPLOAD_PATH;
checkUploadFolder();


// get the model from the DB
$modelRow = getModelDetails($modelId);

$errors = [];
$successfulFiles = 0;
if (!empty($_FILES)) {
	foreach ($_FILES as $file) {
		
		// check errors
		if (!empty($file['error']) || empty($file['size'])) {
			$errors[] = $file['error'] ? codeToMessage($file['error']) : 'Cannot upload file '.$file['name'];
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
		dbExec('update models set images = :images where id = :model_id', array(
			':model_id' => $modelId,
			':images' => $modelImages,
		));
		
		$successfulFiles ++;
	}
}

if (!$successfulFiles && !empty($errors)) {
	_exit(['errors' => $errors]);
}

_success();


///////////////////////////////

function codeToMessage($code) { 
	switch ($code) { 
		case UPLOAD_ERR_INI_SIZE: 
			$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
			break; 
		case UPLOAD_ERR_FORM_SIZE: 
			$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
			break; 
		case UPLOAD_ERR_PARTIAL: 
			$message = "The uploaded file was only partially uploaded"; 
			break; 
		case UPLOAD_ERR_NO_FILE: 
			$message = "No file was uploaded"; 
			break; 
		case UPLOAD_ERR_NO_TMP_DIR: 
			$message = "Missing a temporary folder"; 
			break; 
		case UPLOAD_ERR_CANT_WRITE: 
			$message = "Failed to write file to disk"; 
			break; 
		case UPLOAD_ERR_EXTENSION: 
			$message = "File upload stopped by extension"; 
			break; 

		default: 
			$message = "Unknown upload error"; 
			break; 
	} 
	return $message; 
} 