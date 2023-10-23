<?php
require_once "session.php";
require_once "config.php";

// Define variables and initialize with empty values
$username = $username_err = $started_at = $ended_at = "";
$day = date('d');
$working = $_POST['started'];


$sql = "SELECT role_id, role_desc 
        FROM intra_roles";
if($stmt = $mysqli->prepare($sql)){
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // Store result
        $stmt->store_result();
        // Bind result variables
	    $stmt->bind_result($role_id, $role_desc);
            while ($stmt->fetch()) {
                // Append the start and end times to the $worklogs variable
                $roles[] = array('role_id' => $role_id, 'role_desc' => $role_desc);
            }
	        $stmt->close();
    }
}

    if ($_SESSION["role"] <= 1){
        $sql = "SELECT task_id, task, t.task_desc, t.started_at, t.ended_at, r.role_id, r.role_desc, t.status, s.status_desc, t.request_id 
                FROM intra_tasks as t 
                LEFT JOIN intra_roles as r on t.role=role_id 
                LEFT JOIN intra_status as s on t.status=s.status_id
                WHERE t.period_type = 2
                ORDER BY role_id, t.status, task_id";
    } else {
        $sql = "SELECT task_id, task, t.task_desc, t.started_at, t.ended_at, ro.role_id, ro.role_desc, t.status, s.status_desc, t.request_id  
                FROM intra_tasks as t
                LEFT JOIN intra_roles as ro on t.role=ro.role_id
                LEFT JOIN intra_status as s on t.status=s.status_id
                WHERE role = ? AND t.status != 7 AND t.period_type = 2
                ORDER BY t.status, t.task_id";
    }

    if($stmt = $mysqli->prepare($sql)){
        if ($_SESSION["role"] > 1){
        	$stmt->bind_param("s", $_SESSION["role"]);
        }
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($id, $task, $task_desc, $sT, $eT, $role_id, $role, $status, $status_desc, $request_id);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $worklogs variable
            $tasks[] = array('task_id' => $id, 'task' => $task, 'task_desc' => $task_desc, 'start_time' => $sT, 'end_time' => $eT, 'role_id' => $role_id, 'role' => $role, 'status' => $status, 'status_desc' => $status_desc, 'request_id' => $request_id);
    	}
    	$stmt->close();
    }

if(!empty($tasks)) {
    foreach($tasks as $task) {
        $count = 0;

        echo '<tr class="hoverInfos">';
        echo '<td class="task_desc align-middle">' . $task['task'] . '</td>';

        if ($_SESSION["role"] <= 1){
            echo '<td class="align-middle">' . $task['role'] . '</td>';
        }
        echo '<td class="align-middle">' . $task['status_desc'] . '</td>';
        echo '<td class="but"><center>';
        echo '<form method="post" action="update_tasks.php">';
        echo '<input type="hidden" name="task_id" value="' . $task['task_id'] . '">';
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
										<td class="task_desc align-middle">Responsible: </td>
										<td class="align-middle">
											<select id="role-DD" class="task request" value="<?php echo $task['role'] ?>" name="role" required/>
											<span class="invalid-feedback"><?php echo $username_err; ?></span>
<?php                                       
											if(!empty($roles)) {
												foreach($roles as $role) {
													if ($role['role_id'] === $task['role_id']) {
?>        
														<option value=<?php echo $role['role_id'] . ' selected>' . $role['role_desc']; ?></option>
<?php	                                            
													} else { 
?>	
														<option value=<?php echo $role['role_id'] . '>' . $role['role_desc']; ?></option>
<?php	
													}
												}
											}
?>     	
											</select>
										</td>
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

