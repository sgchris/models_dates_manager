<?php

define('DS', DIRECTORY_SEPARATOR);
define('IMAGES_UPLOAD_PATH', __DIR__.DS.'..'.DS.'images'.DS.'upload');
define('IMAGES_UPLOAD_URL', 'images/upload'); // the uploaded images will be located under that URL (e.g. images/upload/someimage_fdc123a.jpg)

// define the path to the log file
define('LOG_FILE_PATH', __DIR__.DS.'api.log');

// set the DB file name
define('DB_FILE_PATH', __DIR__.DS.'db'.DS.'models.db');

// facebook info
define('FB_APP_ID', '1464574370233315');
define('FB_APP_SECRET', 'a30e5de58b2a3842532cd02f5a0fe46e');
