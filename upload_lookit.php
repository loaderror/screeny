<?php
require_once('config.php');
global $httpurl, $db;

if (!validateAccount()) {
    die();
}

$uploadFilename = $_FILES['file']['tmp_name'];

if (!imageValid($uploadFilename)) {
    die();
}

$newURL = saveImageToContainer($uploadFilename);
echo $httpurl . $newURL;
