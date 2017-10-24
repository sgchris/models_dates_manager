<?php

define ('IMAGES_FOLDER', realpath(__DIR__.'/../../images/upload/'));

require_once __DIR__.'/functions.php';

$db = new PDO('sqlite:'.__DIR__.'/../db/models.db');
if (!$db) {
	_exit('cannot connect to the database'); }


$models = dbQuery($db, 'select * from models');
$i=0;
foreach ($models as $model) {
	if (!isset($model['images']) || empty($model['images'])) {
		continue;
	}
	
	$images = json_decode($model['images'], true);
	if (!$images || empty($images)) {
		continue;
	}
	
	// get valid model name
	$modelName = getAlphanumericName($model['name']);
	if (empty($modelName)) {
		$modelName = getRandHash($__length = 12);
		echo "Model name is empty. Generating name '{$modelName}'\n";
	}
	foreach ($images as $imgNumber => $image) {
		
		// validate the file extension
		if (!preg_match('/\.jpg$/i', $image)) {
			continue;
		}
		
		$fullImagePath = IMAGES_FOLDER.'/'.$image;
		if (!file_exists($fullImagePath) || !is_readable($fullImagePath)) {
			echo "Image '{$fullImagePath}' is not accessible\n";
			continue;
		}
		
		// get image details
		list($w, $h) = getimagesize($fullImagePath);
		if (!$w || !$h) {
			echo "Image '{$fullImagePath}' is not an image\n";
			continue;
		}
		
		// create new name
		$hash = getRandHash();
		$newName = "model_{$modelName}_{$hash}_{$w}x{$h}.jpg";
		
		
		// convert the regular image
		$newNameFullPath = dirname($fullImagePath).'/'.$newName;
		echo "Renaming:\n{$fullImagePath}\n{$newNameFullPath}\n";
		$renameResult = rename($fullImagePath, $newNameFullPath);
		if (!$renameResult) {
			echo "Renaming failed!\n";
		}
		
		// convert the small image
		$newNameFullPath_small = dirname($newNameFullPath).'/small/'.str_ireplace('.jpg', '_small.jpg', basename($newNameFullPath));
		echo "Checking {$newNameFullPath_small}\n";
		if (!file_exists($newNameFullPath_small)) {
			resizeImage($newNameFullPath, $newNameFullPath_small, 60, 60);
			echo "Created {$newNameFullPath_small}\n";
		} else {
			echo "The file {$newNameFullPath_small} exists!\n";
		}
		
		// convert the medium image
		$newNameFullPath_medium = dirname($newNameFullPath).'/medium/'.str_ireplace('.jpg', '_medium.jpg', basename($newNameFullPath));
		if (!file_exists($newNameFullPath_medium)) {
			resizeImage($newNameFullPath, $newNameFullPath_medium, 180, 180);
			echo "Created {$newNameFullPath_medium}\n";
		} else {
			echo "The file {$newNameFullPath_medium} exists!\n";
		}
		
		$images[$imgNumber] = $newName;
	}
	
	$dbRes = dbExec($db, 'update models set images = :images where id = :id', array(
		'id' => $model['id'],
		'images' => json_encode($images),
	));
	
	echo "Updated model {$modelName} - db res = ", var_export($dbRes), "\n";
}

echo "Done!\n";
