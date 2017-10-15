<?php

define ('IMAGES_FOLDER', realpath(__DIR__.'/../../images/upload/'));

require_once __DIR__.'/functions.php';

$db = new PDO('sqlite:'.__DIR__.'/../db/models.db');
if (!$db) {
	_exit('cannot connect to the database');
}


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
		echo "Renaming '{$fullImagePath}' to {$newNameFullPath}\n";
		$renameResult = rename($fullImagePath, $newNameFullPath);
		if (!$renameResult) {
			echo "Renaming failed!\n";
		}
		
		// convert the small image
		$fullImagePath_small = dirname($fullImagePath).'/small/'.str_ireplace('.jpg', '60x60.jpg', basename($fullImagePath));
		if (file_exists($fullImagePath_small)) {
			$newNameFullPath_small = dirname($fullImagePath_small).'/'.str_replace('.jpg', '_small.jpg', $newName);
			echo "Renaming [SMALL] '{$fullImagePath_small}' to {$newNameFullPath_small}\n";
			
			$renameResult = rename($fullImagePath_small, $newNameFullPath_small);
			if (!$renameResult) {
				echo "Renaming failed!\n";
			}
		}
		
		// convert the medium image
		$fullImagePath_medium = dirname($fullImagePath).'/medium/'.str_ireplace('.jpg', '180x180.jpg', basename($fullImagePath));
		if (file_exists($fullImagePath_medium)) {
			$newNameFullPath_medium = dirname($fullImagePath_medium).'/'.str_replace('.jpg', '_medium.jpg', $newName);
			echo "Renaming [MEDIUM] '{$fullImagePath_medium}' to {$newNameFullPath_medium}\n";
			
			$renameResult = rename($fullImagePath_medium, $newNameFullPath_medium);
			if (!$renameResult) {
				echo "Renaming failed!\n";
			}
		}
		
		$images[$imgNumber] = $newName;
	}
	
	dbExec($db, 'update models set images = :images where id = :id', array(
		'id' => $model['id'],
		'images' => json_encode($images),
	));
	
	echo "Updated model {$modelName}\n";
}

echo "Done!\n";

//////////////////////////////////////////////////

/**
 * Get name that consists only of letters and numbers
 * @param string $name 
 * @return string
 */
function getAlphanumericName($name) {
	return preg_replace('/[^A-Za-z0-9]+/i', '', $name);
}

/**
 * Get random hash
 * @param integer $length 
 * @return string
 */
function getRandHash($length = 16) {
	return substr(md5(mt_rand(0, pow(10, 10))), 0, $length);
}