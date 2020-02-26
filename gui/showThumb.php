<?php
// @error_reporting(0);
error_reporting(E_ALL);

function logMsg(...$msg)
{
    global $errors;

    array_push($errors, ...$msg);
}

/**
 * @param $cmd
 * @return null
 */
function execWithLog($cmd)
{
    logMsg('cmd: ' . $cmd);
    $output = null;
    $returnVar = null;

    exec($cmd, $output, $returnVar);
    logMsg('exit-code: ' . $returnVar);

    if ($output !== null) {
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

$errors = [];
if (isset($_GET['img']) && preg_match('/^[a-zA-Z0-9_\.\[\]]+$/', $_GET['img'])) {
    require_once('../config.php');

    $ret = $db->fetch("SELECT * FROM screenshots WHERE url = '" . $db->escape($_GET['img']) . "' AND hidden = 0", true);
    if ($ret === null) {
        die();
    }

    $padding = 0;
    $file = $store . $ret['filename'];
    $thumbFile = $file . '.thumbs.png';

    if (file_exists($thumbFile)) {
        sendFile($thumbFile);
    } else {
        if (file_exists($file)) {
            $mime = mime_content_type($file);
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
