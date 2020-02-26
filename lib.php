<?php
/*
Written by Tobias Mädel (t.maedel@alfeld.de) - http://tbspace.de
Overhault by Steven Tappert (mail@steven-tappert.de) - https://steven-tappert.de
*/

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
