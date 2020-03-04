<?php

if(PHP_SAPI !== 'cli') {
    die();
}

require_once('../config.php');
$base = '_old/screens';

global $debug, $outputDebug;
$debug = true;
$outputDebug = true;

foreach (new DirectoryIterator($base) as $fileInfo)
{
    if($fileInfo->isDot()) continue;
    $filename = $fileInfo->getFilename();
    echo $filename . "\n";

 	$timestamp = $fileInfo->getMTime();
 	echo date('d.m.Y H:i:s', $timestamp). "\n";

 	$year = date('Y', $timestamp);
 	$month = date('m', $timestamp);

 	@mkdir($store . $year);
 	@mkdir($store . $year . '/' . $month);

 	$path = $store . $year . '/' . $month . '/' . $filename;

 	copy('./' . $base . '/' . $filename, $path);

 	$sqlUrl = $db->escape($filename);
 	$sqlFilename = $db->escape($year . '/' . $month . '/' . $filename);
    $sqlTimestamp = $db->escape($timestamp);
    $sqlMimetype = $db->escape(mime_content_type($path)); // Use newly copied file

 	$db->query("INSERT INTO `screenshots` (`url`, `filename`, `date`, `mimetype`) VALUES
        ('$sqlUrl', '$sqlFilename', FROM_UNIXTIME($sqlTimestamp), '$sqlMimetype')");
}
