<?php
require_once 'config.php';
require_once 'common.php';

//DB table process id
$process_id = 3;

try {
    //Verify Start or Continue Process or Continue Process via Post
    if (isset($_POST['exec_id'])) {
        //Continue Process via Post
        $exec_id = $_POST['exec_id'];
        $numYouthTeams = $_POST['numYouthTeams'];
        $numYouthMatchLineups = $_POST['numYouthMatchLineups'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $youthTeam_ids = $_POST['youthTeam_ids'];
    } else {
        $con = startDBcon();
        //Verify Start or Continue Process
        $results = query($con, "SELECT id,params FROM execution WHERE status=0 AND process_id=$process_id;");
        if ($results->num_rows == 0) {
            //Start Process
            //Verify previous process has ended and get number results
            $previous_process_id = $process_id - 1;
            $results = query($con, "SELECT id,status,results FROM execution WHERE id=(SELECT MAX(id) FROM execution WHERE process_id=$previous_process_id);");
            if ($results->num_rows == 0) {
                endDBcon($con);
                exit();
            }
            $row = $results->fetch_assoc();
            if ((int)$row['status']) {
                $numYouthTeams = $row['results'];
            } else {
                endDBcon($con);
                exit();
            }
            $numYouthMatchLineups = query($con, "SELECT count(*) AS 'total' FROM youthmatchlineup;")->fetch_assoc()['total'];
            $params = array('numYouthTeams' => $numYouthTeams, 'numYouthMatchLineups' => $numYouthMatchLineups, 'startDate' => date("Y-m-d", strtotime("-8 day")), 'endDate' => date("Y-m-d", strtotime("-1 day")));
            $exec_id = startProcess($con, $process_id, http_build_query($params));
            query($con, "UPDATE youthteam SET status=0;");
        } else {
            //Continue Process
            $row = $results->fetch_assoc();
            $exec_id = $row['id'];
            parse_str($row['params'], $params);
        }
        $numYouthTeams = $params['numYouthTeams'];
        $numYouthMatchLineups = $params['numYouthMatchLineups'];
        $startDate = $params['startDate'];
        $endDate = $params['endDate'];
        $results = query($con, "SELECT id FROM youthteam WHERE active=1 AND status=0");
        $youthTeam_ids = '';
        while ($row = $results->fetch_assoc()) {
            $youthTeam_ids .= $row['id'] . '|';
        }
        $youthTeam_ids = rtrim($youthTeam_ids, '|');
        endDBcon($con);
    }
    //The last array's index will have the pending teams to process.
    $youthTeams = explode('|', $youthTeam_ids, PARALLEL_THREADS + 1);
    $youthTeams_count = count($youthTeams);
    $continue = false;

    //If there is more teams than threads, it will open a new curl conection.
    if ($youthTeams_count == PARALLEL_THREADS + 1) {
        $continue = true;
        $youthTeams_count--;
        $youthTeam_ids = $youthTeams[PARALLEL_THREADS]; //This are the pending teams as string
    }
    $curl_arr = array();
    $master = curl_multi_init();
    for ($i = 0; $i < $youthTeams_count; $i++) {
        $url = HOST . "/updateMatchLineup.php?exec_id=" . $exec_id . "&youthTeam_id=" . $youthTeams[$i] . "&startDate=" . $startDate . "&endDate=" . $endDate;
        $curl_arr[$i] = curl_init($url);
        curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($master, $curl_arr[$i]);
    }
    do {
        curl_multi_exec($master, $running);
    } while ($running > 0);

    //If true, it will open a new connection.
    if ($continue) {
        sleep(1);
        $url = HOST . "/updateMatchLineups.php";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "exec_id=" . $exec_id . "&numYouthTeams=" . $numYouthTeams . "&numYouthMatchLineups=" . $numYouthMatchLineups . "&startDate=" . $startDate . "&endDate=" . $endDate . "&youthTeam_ids=" . $youthTeam_ids);
        curl_exec($ch);
        curl_close($ch);
    } else {
        //The process it's finished.
        $con = startDBcon();
        $numEndYouthTeams = (int)query($con, "SELECT count(*) AS 'total' FROM youthteam WHERE active=1 AND status=1")->fetch_assoc()['total'];
        $status = $numEndYouthTeams == $numYouthTeams ? 1 : 0;
        $results = (int)query($con, "SELECT count(*) AS 'total' FROM youthmatchlineup")->fetch_assoc()['total'] - $numYouthMatchLineups;
        endProcess($con, $exec_id, $status, $results);
        endDBcon($con);
    }
} catch (\Exception $e) {
    writeFile("log.txt", $e->getTraceAsString());
    login($config, $e->getMessage());
}