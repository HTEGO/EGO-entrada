<?php
set_time_limit(PHP_INT_MAX);

require_once 'config.php';
require_once 'common.php';
require_once 'updateSpecificSeniorTeam.php';

try {
    $HT = new \PHT\PHT($config);

    $league_id = $_GET["league_id"];
    $exec_id = $_GET["exec_id"];

    $con = startDBcon();

    //Collect BD seniorTeam_id for insert or update
    $seniorTeams = array();
    $results = query($con, "SELECT id FROM seniorteam");
    while ($row = $results->fetch_assoc()) {
        $seniorTeams[] = $row['id'];
    }

    //Collect BD youthTeam_id for insert or update
    $youthTeams = array();
    $results = query($con, "SELECT id FROM youthteam");
    while ($row = $results->fetch_assoc()) {
        $youthTeams[] = $row['id'];
    }

    $league = $HT->getSeniorLeague($league_id);
    foreach ($league->getTeams() as $team) {
        $seniorTeam = $team->getTeam();
        $isNew = in_array($seniorTeam->getId(),$league_id, $seniorTeams);
        $isNewYouthTeam = in_array($youthTeam_id, $youthTeams);
        updateSpecificSeniorTeam($seniorTeam,$league_id,$isNew,$isNewYouthTeam);
    }
    query($con, "UPDATE league SET status=1 WHERE id=$league_id;");
    updateProcess($con, $exec_id);
    echo 'OK';
} catch (\Exception $e) {
    echo $e->getMessage();
    login($config, $e->getMessage());
}