<?php

require_once "config.php";

$sql = "UPDATE intra_worklog SET ended_at = ? , working = ? WHERE working = 1";
if($stmt = $mysqli->prepare($sql)){
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("ss", $param_eT, $started );
    
    // Set parameters
    $param_eT = date('Y-m-d H:i:s');
    $started = 0;

    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // store result
        $stmt->store_result();
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }
    // Close statement
    $stmt->close();
}

$sql = "UPDATE intra_tasks SET status = 1 WHERE period_type = 2";
if($stmt = $mysqli->prepare($sql)){

    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // store result
        $stmt->store_result();
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }
    // Close statement
    $stmt->close();
}
// Close connection 
$mysqli->close(); 	
?>
