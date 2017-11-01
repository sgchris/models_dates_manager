<?php

require_once __DIR__.'/init.php';

requestShouldBe('post');
setRestrictedAccess();

$params = receiveParams(['list_id'], ['list_id']);

$listId = $params['list_id'];
if (!is_numeric($listId) || !($listId > 0)) {
	_exit('bad list_id parameter');
}

// delete list's images
$listDetails = dbRow('select * from lists where id = :id', ['id' => $listId]);
if (!$listDetails) {
	_exit('list '.$listId.' is not found');
}

// delete from lists table
$result = dbExec('delete from lists where id = :list_id', array(
	':list_id' => $listId
));

_success();
