<?php

define('USERS_FILE', __DIR__.DS.'..'.DS.'db'.DS.'users.json');
define('ACCESS_TOKEN_KEY', 'access_token');

function checkAuth() {
    // check if already logged in
    if (isset($_SESSION['user']['username'])) {
        logInfo("user ".$_SESSION['user']['username'].' is already logged in');
        return true;
    }

    // check cookie
    if (array_key_exists(ACCESS_TOKEN_KEY, $_COOKIE) && strlen($_COOKIE[ACCESS_TOKEN_KEY]) < 255) {
        logInfo('found access token in the cookies, logging in');
        $userData = _getUserByAccessToken($_COOKIE[ACCESS_TOKEN_KEY]);
        if ($userData !== false) {
            logInfo('found user by access token', 'signing in');
            $res = signIn($userData['username'], $userData['password'], $passIsMD5Hashed = true);
            return $res;
        }
    }

    logInfo('user is not logged in', 'from: '.$_SERVER['REMOTE_ADDR'], 'path:'.$_SERVER['REQUEST_URI']);
    return false;
}

/**
 * Sign in and set the session
 * 
 * @return bool
 */
function signIn($username, $password, $passIsMD5Hashed = false) {
    $users = _getStoredUsers();
    if ($users === false) {
        logError('cannot find stored users');
        return false;
    }

    $password = $passIsMD5Hashed ? $password : md5($password);
    $accessToken = md5(microtime());
    $foundUser = false;
    foreach ($users as $i => $user) {
        logInfo('checking against user', $username);
        if (strcmp($user['username'], $username) === 0 && 
            strcmp($user['password'], $password) === 0)
        {
            // found the user, set the session
            $users[$i][ACCESS_TOKEN_KEY] = $accessToken;
            $_SESSION['user'] = $users[$i];
            $foundUser = true;
        }
    }

    if (!$foundUser) {
        logInfo('could not find the user');
        return false;
    }

    // update the db
    logInfo('found the user');
    _setStoredUsers($users);

    return $accessToken;
}

/**
 * Get user object by access token. False otherwise
 * 
 * @return object|false
 */
function _getUserByAccessToken($accessToken) {
    $storedUsers = _getStoredUsers();
    if ($storedUsers !== false && is_array($storedUsers)) {
        foreach ($storedUsers as $userData) {
            if (is_array($userData) && 
                array_key_exists(ACCESS_TOKEN_KEY, $userData) && 
                strcmp($userData[ACCESS_TOKEN_KEY], $accessToken) === 0) 
            {
                return $userData;
            }
        }
    }

    return false;
}

/**
 * Get stored users in the db/file
 * 
 * @return json|false
 */
function _getStoredUsers() {
    $contents = @file_get_contents(USERS_FILE);
    if ($contents === null) {
        return false;
    }

    $contents = @json_decode($contents, $associative = true);
    if ($contents === null) {
        return false;
    }

    return $contents;
}

/**
 * Get stored users in the db/file
 * 
 * @return bool
 */
function _setStoredUsers($usersData) {
    logInfo('storing users');
    if ($usersData !== null && is_array($usersData)) {
        $storedUsersStr = @json_encode($usersData, JSON_PRETTY_PRINT);
        if ($storedUsersStr === false) {
            return false;
        }
        
        $filePutResult = @file_put_contents(USERS_FILE, $storedUsersStr);
        logInfo('filePutResult = ', var_export($filePutResult, true));
        return ($filePutResult !== false);
    }

    return false;
}