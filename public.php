<?php
/**
 * The file is a public gateway to the inner pages, like date, model or list.
 * The script is created to provide headers to social networks, like title, description and image.
 * The script expects one parameter - hash. By this hash, the relevant page is identified and 
 * the corresponding data is displayed.
 */

require_once __DIR__.'/api/config.php';
require_once __DIR__.'/api/tools/check_db.php';
require_once __DIR__.'/api/tools/functions.php';

// check the parameter
$content = '';
if (!isset($_GET['hash']) || empty($_GET['hash']) || !preg_match('/^[A-Za-z0-9]{32}$/i', $_GET['hash'])) {
	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	header($protocol.' 400 Bad Request');
	die('Bad Request');
}

// initialize variables
$hash = $_GET['hash'];
$title = $description = $image = $originalUrl = false;

// get current HTTP schema (for URLs)
$schema = isset($_SERVER['HTTPS']) ? 'https' : 'http';


// check which page it belongs
$date = dbRow('select * from dates_list where hash = :hash', ['hash' => $hash]);
if (!empty($date)) {
	// this is "date" page
	$title = date('d.m.Y', $date['date_ts']);
	$models = json_decode($date['available_models'], true);
	$description = count($models).' available model(s)';
	$originalUrl = '/#/date/'.$hash;

	// get the image of the first model
	if ($models) {
		$firstModel = dbRow('select * from models where id = :id', ['id' => $models[0]]);
		if ($firstModel) {
			$images = json_decode($firstModel['images'], true);
			if ($images) {
				$mediumImageName = str_ireplace('.jpg', '_medium.jpg', $images[0]);
				$image = 'images/upload/medium/'.$mediumImageName;
				$image = "{$schema}://{$_SERVER['HTTP_HOST']}/{$image}";
			}
		}
	}
}

$model = dbRow('select * from models where hash = :hash', ['hash' => $hash]);
if (!empty($model)) {
	// this is "model" page
	$title = $model['name'];
	$images = json_decode($model['images'], true);
	$description = $model['name'].' model page - '.count($images).' photos';
	$originalUrl = '/#/model/'.$hash;

	if ($images) {
		$mediumImageName = str_ireplace('.jpg', '_medium.jpg', $images[0]);
		$image = 'images/upload/medium/'.$mediumImageName;
		$image = "{$schema}://{$_SERVER['HTTP_HOST']}/{$image}";
	}
}

$list = dbRow('select * from lists where hash = :hash', ['hash' => $hash]);
if (!empty($list)) {
	// this is "list" page
	$title = $list['name'];
	$models = json_decode($list['models'], true);
	$description = count($models).' available model(s)';
	$originalUrl = '/#/list/'.$hash;

	// get the image of the first model
	if ($models) {
		$firstModel = dbRow('select * from models where id = :id', ['id' => $models[0]]);
		if ($firstModel) {
			$images = json_decode($firstModel['images'], true);
			if ($images) {
				$mediumImageName = str_ireplace('.jpg', '_medium.jpg', $images[0]);
				$image = 'images/upload/medium/'.$mediumImageName;
				$image = "{$schema}://{$_SERVER['HTTP_HOST']}/{$image}";
			}
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>The HTML5 Herald</title>
	<meta name="description" content="The HTML5 Herald">
	<meta name="author" content="SitePoint">

	<!-- social (open graph) headers -->
	<?php if (!is_null($title)) { ?>
	<meta property="og:title" content="<?php echo htmlentities($title, ENT_QUOTES, 'utf-8'); ?>" />
	<meta property="og:description" content="<?php echo htmlentities($description, ENT_QUOTES, 'utf-8'); ?>" />
	<meta property="og:image" content="<?php echo htmlentities($image, ENT_QUOTES, 'utf-8'); ?>" />
	<?php } ?>
</head>
<body>
	<!-- body -->
	<?php if (!is_null($title)) { ?>
	<h1><?php echo htmlentities($title, ENT_QUOTES, 'utf-8'); ?></h1>
	<p><?php echo htmlentities($description, ENT_QUOTES, 'utf-8'); ?></p>
	<img src="<?php echo htmlentities($image, ENT_QUOTES, 'utf-8'); ?>" />
	<script>
	document.location.href="<?php echo htmlentities($originalUrl, ENT_QUOTES, 'utf-8'); ?>";
	</script>
	<?php } ?>
</body>
</html>

