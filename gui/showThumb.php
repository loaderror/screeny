<?php
// @error_reporting(0);
error_reporting(E_ALL);
require_once('../lib.php');

$errors = [];
if (isset($_GET['img']) && preg_match('/^[a-zA-Z0-9_\.\[\]]+$/', $_GET['img'])) {
    require_once('../config.php');

    $ret = $db->fetch("SELECT * FROM screenshots WHERE url = '" . $db->escape($_GET['img']) . "' AND hidden = 0", true);
    if ($ret === null) {
        die();
    }

    $padding = null;
    $file = $store . $ret['filename'];
    $mime = $ret['mimetype'];
    $thumbFile = $file . '.thumbs.png';

    if (file_exists($thumbFile)) {
        logMsg('already there: ' . $thumbFile);
        sendFile($thumbFile);
    } else {
        if (file_exists($file)) {
            logMsg('Mimetype: ' . $mime);
            $outputFile = true;

            if (!is_writable(dirname($file))) {
                logMsg('Thumbnail-Folder is not writeable / existent');
                $outputFile = false;
            }

            if (!is_writable($file)) {
                logMsg('Thumbnail is not writable');
                $outputFile = false;
            }

            if (preg_match('/video\//', $mime)) {
                logMsg('Processing FFmpeg');
                writeFfmpegThumb($file, $thumbFile);
            } else {
                if ($mime === 'image/png' || $mime === 'image/jpeg') {
                    $file .= '[1]';
                }

                if ($mime === 'application/pdf') {
                    $file .= '[0]'; // Seite 1
                }

                if ($mime === 'application/zip') {
                    $file = __DIR__ . DIRECTORY_SEPARATOR .
                        'assets' . DIRECTORY_SEPARATOR .
                        'fonts' . DIRECTORY_SEPARATOR .
                        'file-archive-regular.png';
                    $padding = 100;
                }

                logMsg('Processing ImageMagick');
                writeImThumb($file, $thumbFile, $padding);
            }

            if (!file_exists($thumbFile)) {
                $outputFile = false;
            }

            if ($outputFile) {
                sendFile($thumbFile);
            } else {
                header('Content-Type: text/plain; charset=UTF-8');
                logMsg('Could not generate thumbnail');

                var_dump($errors);
                var_dump($ret);
            }
        }
    }
}
