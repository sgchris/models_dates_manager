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

// get model name
$modelName = getAlphanumericName($modelRow['name']);
if (empty($modelName)) {
	$modelName = getRandHash($__length = 12);
}

$errors = [];
$successfulFiles = 0;
$videoExtensions = ['mp4', 'wmv', 'mpg', 'mpeg'];
if (!empty($_FILES)) {
	foreach ($_FILES as $file) {
		
		// check errors
		if (!empty($file['error']) || empty($file['size'])) {
			$errors[] = $file['error'] ? codeToMessage($file['error']) : 'Cannot upload file '.$file['name'];
			continue;
		}
		
		// check the upload
		if (!is_uploaded_file($file['tmp_name'])) {
			$errors[] = 'Upload file '.$file['name'].' failed';
			continue;
		}
		
		$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
		if (empty($fileExtension)) {
			$errors[] = 'Cannot get file extension of '.$file['name'];
			continue;
		}

		$isVideo = in_array($fileExtension, $videoExtensions);
		$hash = getRandHash();
		if ($isVideo) {
			$newImageFileName = uniqid('model_vid_').$file['name'];
		} else {
			// check that this is an image
			list ($width, $height) = getimagesize($file['tmp_name']);
			if (!$width || !$height) {
				$errors[] = 'The file '.$file['name'].' is not an image';
				continue;
			}
			
			// resize the oiginal file, if needed
			if ($width > 1920 || $height > 1280) {
				$r_hash = getRandHash();
				$tempImgPath = sys_get_temp_dir().'/resized_img_'.$r_hash;
				
				// resize
				$result = resizeImage(
					$file['tmp_name'],
					$file['tmp_name'],
					1920,
					1280
				);
				
				// renew the w/h values
				list($width, $height) = getimagesize($file['tmp_name']);
			}

			$newImageFileName = "model_{$modelName}_{$hash}_{$width}x{$height}.jpg";
			
			// relocate the file
			if (!move_uploaded_file($file['tmp_name'], $destinationFolder.'/'.$newImageFileName)) {
				$errors[] = 'Could not relocate uploaded file '.$file['name'];
				continue;
			}
			
			// create small image
			$smallImagePath = $destinationFolder.'/small/'.$newImageFileName;
			$smallImagePath = str_ireplace('.jpg', '_small.jpg', $smallImagePath);
			resizeImage($destinationFolder.'/'.$newImageFileName, $smallImagePath, 60, 60);
			
			// create medium image
			$mediumImagePath = $destinationFolder.'/medium/'.$newImageFileName;
			$mediumImagePath = str_ireplace('.jpg', '_medium.jpg', $mediumImagePath);
			resizeImage($destinationFolder.'/'.$newImageFileName, $mediumImagePath, 180, 180);
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

// update images map JSON file
$phpExecutable = trim(shell_exec('/usr/bin/which php'));
_success([
	'images_map_result' => shell_exec(escapeshellcmd($phpExecutable).' '.escapeshellarg(realpath(__DIR__.'/get_images_map.php'))),
]);


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