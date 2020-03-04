<?php
if(PHP_SAPI !== 'cli') {
    die();
}

require_once('../config.php');

global $debug, $outputDebug;
$debug = true;
$outputDebug = true;

$count = 100;
$totalCount = 0;

logMsg("Screenshot Dir: $store");

while ($count !== 0) {
    $entries = $db->fetch('SELECT COUNT(id) FROM screenshots');
    $shots = $db->fetch('SELECT id, filename FROM screenshots WHERE mimetype IS NULL LIMIT 0, 100');
    $count = count($shots);

    logMsg('Found ' . $count . ' unprocessed entries');

    foreach ($shots as $shot) {
        $id = $shot['id'];
        $filename = $shot['filename'];
        $realFilename = $store . DIRECTORY_SEPARATOR . $filename;

        if(!is_file($realFilename)) {
            logMsg('File ' . $filename . ' not found ... skipping');
            continue;
        }

        $mimetype = $db->escape(mime_content_type($realFilename));
        logMsg("Found $filename with type: $mimetype");

        $db->query('UPDATE screenshots SET mimetype = \'' . $mimetype . '\' WHERE `id` = ' . $db->escape($id));
        $totalCount++;
    }
}

logMsg('Update mimetype finished! (Count: ' . $totalCount . ')');
