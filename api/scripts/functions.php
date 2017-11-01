<?php

define('LOG_FILE_PATH', __DIR__.'/migration.'.date('YmdHis').'.log');


/**
 * 
 * @param <unknown> $errorStr 
 * @return  
 */
function _exit($errorStr = '') {
	echo json_encode(['result' => 'error', 'error' => $errorStr], JSON_PRETTY_PRINT), "\n";
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
	
	$apiFileName = basename($_SERVER['PHP_SELF']);
	
	$logStr = date('d.M.Y H:i:s') . ' ' . $apiFileName . ': ' . implode(' ', $logElements) . "\r\n";
	$res = @file_put_contents(LOG_FILE_PATH, $logStr, FILE_APPEND);
	
	// echo the log
	echo $logStr;

	return (is_numeric($res) && $res > 0);
}



/**
 * Execute statement with error check
 * 
 * @param \PDO $db 
 * @param mixed $sql 
 * @param array $paramsArray 
 * @param mixed $returnResultSet 
 * @param mixed $fetchOnlyOne 
 * @return  
 */
function executeQuery(PDO $db, $sql, array $paramsArray = array(), $returnResultSet = true, $fetchOnlyOne = false) {
	
	// prepare
	$stmt = $db->prepare($sql);
	if (!$stmt) {
		_log($db->errorInfo());
		return false;
	}
	
	// execute
	$result = $stmt->execute($paramsArray);
	if (!$result) {
		_log($stmt->errorInfo());
		return false;
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
function dbExec(PDO $db, $sql, array $paramsArray = array()) {
	return executeQuery($db, $sql, $paramsArray, $__returnResultSet = false);
}

/**
 * Execute query and get the results - list of rows
 * @param string $sql 
 * @param array $paramsArray 
 * @return array
 */
function dbQuery(PDO $db, $sql, array $paramsArray = array()) {
	return executeQuery($db, $sql, $paramsArray, $__returnResultSet = true);
}

/**
 * Execute query and get the result as one row array
 * @param string $sql 
 * @param array $paramsArray 
 * @return array
 */
function dbRow(PDO $db, $sql, array $paramsArray = array()) {
	return executeQuery($db, $sql, $paramsArray, $__returnResultSet = true, $__fetchOnlyOne = true);
}

/**
 * Download a file
 * @param mixed $url 
 * @return  
 */
function download($url) {
	$opts = array('http' =>
		array(
			'method'  => 'GET',
			'user_agent'  => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
			'header' => array(
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*\/*;q=0.8',
			), 
		)
	);
	$context  = stream_context_create($opts);
	
	return file_get_contents($url, false, $context);
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
