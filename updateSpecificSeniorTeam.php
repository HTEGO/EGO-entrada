<?php

    function updateSpecificSeniorTeam($con,$seniorTeam,$league_id,$isNew,$isNewYounthTeam){
        $seniorTeam_id = $seniorTeam->getId();
        if (!$seniorTeam->isBot()) {
            $seniorTeam_name = addslashes($seniorTeam->getName());
            $user_id = $seniorTeam->getUserId();
            if (!$isNew) {
                query($con, "UPDATE seniorteam SET name='$seniorTeam_name', user_id=$user_id, league_id=$league_id, active=1 WHERE id=$seniorTeam_id;");
            } else {
                query($con, "INSERT INTO seniorteam(id, name, user_id, league_id, active) VALUES($seniorTeam_id, '$seniorTeam_name', $user_id, $league_id, 1);");
            }
            $youthTeam = $seniorTeam->getYouthTeam();
            if (!$youthTeam == null) {
                $youthTeam_id = $youthTeam->getId();
                $youthTeam_name = addslashes($youthTeam->getName());
                if (!$isNewYounthTeam) {
                    query($con, "UPDATE youthteam SET name='$youthTeam_name', seniorTeam_id=$seniorTeam_id, active=1 WHERE id=$youthTeam_id;");
                } else {
                    query($con, "INSERT INTO youthteam(id, name, seniorTeam_id, active) VALUES($youthTeam_id, '$youthTeam_name', $seniorTeam_id, 1);");
                }
            }
        }
    }
?>