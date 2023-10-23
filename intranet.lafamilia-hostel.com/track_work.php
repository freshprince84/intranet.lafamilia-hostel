<?php

require_once "session.php";                          
require_once "config.php";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] === "POST"){
	if (isset($_POST['workingsNowStart'])) {
		if(!isset($_SESSION['started_at'])){
			// Prepare an insert statement
			$sql = "INSERT INTO intra_worklog (user_id, started_at, working, d_salary) VALUES (?, ?, ?, ?)";		         
			if($stmt = $mysqli->prepare($sql)){
				// Bind variables to the prepared statement as parameters
				$stmt->bind_param( "ssss", $param_userid, $param_sT, $started, $_SESSION["salary"]);
				
				// Set parameters
				$param_userid = $_SESSION["id"];
				$param_sT = date('Y-m-d H:i:s');
				
				$started = 1;
				
				// Attempt to execute the prepared statement
				if($stmt->execute()){
					// Store the started_at time in a session variable
					$_SESSION['started_at'] = $param_sT;     
				} else{
					echo "Oooooops! Something went wrong. Please try again later.";
				}
				// Close statement
				$stmt->close();
            }
            $month = date('m');
		}
    } elseif (isset($_POST['workingsNowStopSend'])) {
            // Prepare a select statement
            $sql = "SELECT w.user_id, started_at 
                    FROM intra_worklog as w 
                    JOIN intra_users as u on w.user_id=u.id 
                    WHERE u.username = ? 
                    ORDER BY started_at DESC LIMIT 1";
            if($stmt = $mysqli->prepare($sql)){
            	$stmt->bind_param("s", $_POST["username"]);
            	$stmt->execute();
            	$stmt->store_result();
            	$stmt->bind_result($uID, $sT);
            	$stmt->fetch();
            	$stmt->close();
            }
            
            $sql = "UPDATE intra_worklog 
                    SET ended_at = ? , working = ? , quinzena = ? 
                    WHERE user_id = ? and started_at = ?";
            if($stmt = $mysqli->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("sssss", $param_eT, $started , $qui , $uID, $sT);
                
                // Set parameters
                //$param_eT = date('Y-m-d H:i:s');
                
                $date_string = $_POST["eT"];
                $date_parts = date_parse($date_string);
                $param_eT = sprintf('%04d-%02d-%02d %02d:%02d:00', $date_parts['year'], $date_parts['month'], $date_parts['day'], $date_parts['hour'], $date_parts['minute']);;


                $qui = 0;
                if(date('d') < 15) {
                    $qui = 1;
                } else if(date('d') >= 15) {
                    $qui = 2;
                }
                
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
    }
// Close connection
$mysqli->close(); 	
}

header("Location: working.php");
exit;

?>