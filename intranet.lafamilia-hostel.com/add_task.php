<?php
	require_once 'config.php';
	
	if(ISSET($_POST['add'])){
		if($_POST['task'] != ""){
			$task = $_POST['task'];
			$task_role = $_POST['task_role'];
 
//error_log(print_r($_POST, true)); // This will log the POST data to the PHP error log


			$sql = "INSERT INTO intra_tasks (task, role, status) 
			        VALUES (?,?,?)";		         

			if($stmt = $mysqli->prepare($sql)){
				// Bind variables to the prepared statement as parameters
				$stmt->bind_param( "sii", $task, $task_role, $param_status);
				
				// Set parameters
				$param_username = $_SESSION["username"];
				$param_sT = date('Y-m-d H:i:s');
				$param_status = 1;
				

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

	} elseif($_GET['type'] == 99){
		$task_id = $_GET['task_id'];
 
		$mysqli->query("DELETE FROM `intra_tasks` WHERE `task_id` = $task_id") or die(mysqli_errno($conn));
	}	
	
	header('location: worktracker.php');
?>