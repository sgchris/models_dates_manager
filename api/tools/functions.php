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
 * Check if the script is running from web or as a CLI
 * 
 * @return bool
 */
function isCommandLineInterface() {
    return (php_sapi_name() === 'cli');
}

/**
 * define that the current request should be allowed only to "$allowedUsers"
 * (defined in the config file)
 * @return  
 */
function setRestrictedAccess() {
	global $allowedUsers;
	
	// wait, while the user is stored in the session
	$counter = 30;
	while (!isset($_SESSION) || !isset($_SESSION['user']) || !isset($_SESSION['user']['name'])) {
		// in milliseconds
		usleep(100 * 1000);
		
		if (--$counter < 0) {
			break;
		}
	}
	
	if (!isset($_SESSION) || !isset($_SESSION['user']) || !isset($_SESSION['user']['name'])) {
		_exit('Not logged in');
	}
	
	if (!in_array($_SESSION['user']['name'], $allowedUsers)) {
		_exit('no permissions to do that operation');
	}
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
			if (isset($_REQUEST[$paramName])) {
				$params[$paramName] = $_REQUEST[$paramName];
			}
		}
	}
	
	// check mandatory params
	foreach ($mandatoryParams as $mandatoryParam) {
		if (!isset($params[$mandatoryParam])) {
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


/**
 * Execute statement with error check
 * @param string $sql 
 * @param array $paramsArray 
 * @return array 
 */
function executeQuery($sql, array $paramsArray = array(), $returnResultSet = true, $fetchOnlyOne = false) {
	global $db;
	
	// prepare
	$stmt = $db->prepare($sql);
	if (!$stmt) {
		_exit($db->errorInfo());
	}
	
	// execute
	$result = $stmt->execute($paramsArray);
	if (!$result) {
		_exit($stmt->errorInfo());
	}
	
	return $returnResultSet ? 
		($fetchOnlyOne ? $stmt->fetch() : $stmt->fetchAll()) : 
		true;
}


/**
 * Execute query without getting the result
 * @param string $sql 
 * @param array $paramsArray 
 * @return boolean
 */
function dbExec($sql, array $paramsArray = array()) {
	return executeQuery($sql, $paramsArray, $__returnResultSet = false);
}

/**
 * Execute query and get the results - list of rows
 * @param string $sql 
 * @param array $paramsArray 
 * @return array
 */
function dbQuery($sql, array $paramsArray = array()) {
	return executeQuery($sql, $paramsArray, $__returnResultSet = true);
}

/**
 * Execute query and get the result as one row array
 * @param string $sql 
 * @param array $paramsArray 
 * @return array
 */
function dbRow($sql, array $paramsArray = array()) {
	return executeQuery($sql, $paramsArray, $__returnResultSet = true, $__fetchOnlyOne = true);
}

/**
 * Get model details
 * @param number $modelId 
 * @return array
 */
function getModelDetails($modelId) {
	global $db;
	
	// get the model from the DB
	$modelRow = dbRow('select * from models where id = :model_id', array(
		':model_id' => $modelId
	));
	
	// check that she exists
	if (!$modelRow) {
		_exit('cannot find the model');
	}
	
	return $modelRow;
}



if (!function_exists('resizeImage')) {
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
			return false;
		} else {
			touch($destinationFile);
		}
		
		return imagejpeg($image_p, $destinationFile);
	}
}

