<?php
/*
Written by Tobias Mädel (t.maedel@alfeld.de) - http://tbspace.de
Overhault by Steven Tappert (mail@steven-tappert.de) - https://steven-tappert.de
*/

function isDebugEnabled() {
    global $debug;
    return $debug;
}

function fileExists($filename)
{
    global $db;
    $ret = $db->fetch("SELECT * FROM screenshots WHERE url LIKE '" . $db->escape($filename) . ".%' LIMIT 0,1", true);

    if ($ret === null) {
        return false;
    }

    return true;
}

// Erzeugt eine zufällige 10-Zeichen Zeichenfolge
function generateRandomURL()
{
    $dummy = array_merge(range('a', 'z'));
    mt_srand((double)microtime() * 1000000);
    for ($i = 1; $i <= (count($dummy) * 2); $i++) {
        $swap = random_int(0, count($dummy) - 1);
        $tmp = $dummy[$swap];
        $dummy[$swap] = $dummy[0];
        $dummy[0] = $tmp;
    }
    return substr(implode('', $dummy), 0, 11);
}

// Erzeugt einen Dateinamen welcher in der .tar Datei noch nicht vorhanden ist.
function generateFreeURL()
{
    while (true) {
        $url = generateRandomURL();
        if (!fileExists($url)) {
            return $url;
        }
    }
}

// Prüft ob ein Pfad ein valides Bild enthält
function imageValid($filename)
{
    $mime = mime_content_type($filename);
    //mail("tobias@tbspace.de", "asdf", print_r($mime, true));
    if ($mime === "video/mp4" || $mime === "video/quicktime") {
        return true;
    }

    list($width, $height, $type, $attr) = getimagesize($filename);
    return (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF), true));
}

function saveImageToContainer($filename)
{
    global $store, $db;

    $mime = mime_content_type($filename);
    $suffix = ".png";
    if ($mime === 'video/mp4') {
        $suffix = '.mp4';
    }
    if ($mime === 'video/quicktime') {
        $suffix = '.mov';
    }

    if ($mime === 'image/jpeg') {
        $suffix = '.jpg';
    }

    // Neuen Zufallsdateinamen generieren
    $newURL = generateFreeURL() . $suffix;

    $timestamp = time();
    $year = date("Y", $timestamp);
    $month = date("m", $timestamp);

    @mkdir($store . $year);
    @mkdir($store . $year . "/" . $month);

    $path = $store . $year . "/" . $month . "/" . $newURL;

    move_uploaded_file($filename, $path);

    $db->query("INSERT INTO `screenshots` (`url`, `filename`, `date`) VALUES 
 		('" . $db->escape($newURL) . "', 
 		'" . $db->escape($year . "/" . $month . "/" . $newURL) . "', 
 		FROM_UNIXTIME(" . $db->escape($timestamp) . ")
 		)");

    return $newURL;
}

// Überprüft die übermittelten Accountdaten
function validateAccount()
{
    global $user;
    foreach ($user as $username => $password) {
        if ((($_REQUEST['username'] === $username) && ($_REQUEST['password'] === $password))) {
            return true;
        }
    }
    return false;
}


function logMsg(...$msg)
{
    if(isDebugEnabled()) {
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.log', implode("\n", $msg) . "\n", FILE_APPEND);
    }
}

/**
 * @param $cmd
 * @return String
 */
function execWithLog($cmd)
{
    logMsg('cmd: ' . $cmd);
    $output = null;
    $returnVar = null;

    exec($cmd, $output, $returnVar);
    logMsg('exit-code: ' . $returnVar);

    if ($output !== null) {
        if(is_array($output)) {
            $output = implode('\n', $output);
        }

        logMsg('output: ' . $output);
    }

    return $output;
}

function writeImThumb($file, $thumbFile, $padding = null)
{
    $thumbHeight = 570;
    $thumbWidth = 750;
    $paddingCmd = $padding === null ? '' : '-extent ' . ($thumbWidth + $padding) . 'x' . ($thumbHeight + $padding);

    // exec("convert -resize 750x570 -background white -gravity center -extent 750x570 -format jpg -quality 98 " . escapeshellarg($targetFile) . " " . escapeshellarg($thumbsfile) . "");
    $cmd = "convert -auto-orient -thumbnail '750x570>' $paddingCmd -gravity center -format png -quality 80 " . escapeshellarg($file) . ' ' . escapeshellarg($thumbFile);
    execWithLog($cmd);
}

function writeFfmpegThumb($file, $thumbFile)
{
    // ffmpeg -i ifqmphekzls.mp4 -vframes 1 -an -ss 30 ifqmphekzls.mp4.thumb.png
    $cmd = 'ffmpeg -i ' . escapeshellarg($file) . ' -vframes 1 -an -ss 5 -vf scale=750:-1 ' . escapeshellarg($thumbFile);
    execWithLog($cmd);
}

function sendFile($file)
{
    $mime = mime_content_type($file);
    header('Content-Type: ' . $mime);
    header('Pragma: public');
    header('Cache-Control: max-age=86400, public');
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
    header('Content-Length: ' . filesize($file));

    readfile($file);
}
