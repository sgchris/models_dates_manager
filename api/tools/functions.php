<?php

/**
 * 
 * @param <unknown> $errorStr 
 * @return  
 */
function _exit($errorStr) {
	echo json_encode(['result' => 'error', 'error' => $errorStr]);
	exit;
}

/**
 * return success message JSON
 * @param string|number|array $successData 
 * @return null
 */
function _success($successData = null) {
	if (is_null($successData)) {
		echo json_encode(['result' => 'ok']);
	} elseif (is_string($successData) || is_numeric($successData)) {
		echo json_encode(['result' => 'ok', 'message' => $successData]);
	} elseif (is_array($successData)) {
		echo json_encode(array_merge($successData, ['result' => 'ok']));
	}
	
	exit;
}

/**
 * Log activity
 * @param string log - unlimited number of arguments
 * @return bool
 */
function _log() {
	// check log file permissions
	if (!file_exists(LOG_FILE_PATH) && !is_writable(dirname(LOG_FILE_PATH))) {
		_exit('Cannot create log file');
	} elseif (file_exists(LOG_FILE_PATH) && !is_writable(LOG_FILE_PATH)) {
		_exit('Cannot modify log file');
	}
	
	
	$logElements = [];
	if (func_num_args() > 0) {
		foreach (func_get_args() as $arg) {
			if (is_scalar($arg)) {
				$logElements[] = $arg;
			} elseif (is_array($arg)) {
				$logElements[] = json_encode($arg);
			}
		}
	}
	
	$parsedUrl = parse_url($_SERVER['REQUEST_URI']);
	$apiFileName = basename($parsedUrl['path']);
	
	$logStr = date('d.M.Y H:i:s') . ' ' . $apiFileName . ': ' . implode(' ', $logElements) . "\r\n";
	$res = @file_put_contents(LOG_FILE_PATH, $logStr, FILE_APPEND);
	return (is_numeric($res) && $res > 0);
}

/**
 * Check the request method
 * @param <unknown> $requestMethod 
 * @return  
 */
function requestShouldBe($requestMethod) {
	$requestMethod = strtoupper($requestMethod);
	if ($_SERVER['REQUEST_METHOD'] != $requestMethod) {
		_exit('should be '.$requestMethod.' request');
	}
}

/**
 * Get list of parameters from the request params (POST or GET)
 * @param array $paramsList 
 * @param array $mandatoryParams 
 * @return  
 */
function receiveParams(array $paramsList, array $mandatoryParams = array()) {
	$params = array();
	if (!empty($paramsList)) {
		foreach($paramsList as $paramName) {
			$params[$paramName] = (isset($_REQUEST[$paramName])) ? $_REQUEST[$paramName] : '';
		}
	}
	
	// check mandatory params
	foreach ($mandatoryParams as $mandatoryParam) {
		if (empty($params[$mandatoryParam])) {
			_exit('missing '.$mandatoryParam.' parameter');
		}
	}
	
	return $params;
}

/**
 * Check the images upload folder if it's writeable
 * @return
 */
function checkUploadFolder() {
	$uploadFolder = IMAGES_UPLOAD_PATH;
	
	// check if the folder exists
	if (!file_exists($uploadFolder)) {
		if (is_writeable(dirname($uploadFolder))) {
			$mkdirResult = @mkdir($uploadFolder);
			if ($mkdirResult === false) {
				_exit('could not create upload folder though its folder is writeable');
			}
		} else {
			_exit('no permissions to create upload folder');
		}
	}
	
	// check if uploading image is possible
	if (!is_writeable($uploadFolder)) {
		_exit('no write permissions to the images upload folder');
	}
}

/**
 * Take base64 encoded images in "extra_data", create images, and replace the content
 * in `params` array
 * with their URL
 * @param array $params 
 * @return  
 */
function uploadImages(&$params) {
	if (!empty($params['extra_data'])) {

		// decode extra parameters
		$params['extra_data'] = json_decode($params['extra_data'], true);
		
		// check if "additional" images were uploaded
		if (isset($params['extra_data']['additionalPictures']) && !empty($params['extra_data']['additionalPictures'])) {
			
			// check that the images can be created
			checkUploadFolder();
			
			// set the upload folder (defined in the config file)
			$uploadFolder = IMAGES_UPLOAD_PATH;
			
			// store every passed image (it's passed as a base64 encoded string)
			foreach ($params['extra_data']['additionalPictures'] as $i => $additionalPicture) {
				$additionalPictureContent = preg_replace('%^data.*?base64\,%i', '', $additionalPicture, -1, $totalReplaced);
				
				// check if replaced the base64 encoded shit. If not, probably that's a URL to the image
				if ($totalReplaced == 0) {
					continue;
				}
				
				// decode the content
				$imageContent = base64_decode($additionalPictureContent);
				
				// generate image name
                $pureName = preg_replace('%[^A-Za-z0-9]+%', '', $params['name']);
				$imageName = uniqid($pureName.'_').'.jpg';
				$imagePath = $uploadFolder.DIRECTORY_SEPARATOR.$imageName;
				
				
				// create the image
				$filePutContentsResult = file_put_contents($imagePath, $imageContent);
				if ($filePutContentsResult === false) {
					_exit('could not save image #' . $i);
				}
				
				// change the base64 content with the new image path
				$params['extra_data']['additionalPictures'][$i] = IMAGES_UPLOAD_URL.'/'.$imageName;
			}
			
			// check if the main image was set, if not, set the first uploaded image as the main image
			if (empty($params['picture'])) {
				$params['picture'] = array_shift($params['extra_data']['additionalPictures']);
			}
		}
		
		// encode the extra parameters back to a string
		$params['extra_data'] = json_encode($params['extra_data']);
	}
}

/**
 * Check if the value is in the range
 * @param <unknown> $var 
 * @param <unknown> $min 
 * @param <unknown> $max 
 * @param <unknown> $includingMin 
 * @param <unknown> $includingMax 
 * @return  
 */
function between($var, $min, $max, $includingMin = true, $includingMax = true) {
	$condA = $var > $min;
	if ($includingMin) {
		$condA = $var >= $min;
	}
	
	$condB = $var < $max;
	if ($includingMin) {
		$condB = $var <= $max;
	}
	
	return $condA && $condB;
}