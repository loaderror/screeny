<?php
require_once("config.php");

$count = 100;

while ($count !== 0) {
    $screenshots = $db->fetch("SELECT * FROM screenshots WHERE ocr = 0 AND filename LIKE '%.png' LIMIT 0, 100");
    $count = count($screenshots);
    foreach ($screenshots as $screenshot) {
        $filename = 'screenshots/' . $screenshot['filename'];

        if (!preg_match('/.*\.(?:png|jpg|jpeg|bmp|heic|gif|tif|tiff|webp|apng|hevc)/i', $screenshot['filename'])) {
            echo '-> Skipping ' . $screenshot['filename'] . "\n";
            continue;
        }

        echo '=> Processing: ' . $screenshot['filename'] . "\n";
        $command = "convert $filename -define filter:filter=Sinc -define filter:window=Jinc -define filter:lobes=3 -resize 400% - | tesseract -l eng+deu stdin stdout";
        $data = shell_exec("\$command");
        $db->query("UPDATE `screenshots` SET `fulltext`='" . $db->escape($data) . "', `ocr`='1' WHERE (`id`='" . $db->escape($screenshot['id']) . "')");
    }
}
