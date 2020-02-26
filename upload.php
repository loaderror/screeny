<?php
require_once('config.php');
global $httpurl, $db;

file_put_contents("requestLog.log", print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents("requestLog.log", print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents("requestLog.log", print_r($_FILES, true) . "\n", FILE_APPEND);
file_put_contents("requestLog.log", "---------------------------------------\n", FILE_APPEND);

if (!validateAccount())
    die();

$uploadFilename = $_FILES['image']['tmp_name'];

if (!imageValid($uploadFilename))
    die();

$newURL = saveImageToContainer($uploadFilename);
echo '#success ' . $httpurl . $newURL;
