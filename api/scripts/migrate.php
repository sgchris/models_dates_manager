<?php

require_once __DIR__.'/functions.php';

define('CATEGORY_NEW_FACE', 2);
define('ROOT_FOLDER', realpath(dirname(__DIR__).'/..'));

_log('migration start');

$db_ff = new PDO('sqlite:ff_models.db');
if (!$db_ff) {
	die('Cannot connect to FF DB');
}
$db_ff->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


$db_vip = new PDO('sqlite:vm_models.db');
if (!$db_vip) {
	die('Cannot connect to VIP DB');
}
$db_vip->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// get all the old models
$ffModels = dbQuery($db_ff, 'select * from models');

_log('read', count($ffModels), 'models from FF');

/*
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
 */
foreach ($ffModels as $ffModel) {
	_log('checking', $ffModel['name']);

	// find the model in the new database
	$vmModel = dbRow($db_vip, '
		SELECT id, name, phone 
		FROM models 
		WHERE name LIKE :name', 
		['name' => $ffModel['name']]
	);

	if (!$vmModel) {
		_log('model', $ffModel['name'], 'was not found in the new DB');
		continue;
	}

	// check if the new model has no phone
	if (empty($vmModel['phone']) && !empty($ffModel['phone'])) {
		_log('model', $ffModel['name'], 'needs to be updated');
	}
}

echo "Finished\n";



// ---------------------------------------------------------------

/**
 * 
 * @param array $ffModel - model record.
	{

	}


 * @return array - list of new images
 */
function downloadPictures($ffModel) {
	_log('downloading pictures of model', $ffModel['name']);
	$newFileNames = [];
	
	// download the main image
	$mainImageUrl = trim($ffModel['picture']);
	if (!empty($mainImageUrl)) {
		// check if this is local image
		if (preg_match('/^images/i', $mainImageUrl)) {
			$mainImageUrl = 'http://srochno:sport@freshfaces.multashka.com/'.$mainImageUrl;
		}
		
		_log('downloading', $mainImageUrl);
		
		$imageContent = download($mainImageUrl);
		if ($imageContent !== false) {
			_log('downloaded', number_format(strlen($imageContent)), 'bytes');
			
			$newFileName = md5(microtime(true)).'.jpg';
			$newFilePath = ROOT_FOLDER.'/images/upload/'.$newFileName;
			$writeResult = file_put_contents($newFilePath, $imageContent);
			if ($writeResult !== false) {
				_log('wrote to', $newFileName);
				$newFileNames[] = $newFileName;
			} else {
				_log('writing to', $newFilePath, 'failed');
			}
		} else {
			_log('error downloading');
		}
	}
	
	$extraData = $ffModel['extra_data'];
	if (is_string($extraData) && !empty($extraData)) {
		$extraData = json_decode($extraData, true);
	}
	
	if (!empty($extraData)) {
		if (!empty($extraData['additionalPictures'])) {
			foreach ($extraData['additionalPictures'] as $imageUrl) {
				// check if this is local image
				if (preg_match('/^images/i', $imageUrl)) {
					$imageUrl = 'http://srochno:sport@freshfaces.multashka.com/'.$imageUrl;
				}
				
				_log('downloading', $imageUrl);
				
				$imageContent = download($imageUrl);
				if ($imageContent !== false) {
					_log('downloaded', number_format(strlen($imageContent)), 'bytes');
					
					$newFileName = md5(microtime(true)).'.jpg';
					$newFilePath = ROOT_FOLDER.'/images/upload/'.$newFileName;
					$writeResult = file_put_contents($newFilePath, $imageContent);
					if ($writeResult !== false) {
						$newFileNames[] = $newFileName;
						_log('wrote to', $newFilePath);
					} else {
						_log('writing to', $newFilePath, 'failed');
					}
				} else {
					_log('error downloading', $imageUrl);
				}
			}
		}
	}
	
	return $newFileNames;
}
