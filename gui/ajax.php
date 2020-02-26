<?php
require_once('config.php');
header('Content-Type: application/json');

if (isset($_REQUEST['fnc'])) {
    switch ($_REQUEST['fnc']) {
        // Ermittelt einen Eintrag anhand der ID
        case 'getEntry':
            if ($entry = $db->fetch("SELECT * FROM `screenshots` WHERE `id` = '" . $db->escape((int)trim(strip_tags($_REQUEST['entryID']))) . "' AND `hidden` = 0", true)) {
                die(json_encode($entry));
            }
            break;

        // Ermittelt ein Suchergebnis
        case 'getEntries':
            $where = '';
            if (isset($_REQUEST['term']) && $_REQUEST['term'] != "") {
                $where = "AND MATCH( `tags`, `fulltext` ) AGAINST ( '*" . $db->escape(trim(strip_tags($_REQUEST['term']))) . "*' IN BOOLEAN MODE )";
            }

            $offset = (int)trim(strip_tags(($_REQUEST['offset'] ?? 0)));
            $total = $db->fetch("SELECT COUNT( `id` ) AS `total` FROM `screenshots` WHERE `hidden` = 0 " . $where, "total");
            $entries = $db->fetch("SELECT * FROM `screenshots` WHERE `hidden` = 0 " . $where . " ORDER BY `date` DESC LIMIT " . $db->escape($offset) . ", 24");
            die(json_encode(array("total" => $total, "offset" => $offset, "entries" => $entries)));
            break;

        // Speichert die TAGs zu einem Eintrag
        case 'setTags':
            $value = '';
            $tags = preg_split("/(,|;|\n)/", $_REQUEST['tags']);
            if (count($tags) > 0) {
                foreach ($tags as $tag) {
                    $tag = trim(strip_tags($tag));
                    if ($tag == '') continue;
                    $value .= $tag . ', ';
                }
                $value = substr($value, 0, -2);
            }

            $db->query("UPDATE `screenshots` SET `tags` = '" . $db->escape($value) . "' WHERE `id` = '" . $db->escape((int)trim(strip_tags($_REQUEST['entryID']))) . "'");
            die(json_encode(($db->affectedRows() ? $value : 'failed')));
            break;

        // Löscht einen Eintrag
        case 'delEntry':
            if ($entry = $db->fetch("SELECT * FROM `screenshots` WHERE `id` = '" . $db->escape((int)trim(strip_tags($_REQUEST['entryID']))) . "'", true)) {
                $time = time() . '_';
                $newFilename = str_replace($entry['url'], $time . $entry['url'], $entry['filename']);
                $db->query("UPDATE `screenshots` SET `url`= CONCAT( '" . $time . "', `url` ), `filename` = '" . $db->escape($newFilename) . "', `hidden` = '1' WHERE `id` = '" . $db->escape((int)trim(strip_tags($_REQUEST['entryID']))) . "'");
                rename($store . $entry['filename'], $store . $newFilename);
                die(json_encode(($db->affectedRows() ? 'success' : 'failed')));
            }
            break;
    }
}

echo json_encode('');
