<?php
/*
Written by Tobias Mädel (t.maedel@alfeld.de) - http://tbspace.de
Overhaul by Steven Tappert (mail@steven-tappert.de) - https://steven-tappert.de
*/

function isDebugEnabled(): bool
{
    global $debug;
    return $debug;
}

function isDebugEchoEnabled(): bool
{
    global $outputDebug;
    return $outputDebug;
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
    if ($mime === 'video/mp4' || $mime === 'video/quicktime') {
        return true;
    }

    list($width, $height, $type, $attr) = getimagesize($filename);
    return (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF), true));
}

function saveImageToContainer($filename)
{
    global $store, $db;

    $mime = mime_content_type($filename);
    $suffix = '';

    logMsg('Saving: ' . $filename . ' with type: ' . $mime);

    $info = pathinfo($filename);
    if (array_key_exists('extension', $info)) {
        $suffix = $info['extension'];
    }

    if ($mime === 'video/mp4') {
        $suffix = '.mp4';
    }

    if ($mime === 'video/quicktime') {
        $suffix = '.mov';
    }

    if ($mime === 'image/jpeg') {
        $suffix = '.jpg';
    }

    if ($mime === 'image/png') {
        $suffix = '.png';
    }

    if ($mime === 'application/pdf') {
        $suffix = '.pdf';
    }

    if ($mime === 'application/zip') {
        $suffix = '.zip';
    }

    if ($mime === 'text/plain') {
        $suffix = '.txt';
    }

    // Neuen Zufallsdateinamen generieren
    $newURL = generateFreeURL() . $suffix;

    $timestamp = time();
    $year = date('Y', $timestamp);
    $month = date('m', $timestamp);

    @mkdir($store . $year);
    @mkdir($store . $year . '/' . $month);

    $path = $store . $year . '/' . $month . '/' . $newURL;

    move_uploaded_file($filename, $path);

    $sqlUrl = $db->escape($newURL);
    $sqlFilename = $db->escape($year . '/' . $month . '/' . $newURL);
    $sqlTimestamp = $db->escape($timestamp);
    $sqlMimetype = $db->escape(mime_content_type($path)); // Use newly copied file

    $db->query("INSERT INTO `screenshots` (`url`, `filename`, `date`, `mimetype`) VALUES
        ('$sqlUrl', '$sqlFilename', FROM_UNIXTIME($sqlTimestamp), '$sqlMimetype')");

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

function getUser()
{
    if (in_array('username', $_REQUEST, true)) {
        return $_REQUEST['username'];
    }

    return null;
}

function logMsg(...$msg)
{
    if (isDebugEnabled()) {
        if (isDebugEchoEnabled()) {
            echo implode(' ', $msg) . PHP_EOL;
        }

        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.log', implode("\n", $msg) . PHP_EOL, FILE_APPEND);
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
        if (is_array($output)) {
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

    $cmd = "convert -auto-orient -thumbnail '750x570>' $paddingCmd -gravity center -format png -quality 80 " . escapeshellarg($file) . ' ' . escapeshellarg($thumbFile);
    execWithLog($cmd);
}

function writeFfmpegThumb($file, $thumbFile)
{
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
