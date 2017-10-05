<?php
/**
 * for every (uploaded) image in the system, create its small version
 * with max dimensions 60x60, and create a "map" file with base64 encoded
 * images
 */

require_once __DIR__.'/init.php';

requestShouldBe('GET');

define('UPLOADED_IMAGES_PATH', __DIR__.'/../images/upload');
define('SMALL_IMAGES_PATH', UPLOADED_IMAGES_PATH.'/small');
define('JSON_DATA_PATH', UPLOADED_IMAGES_PATH.'/small/images.json');

// validate small folder
checkThumbnailsFolder();

// create thumbnails
createThumbnails();

// create JSON file
createJsonFile();

readfile(JSON_DATA_PATH);

////////////////////////////////////////////////////////////////////////////////

/**
 * Check if small folder exists, if not, try to create it
 * @return  
 */
function checkThumbnailsFolder() {
	if (!is_dir(SMALL_IMAGES_PATH)) {
		if (!is_writable(SMALL_IMAGES_PATH)) {
			_exit('cannot create small folder');
		}
		 
		$result = mkdir(SMALL_IMAGES_PATH, 0664);
		if (!$result) {
			_exit('creating small folder failed');
		}
	}
	
	if (!is_writable(SMALL_IMAGES_PATH)) {
		_exit('bad permissions on upload folder');
	} 
}


/**
* Resize an image and keep the proportions
* 
* @author Allison Beckwith <allison@planetargon.com>
* @param string $filename
* @param integer $max_width
* @param integer $max_height
* @return image
*/
function resizeImage($filename, $destinationFile, $max_width, $max_height) {
	if (!file_exists($filename)) {
		return false;
	}
	
	if (!preg_match('/\.jpg$/i', $filename)) {
		return false;
	}
	
    list($orig_width, $orig_height) = @getimagesize($filename);
	if (!$orig_width || !$orig_height) {
		return false;
	}

    $width = $orig_width;
    $height = $orig_height;

    # taller
    if ($height > $max_height) {
        $width = ($max_height / $height) * $width;
        $height = $max_height;
    }

    # wider
    if ($width > $max_width) {
        $height = ($max_width / $width) * $height;
        $width = $max_width;
    }

    $image_p = imagecreatetruecolor($width, $height);

    $image = imagecreatefromjpeg($filename);

    imagecopyresampled($image_p, $image, 0, 0, 0, 0, 
                                     $width, $height, $orig_width, $orig_height);

	if (!is_writable(dirname($destinationFile))) {
		_exit(dirname($destinationFile).' is not writeable');
	} else {
		touch($destinationFile);
	}
	
    return imagejpeg($image_p, $destinationFile);
}


/**
 * create small images 
 * @return  
 */
function createThumbnails() {
	// loop thru all the images, and create small version of every image
	foreach (scandir(UPLOADED_IMAGES_PATH) as $imageFile) {
		// skip the dots
		if ($imageFile == '.' || $imageFile == '..') {
			continue;
		}
		
		if (!is_file(UPLOADED_IMAGES_PATH.'/'.$imageFile)) {
			continue;
		}
		 
		// skip the small files
		if (preg_match('/60x60\.jpg$/i', $imageFile)) {
			continue;
		}
		 
		$imagePath = UPLOADED_IMAGES_PATH.'/'.$imageFile;
		if (!is_readable($imagePath)) {
			continue;
		}
		
		$newImagePath = realpath(dirname($imagePath)).'/small/'.preg_replace('/\.jpg$/i', '60x60.jpg', basename($imagePath));
		if (!file_exists($newImagePath)) {
			$resizeResult = resizeImage($imagePath, $newImagePath, 60, 60);
		}
	}
}

/**
 * Create one json file with information about all the small files
 * @return  
 */
function createJsonFile() {
	$data = array();
	
	if (file_exists(JSON_DATA_PATH) && is_readable(JSON_DATA_PATH)) {
		$data = json_decode(file_get_contents(JSON_DATA_PATH), true);
		if (!$data) {
			$data = array();
		}
	}
	
	// loop thru all the images, and create small version of every image
	foreach (scandir(SMALL_IMAGES_PATH) as $imageFile) {
		// skip the dots
		if ($imageFile == '.' || $imageFile == '..') {
			continue;
		}
		
		$imageFilePath = UPLOADED_IMAGES_PATH.'/small/'.$imageFile;
		
		if (!is_file($imageFilePath)) {
			continue;
		}
		 
		// skip the small files
		if (!preg_match('/60x60\.jpg$/i', $imageFile)) {
			continue;
		}
		 
		$imagePath = $imageFilePath;
		if (!is_readable($imagePath)) {
			continue;
		}
		
		if (!isset($data[$imageFile])) {
			$data[$imageFile] = 'data:image/jpeg;base64,'.base64_encode(file_get_contents($imageFilePath));
		}
	}
	
	$result = file_put_contents(JSON_DATA_PATH, 'var SMALL_IMAGES_DATA='.json_encode($data, JSON_PRETTY_PRINT));
	if (!$result) {
		_exit('failed writing to the JSON file');
	}
}