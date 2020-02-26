<?php
// @error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['img']) && preg_match('/^[a-zA-Z0-9_\.\[\]]+$/', $_GET['img'])) {
    require_once('config.php');

//	var_dump($_GET);

    $ret = $db->fetch("SELECT * FROM screenshots WHERE url = '" . $db->escape($_GET['img']) . "' AND hidden = 0", true);
    if ($ret === null) {
        header('HTTP/1.1 404 Not Found');
        header('X-Reason: Not Found in DB');
        die();
    }

    // file_put_contents("/var/customers/webs/steven/scr.2linux.de/log.jihad", print_r($ret, true) . "\n", FILE_APPEND);

    if (file_exists($store . $ret['filename'])) {
        $mime = mime_content_type($store . $ret['filename']);

        // Hack, damit Browser iPhone-Videos direkt abspielen.
        if ($mime === 'video/quicktime') {
            $mime = 'video/mp4';
        }

        header('Content-Type: ' . $mime);

        $file = $store . $ret['filename'];

        // Get the 'Range' header if one was sent
        if (isset($_SERVER['HTTP_RANGE']))
            $range = $_SERVER['HTTP_RANGE']; // IIS/Some Apache versions
        else
            $range = FALSE; // We can't get the header/there isn't one set

        // Get the data range requested (if any)
        $filesize = filesize($file);
        if ($range) {
            $partial = true;
            list($param, $range) = explode('=', $range);
            if (strtolower(trim($param)) !== 'bytes') { // Bad request - range unit is not 'bytes'
                header('HTTP/1.1 400 Invalid Request');
                exit;
            }
            $range = explode(',', $range);
            $range = explode('-', $range[0]); // We only deal with the first requested range
            if (count($range) !== 2) { // Bad request - 'bytes' parameter is not valid
                header('HTTP/1.1 400 Invalid Request');
                exit;
            }
            if ($range[0] === '') { // First number missing, return last $range[1] bytes
                $end = $filesize - 1;
                $start = $end - (int)$range[0];
            } else if ($range[1] === '') { // Second number missing, return from byte $range[0] to end
                $start = (int)$range[0];
                $end = $filesize - 1;
            } else { // Both numbers present, return specific range
                $start = (int)$range[0];
                $end = (int)$range[1];
                if ($end >= $filesize || (!$start && (!$end || $end === ($filesize - 1)))) {
                    $partial = false;
                } // Invalid range/whole file specified, return whole file
            }
            $length = $end - $start + 1;
        } else {
            $partial = false;
        } // No range requested

        header('Pragma: public');
        header('Cache-Control: max-age=86400, public');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        header('Content-Length: ' . filesize($store . $ret['filename']));

        if ($partial) {
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$filesize");
            if (!$fp = fopen($file, 'rb')) { // Error out if we can't read the file
                header('HTTP/1.1 500 Internal Server Error');
                exit;
            }

            if ($start) {
                fseek($fp, $start);
            }

            while ($length) { // Read in blocks of 8KB so we don't chew up memory on the server
                $read = ($length > 1048576) ? 1048576 : $length;
                $length -= $read;
                print(fread($fp, $read));
            }
            fclose($fp);
        } else {
            readfile($file);
        } // ...otherwise just send the whole file
    } else {
        header('HTTP/1.1 404 Not Found');
        header('X-Reason: File Not Found');
    }
} else {
    header('HTTP/1.1 404 Not Found');
    header('X-Reason: Last Die');
    die('Finally');
}
