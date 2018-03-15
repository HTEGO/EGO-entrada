<?php
require_once 'config.php';
require_once 'common.php';

//DB table process id
$process_id = 3;

try {
    writeFile("log.txt", "CRON");
    $con = startDBcon();
    $results = query($con, "SELECT id,`update` FROM execution WHERE status=0 AND process_id=$process_id;");
    if (($results->num_rows == 0 && date("D_H") == 'Sun_19') || ($results->num_rows == 1 && strtotime($results->fetch_assoc()['update']) < strtotime('-2 minutes'))) {
        endDBcon($con);
        writeFile("log.txt", "LAUNCH UPDATE");
        curl(HOST . '/EGO/updateMatchLineups.php');
    }
    endDBcon($con);
} catch (\Exception $e) {
    writeFile("log.txt", $e->getMessage());
}

function curl($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_exec($ch);
    curl_close($ch);
}