<?php
	require_once 'config.php';

	error_log('Test');

	if(ISSET($_POST['add'])){
		if($_POST['request'] != ""){
			$request = $_POST['request'];
			$request_role = $_POST['request_role'];
 
			$sql = "INSERT INTO intra_requests (request, requested_by) VALUES (?,?)";		         
			if($stmt = $mysqli->prepare($sql)){
				// Bind variables to the prepared statement as parameters
				$stmt->bind_param( "ss", $request, $param_username);
				
				// Set parameters
				$param_username = $_SESSION["username"];
				$param_sT = date('Y-m-d H:i:s');
				

				// Attempt to execute the prepared statement
				if($stmt->execute()){
					// Store the started_at time in a session variable
				} else{
					echo "Oooooops! Something went wrong. Please try again later.";
				}
				// Close statement
				$stmt->close();
            }
		}

	} elseif($_GET['request_id'] != "" && $_GET['type'] == 1){
		$request_id = $_GET['request_id'];
 
		$mysqli->query("UPDATE `intra_requests` SET `status` = 'Done' WHERE `request_id` = $request_id") or die(mysqli_errno($conn));

	} elseif($_GET['request_id'] != "" && $_GET['type'] == 2){
		$request_id = $_GET['request_id'];
 
		$mysqli->query("UPDATE `intra_requests` SET `status` = '' WHERE `request_id` = $request_id") or die(mysqli_errno($conn));

	} elseif($_GET['type'] == 99){
		$request_id = $_GET['request_id'];
 
		$mysqli->query("DELETE FROM `intra_requests` WHERE `request_id` = $request_id") or die(mysqli_errno($conn));
	}	
	
	header('location: worktracker.php');
?>