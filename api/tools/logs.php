<?php

define('LOGS_FILE', __DIR__.DS.'..'.DS.'db'.DS.'logs.txt');

function logInfo(...$logs) {
    _logLine(implode(' | ', $logs));
}

function logError(...$logs) {
    _logLine(implode(' | ', $logs), 'ERROR');
}

/**
 * write log line into the logs file
 * 
 * @return bool
 */
function _logLine($str, $level = 'INFO') {
    $logStr = '['.date('d.M.Y H:i:s')."] [{$level}] {$str}" . PHP_EOL;
    error_log("log: ".$logStr);
    if (false === ($fp = fopen(LOGS_FILE, 'a+'))) {
        error_log("Could not open file ".LOGS_FILE);
        return false;
    }

    if (false === fwrite($fp, $logStr)) {
        error_log("Could not write to file ".LOGS_FILE);
        return false;
    }

    fclose($fp);
}