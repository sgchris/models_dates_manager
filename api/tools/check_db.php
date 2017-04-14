<?php

if (!file_exists(DB_FILE_PATH) && !is_writable(dirname(DB_FILE_PATH))) {
	_exit('Cannot create DB file');
} elseif (file_exists(DB_FILE_PATH) && !is_writable(DB_FILE_PATH)) {
	_exit('Cannot modify DB file');
}

// open a connection
$db = new PDO('sqlite:'.DB_FILE_PATH);
if (!$db) {
	_exit('cannot connect to the DB');
}

$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// check if models table exists
$res = $db->query('select * from models limit 1');
if ($res === false) {
	// create the models table
	$res = $db->exec('
		create table models (
			id integer primary key autoincrement,
			name text,
			notes text,
			images text,
			display_order number
		)
	');
	if ($res === false) {
		_exit('Cannot create models table');
	}
	
	// create the models table
	$res = $db->exec('
		create table dates_list (
			date_ts number primary key,
			excluded_models text,
			hash text
		)
	');
	if ($res === false) {
		_exit('Cannot create dates_list table');
	}
}
