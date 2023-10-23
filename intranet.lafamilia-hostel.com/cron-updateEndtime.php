<?php

require_once "config.php";

$sql = "UPDATE intra_worklog 
        SET ended_at = ? 
        WHERE  working = 1";
if($stmt = $mysqli->prepare($sql)){
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("s", $param_eT );
    
    // Set parameters
    $param_eT = date('Y-m-d H:i:s');

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
?>
