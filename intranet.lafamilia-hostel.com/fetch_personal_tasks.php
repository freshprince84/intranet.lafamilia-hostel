<?php
require_once "session.php";
require_once "config.php";

// Define variables and initialize with empty values
$username = $username_err = $started_at = $ended_at = "";
$dateNow = date('Y-m-d');
$day = date('d');
$working = $_POST['started'];

$sql = "SELECT id, username 
        FROM intra_users";
if($stmt = $mysqli->prepare($sql)){
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // Store result
        $stmt->store_result();
        // Bind result variables
	    $stmt->bind_result($userid, $username);
            while ($stmt->fetch()) {
                // Append the start and end times to the $worklogs variable
                $users[] = array('userid' => $userid, 'username' => $username);
            }
	        $stmt->close();
    }
}

    if ($_SESSION["role"] <= 1){
        $sql = "SELECT t.task_id, t.task, t.task_desc, t.user_id, t.due_date, t.status, s.status_desc, t.request_id, us.username as 'requestor', u.id, u.username  
                FROM intra_tasks as t 
                JOIN intra_users as u on t.user_id=u.id
                LEFT JOIN intra_requests as r on t.request_id = r.request_id
                JOIN intra_users as us on r.requested_by = us.id
                LEFT JOIN intra_status as s on t.status=s.status_id
                WHERE  period_type = 1"; 
                if (isset($_POST["arch"])) {
        $sql .=   " AND (t.status = 6 AND t.due_date <= ?)";
                } else {
        $sql .=   " AND (t.status != 6 OR t.due_date >= ?)";
                }
        $sql .= " ORDER BY t.due_date, t.status, u.id";
    } else {
        $sql = "SELECT t.task_id, t.task, t.task_desc, t.user_id, t.due_date, t.status, s.status_desc, t.request_id, us.username as 'requestor', u.id, u.username 
                FROM intra_tasks as t
                JOIN intra_users as u on t.user_id = u.id
                LEFT JOIN intra_requests as r on t.request_id = r.request_id
                JOIN intra_users as us on r.requested_by = us.id
                LEFT JOIN intra_status as s on t.status = s.status_id
                WHERE u.id = ? AND t.status != 7 AND period_type = 1";
                if (isset($_POST["arch"])) {
        $sql .=   " AND (t.status = 6 AND t.due_date <= ?)";
                } else {
        $sql .=   " AND (t.status != 6 OR t.due_date >= ?)";
                }
        $sql .= "   ORDER BY u.id, t.due_date, t.status";
    }

    if($stmt = $mysqli->prepare($sql)){
        if ($_SESSION["role"] > 1){
        	$stmt->bind_param("is", $_SESSION["id"], $dateNow);
        } else {
        	$stmt->bind_param("s", $dateNow);
        }
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($id, $task, $task_desc, $resp, $due_date, $status, $status_desc, $request_id, $requestor, $user_id, $username);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $worklogs variable
            $tasks[] = array('task_id' => $id, 'task' => $task, 'task_desc' => $task_desc, 'resp' => $resp, 'due_date' => $due_date, 'status' => $status, 'status_desc' => $status_desc, 'request_id' => $request_id, 'requestor' => $requestor, 'user_id' => $user_id , 'username' => $username);
    	}
    	$stmt->close();
    }

if(!empty($tasks)) {
    foreach($tasks as $task) {
        $count = 0;

		$day = substr($task['due_date'],8,2);
		$month = substr($task['due_date'],5,2);
		$year = substr($task['due_date'],2,2);

        echo '<tr class="hoverInfos">';
        echo '<td class="task_desc align-middle"><span class="hoverInfo"><h7>Description</h7><br>' . $task['task_desc'] . '</span>' . $task['task'] . '</td>';

        if ($_SESSION["role"] <= 1){
            echo '<td class="align-middle">' . $task['username'] . '</td>';
        }
        echo '<td class="align-middle">' . $task['status_desc'] . '</td>';

		if ($task['due_date'] < date("Y-m-d")) {
			echo '<td class="align-middle" style="background-color:red;">' . $day . '.' . $month . '.' . $year . '</td>';
		} else {
			echo '<td class="align-middle">' . $day . '.' . $month . '.' . $year . '</td>';
		}
        echo '<td class="but"><center>';
        echo '<form method="post" action="update_tasks.php">';
        echo '<input type="hidden" name="task_id" value="' . $task["task_id"] . '">';
        echo '<input type="hidden" name="user_id" value="' . $_SESSION["id"] . '">';

            if($task['status'] == "1"){
?>
					<button type="submit" name="inProgress" class="mr-1 mb-1 bi button btn btn-light" <?php if($working < 1) { echo "disabled"; } ?> value="4" ><i class="bi bi-play"></i></button>
<?php
			} elseif ($task['status'] == "4") {
?>
					<button type="submit" name="qm" class="mr-1 mb-1 bi bi-check button btn btn-light" <?php if($working < 1) { echo "disabled"; } ?> value="5" ></button>
					<button type="submit" name="open" class="mr-1 mb-1 bi bi-arrow-left-circle button btn btn-danger" <?php if($working < 1) { echo "disabled"; } ?> value="1" ></button>
<?php
			} elseif ($task['status'] == "5") {
?>
					<button type="submit" name="done" class="mr-1  mb-1 bi bi-check button btn btn-light" <?php if($working < 1) { echo "disabled"; } ?> value="6" ></button>
					<button type="submit" name="inProgress" class="mr-1 mb-1 bi bi-arrow-left-circle button btn btn-danger" <?php if($working < 1) { echo "disabled"; } ?> value="4" ></button>
<?php
			} elseif ($task['status'] == "6") {

?>
					<button type="submit" name="inProgress" class="mr-1 mb-1 bi bi-arrow-left-circle button btn btn-light" <?php if($working < 1) { echo "disabled"; } ?> value="4" ></button>
<?php
		    }
            echo '</form>';

            if ($_SESSION["role"] == 0){
?>                
				<button id="task_modal_button" name="editTask" class="bi ml- 1 bi button btn btn-primary taskMod" value="editTask" ><i class="bi bi-pen"></i></button>
					<div id="task_modal" class="modal">
						<div class="modal-content">
							<span class="close">&times;</span>
							<h6 class="title mb-3">Edit task</h6>
							<form action="update_tasks.php" method="post">
								<div class="table-responsive">
								<table class="tasks table">
									<tr>
										<td class="task_desc align-middle">Task: </td>
										<td class="align-middle">
											<input type="hidden" name="task_id" value="<?php echo $task['task_id'] ?>">
											<input type="text" class="task request" name="task" value="<?php echo $task['task'] ?>" size="20" required></td>
									</tr>
									<tr>
										<td class="task_desc align-middle">Description: </td>
										<td class="align-middle"><textarea type="text" class="task request" name="task_desc" value="<?php echo $task['task_desc']; ?>" size="300" rows="4" required><?php echo $task['task_desc']; ?></textarea></td>
									</tr>
									<tr>
										<td class="task_desc align-middle">Requested by: </td>
										<td class="align-middle"><input type="text" disabled class="request" name="requestor" value="<?php echo $task['requestor']; ?>" size="20">
											<input type="hidden" name="requestor2" value="<?php echo $_SESSION["id"] ?>"></td>
									</tr>
									<tr>
										<td class="task_desc align-middle">Responsible: </td>
										<td class="align-middle">
											<select class="task request" value="<?php echo $task['resp'] ?>" name="resp" required/>
											<span class="invalid-feedback"><?php echo $username_err; ?></span>
<?php                                       
											if(!empty($users)) {
												foreach($users as $user) {
													if ($user['userid'] === $task['resp']) {
?>        
														<option value=<?php echo $user['userid'] . ' selected>' . $user['username']; ?></option>
<?php	                                            
													} else { 
?>	
														<option value=<?php echo $user['userid'] . '>' . $user['username']; ?></option>
<?php	
													}
												}
											}
?>     	
											</select>
										</td>
									</tr>
									<tr>
										<td class="task_desc align-middle">Due Date: </td>
										<td class="align-middle"><input type="date" class="task request" name="due_date" value="<?php echo substr($task['due_date'],0,10); ?>" size="20">
									</tr>
									</table>
							</div>
								<input class="data_btn" type="submit" name="update_tsk" value="Save">
								<input class="data_btn btn-danger" type="submit" name="delete_tsk" value="Delete">
							</form>
						</div>
					</div>                
                
<?php		                 
                
            }	
        echo "</center></td></tr>";                    
            
            
    }
}

?>
