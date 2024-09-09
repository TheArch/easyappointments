<?php extend('layouts/backend_layout'); ?>

<?php section('content'); ?>

<?php

$termine = vars('user_id');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="impfkalender_export.csv" ');

$fiveMBs = 5 * 1024 * 1024; // Set the limit to 5 MB.
//$fp = fopen("php://temp/maxmemory:$fiveMBs", 'w');
$fp = fopen("php://output", 'w');
fputs($fp, "Beginn;Ende;Bereich;Auftrag;Patient;Bereich;\n");
foreach($termine as $item) { //foreach element in $arr
    foreach($item as $item2) {
        fputs($fp, $item2['start_datetime'] . ';' . $item2['end_datetime'] . ';');
        fputs($fp, utf8_decode($item2['provider']['first_name'] . ' ' . $item2['provider']['last_name'] . ';'));
        fputs($fp, utf8_decode($item2['service']['name'] . ';'));
        fputs($fp, utf8_decode($item2['customer']['last_name'] . ', ' . $item2['customer']['first_name'] . ';'));
        fputs($fp, utf8_decode($item2['customer']['custom_field_1']));
        fputs($fp, "\n");
    }
}
fclose($fp);
//rewind($fp);
//echo stream_get_contents($fp);
//$json_string = json_encode($termine, JSON_PRETTY_PRINT);
//print($termine); exit;
exit;
?>
