<?php
require_once 'config.php';

function startDBcon()
{
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_NAME);
    if ($con) {
        return $con;
    } else {
        die("Impossible database connection: " . mysqli_error($con));
    }
}

function endDBcon($con)
{
    mysqli_close($con);
}

function query($con, $query)
{
    $result = mysqli_query($con, $query);
    if ($result) {
        return $result;
    } else {
        $error = mysqli_error($con);
        if (strpos($error, 'Duplicate entry') === false) {
            writeFile("log.txt", $error);
        }
    }
}

function startProcess($con, $process_id, $params = null)
{
    $params = $params == null ? "NULL" : "'$params'";
    query($con, "INSERT INTO execution(process_id,params,start,status) VALUES ($process_id,$params,CURTIME(),0);");
    return mysqli_insert_id($con);
}

function updateProcess($con, $execution_id)
{
    query($con, "UPDATE execution SET `update`=CURTIME(), status_num=status_num+1 WHERE id=$execution_id;");
}

function endProcess($con, $execution_id, $status, $results)
{
    $end = $status == 1 ? "CURTIME()" : "NULL";
    query($con, "UPDATE execution SET end=$end, status=$status, results=$results WHERE id=$execution_id;");
}

function writeFile($fileName, $text)
{
    $myFile = fopen($fileName, "a");
    fwrite($myFile, date("Y-m-d H:i:s") . ': ' . $text . "\n");
    fclose($myFile);
}