<?php
die();
require_once('config.php');
$base = '_old/screens';

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
 	@mkdir($store . $year . "/" . $month);

 	$path = $store . $year . '/' . $month . '/' . $filename;

 	copy('./' . $base . '/' . $filename, $path);

 	$db->query("INSERT INTO `screenshots` (`url`, `filename`, `date`) VALUES 
 		('".$db->escape($filename)."', 
 		'".$db->escape($year . "/" . $month . "/" . $filename)."', 
 		FROM_UNIXTIME(".$db->escape($timestamp).")
 		)");
}
