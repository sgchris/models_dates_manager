<?php

require_once __DIR__.'/functions.php';

define('CATEGORY_NEW_FACE', 2);
define('ROOT_FOLDER', realpath(dirname(__DIR__).'/..'));

$db_ff = new PDO('sqlite:freshfaces_models.db');
if (!$db_ff) {
	die('Cannot connect to FF DB');
}
$db_ff->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


$db_vip = new PDO('sqlite:vipmodels.db');
if (!$db_vip) {
	die('Cannot connect to VIP DB');
}
$db_vip->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


// get all the old models
$ffModels = dbQuery($db_ff, 'select * from models');
_exit(['model' => $ffModels[14]]);

_log('read', count($ffModels), 'models from FF');

foreach ($ffModels as $ffModel) {
	// download pictures
	$newImagesList = downloadPictures($ffModel);
	

}




// ---------------------------------------------------------------

/**
 * 
 * @param array $ffModel - model record.
	{
		"id": "96",
		"name": "\ud83c\udf38PURA VIDA\ud83c\udf38",
		"url": "https:\/\/www.instagram.com\/alonatihonov\/",
		"picture": "https:\/\/scontent-amt2-1.cdninstagram.com\/t51.2885-19\/s320x320\/21980702_553736235018000_5413541040902635520_n.jpg",
		"phone": "+972 53-224-5055",
		"notes": "",
		"display_order": "0",
		"extra_data": "{
		 * 	\"additionalPictures\":[\"images\\\/upload\\\/Snapchatalona789601_57864133e1743.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e1d41.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e243b.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e2cc4.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e3807.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e3d53.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e42e9.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e4a36.jpg\",\"images\\\/upload\\\/Snapchatalona789601_57864133e58c9.jpg\"],
		 * \"color\":\"#DCEDC8\"
		 *}"
	}


 * @return array - list of new images
 */
function downloadPictures($ffModel) {
	_log('downloading pictures of model', $ffModel['name']);
	$newFileNames = [];
	
	// download the main image
	$mainImageUrl = trim($ffModel['picture']);
	if (!empty($mainImageUrl)) {
		_log('downloading', $mainImageUrl);
		
		// check if this is local image
		if (preg_match('/^images/i', $mainImageUrl)) {
			$mainImageUrl = 'http://srochno:sport@freshfaces.multashka.com/'.$mainImageUrl;
		}
		
		$imageContent = file_get_contents($mainImageUrl);
		if ($imageContent !== false) {
			$newFileName = md5(microtime(true)).'.jpg';
			$writeResult = file_put_contents(ROOT_FOLDER.'/images/upload/'.$newFileName);
			if ($writeResult !== false) {
				_log('writing to', $newFileName);
				$newFileNames[] = $newFileName;
			}
		}
	}
	
	if (!empty($ffModel['extra_data']) && ($extraData = json_decode($ffModel['extra_data'], true)) !== NULL) {
		if (!empty($extraData['additionalPictures'])) {
			foreach ($extraData['additionalPictures'] as $imageUrl) {
				// check if this is local image
				if (preg_match('/^images/i', $imageUrl)) {
					$imageUrl = 'http://srochno:sport@freshfaces.multashka.com/'.$imageUrl;
				}
				
				$imageContent = file_get_contents($imageUrl);
				if ($imageContent !== false) {
					$newFileName = md5(microtime(true)).'.jpg';
					
					$writeResult = file_put_contents(ROOT_FOLDER.'/images/upload/'.$newFileName);
					if ($writeResult !== false) {
						$newFileNames[] = $newFileName;
					}
				}
			}
		}
	}
	
	return $newFileNames;
}