<?php

require_once "config.php";
include "head.php";         

$wid = $_POST["wid"];
$start_time = $_POST["start_time"];
$timestamp = strtotime($start_time);
$start_time = date("Y-m-d H:i:s", $timestamp);

$end_time = $_POST["end_time"];
$timestamp = strtotime($end_time);
$end_time = date("Y-m-d H:i:s", $timestamp);

$working = 0;
$qui = 0;

if(date('d') <= 15) {
    $qui = 1;
} else if(date('d') > 15) {
    $qui = 2;
}

$userid = $_POST["userid"];

$sql = "UPDATE intra_worklog 
        SET started_at = ?, ended_at = ?, working = ?, quinzena = ?
        WHERE id = ? AND user_id = ?";


if($stmt = $mysqli->prepare($sql)){
       // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssii", $start_time, $end_time, $working, $qui, $wid, $userid);
        // Attempt to execute the prepared statement
        $stmt->execute();
        $data = $data . $mysqli->error; 
        // Close statement
        $stmt->close();
}


// Close connection

$mysqli->close();


?>
