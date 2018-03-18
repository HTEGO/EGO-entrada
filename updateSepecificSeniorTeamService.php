<?php
set_time_limit(PHP_INT_MAX);

require_once 'config.php';
require_once 'common.php';
require_once 'updateSpecificSeniorTeam.php';

try {
    $HT = new \PHT\PHT($config);

    $league_id = 15251;
    $exec_id = 1;
    $teamId = 1585054;

    $con = startDBcon();


    $seniorTeam = $HT->getClub($teamId)->getTeam();

    updateSpecificSeniorTeam($con,$seniorTeam,$league_id,true,true);

    query($con, "UPDATE league SET status=1 WHERE id=$league_id;");
    updateProcess($con, $exec_id);
    echo 'OK';
} catch (\Exception $e) {
    echo $e->getMessage();
    login($config, $e->getMessage());
}
?>