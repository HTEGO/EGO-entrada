<?php
set_time_limit(PHP_INT_MAX);

require_once 'config.php';
require_once 'common.php';
require_once 'updateSpecificSeniorTeam.php';

    function updateSpecificSeniorTeamService($teamId,$league_id) {

        global $config;
        try {
            $HT = new \PHT\PHT($config);
            $team =  new PHT\Wrapper\Team\Senior();

            $exec_id = 1;

            $seniorTeam = $team->team($teamId);

            $con = startDBcon();
           updateSpecificSeniorTeam($con,$seniorTeam,$league_id,true,true);

            query($con, "UPDATE league SET status=1 WHERE id=$league_id;");
            updateProcess($con, $exec_id);
            echo 'OK';
        } catch (\Exception $e) {
            echo $e->getMessage();
           //login($config, $e->getMessage());
        }
    }
?>
