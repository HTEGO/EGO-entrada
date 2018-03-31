<?php

require_once 'updateSpecificSeniorTeamService.php';

$teamsJson = '{"teams": [
    [1585054,15251],
    [1372906,38655],
    [1853077,14500],
    [312957,38307],
    [1419133,14386],
    [439238,5570],
    [772184, 15188],
    [1589328,5763],
    [311631, 38828],
    [1459840,5674],
    [1333784,3447],
    [1430346,14598],
    [1583077,14500]
]}';

    $teams = json_decode($teamsJson,true);

array_map(function($team){
    echo "Team ID: $team[0], Series ID: $team[1] <br>";
    updateSpecificSeniorTeamService($team[0],$team[1]);
    echo "<br><br><br>";

},(array) $teams["teams"]);
