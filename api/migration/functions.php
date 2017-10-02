<?php
define('LOG_FILE_PATH', __DIR__.'/migration.log');


/**
 * 
 * @param <unknown> $errorStr 
 * @return  
 */
function _exit($errorStr) {
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
	
	$parsedUrl = parse_url($_SERVER['REQUEST_URI']);
	$apiFileName = basename($parsedUrl['path']);
	
	$logStr = date('d.M.Y H:i:s') . ' ' . $apiFileName . ': ' . implode(' ', $logElements) . "\r\n";
	$res = @file_put_contents(LOG_FILE_PATH, $logStr, FILE_APPEND);
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