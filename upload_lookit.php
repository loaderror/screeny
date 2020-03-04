<?php
require_once('config.php');
global $httpurl, $db;

if (!validateAccount()) {
    header('HTTP/1.1 403 Access Denied');
    header('X-Reason: Because u suck!');
    logMsg('Access Denied: ' . $_SERVER['SERVER_ADDR'] . ' -- ' . print_r($_REQUEST));
    die();
} else {
    logMsg("Access Granted: " . getUser());
}

$uploadFilename = $_FILES['file']['tmp_name'];

/*
if (!imageValid($uploadFilename)) {
    header('HTTP/1.1 401 Bad Request');
    echo 'Invalid Filetype!';
    die();
}
*/

$newURL = saveImageToContainer($uploadFilename);
echo $httpurl . $newURL;
