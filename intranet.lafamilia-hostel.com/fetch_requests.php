<?php
require_once "session.php";
require_once "config.php";

$username_err = "";

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

$sql = "SELECT re.request_id
              ,re.request
              ,re.request_desc
              ,u.username
              ,u2.username
              ,re.status
              ,s.status_desc
              ,re.due_date
        FROM intra_requests as re 
        LEFT JOIN intra_roles as ro on re.role=ro.role_id
        LEFT JOIN intra_users as u on re.requested_by=u.id
        LEFT JOIN intra_users as u2 on re.responsible=u2.id
        LEFT JOIN intra_status as s on re.status=s.status_id";
        if (isset($_POST["arch"])) {
$sql .= " WHERE re.status = 7 
          OR (re.status = 3 AND re.due_date < CURDATE()) 
        ORDER BY re.status, re.due_date, re.request_id";
        } else {
$sql .= " WHERE (re.status != 7 AND re.status != 3) 
            OR (re.status = 3 AND re.due_date >= CURDATE()) 
        ORDER BY re.due_date, re.status, re.request_id";
        }   

if($stmt = $mysqli->prepare($sql)){
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($reqid, $req, $req_desc, $requestor, $resp, $stat, $reqStat, $dueDate);
	while ($stmt->fetch()) {
        // Append the start and end times to the $workhours variable
        $requests[] = array('reqid' => $reqid, 'req' => $req, 'req_desc' => $req_desc, 'requestor' => $requestor, 'resp' => $resp , 'stat' => $stat, 'reqStat' => $reqStat, 'dueDate' => $dueDate);
	}
	$stmt->close();
}

if(!empty($requests)) {
    foreach($requests as $request) {
	
		$day = substr($request['dueDate'],8,2);
		$month = substr($request['dueDate'],5,2);
		$year = substr($request['dueDate'],2,2);
		
		if ($request['stat'] == 7) {
		    
			echo '<tr class="hoverInfos grey">';
		} elseif ($request['stat'] == 3) {
			echo '<tr class="hoverInfos thick">';
		} elseif ($request['stat'] == 4) {
			echo '<tr class="hoverInfos blue">';
		} elseif ($request['stat'] == 5) {
			echo '<tr class="hoverInfos orange">';
		} elseif ($request['stat'] == 6) {
			echo '<tr class="hoverInfos thick green">';
		} else {
			echo '<tr class="hoverInfos">';
		}
		echo '<td class="align-middle"><span class="hoverInfo"><h6>Request Description</h6>' . $request['req_desc'] . '</span>' . $request['req'] . '</td>';
		echo '<td class="align-middle">' . $request['requestor'] . '</td>';
		echo '<td class="align-middle">' . $request['resp'] . '</td>';
		echo '<td class="align-middle">' . $request['reqStat'] . '</td>';
	
		if ($request['dueDate'] < date("Y-m-d")) {
			echo '<td class="align-middle" style="background-color:red;">' . $day . '.' . $month . '.' . $year . '</td>';
		} else {
			echo '<td class="align-middle">' . $day . '.' . $month . '.' . $year . '</td>';
		}
		
		if ($_SESSION['role'] == "0") {
			if ($request['stat'] == "2") {
?>	
				<td class="but"><center>
					<form method="post" action="update_status.php">
						<input type="hidden" name="request_id" value="<?php echo $request['reqid'] ?>">
						<button type="submit" name="approve" class="mr-1 mb-1 bi bi-check button btn btn-light" value="approve" ></button>
						<button type="submit" name="improve" class="mr-1 mb-1 bi bi-arrow-left-circle button btn btn-light" value="improve" ></button>
						<button type="submit" name="deny" class="mr-1 mb-1 bi bi-x button btn btn-danger" value="deny" ></button>
					</form>
<?php        
			} elseif ($request['stat'] == "3" || $request['stat'] == "7") {
?>	
				<td class="align-middle">
					<form method="post" action="update_status.php">
						<input type="hidden" name="request_id" value="<?php echo $request['reqid'] ?>">
						<button type="submit" name="approve_back" class="bi bi-arrow-left-circle button btn btn-danger" value="approve_back" ></button>
					</form>
<?php        
			} elseif ($request['stat'] == "8") {
?>	
				<td class="align-middle">
					<form method="post" action="update_status.php">
						<input type="hidden" name="request_id" value="<?php echo $request['reqid'] ?>">
						<button type="submit" name="improve_back" class="bi bi-arrow-right-circle button btn btn-light" value="improve_back" ></button>
					</form>
<?php	
			} else { echo '<td class="align-middle">';}; 
		} elseif ($_SESSION['role'] >= "0" && $_SESSION['role'] < "99") {
			if ($request['stat'] == "2") {
?>	
				<td class="but"><center>
	
<?php        
			} elseif ($request['stat'] == "8") {
?>	
				<td class="align-middle">
					<form method="post" action="update_status.php">
						<input type="hidden" name="request_id" value="<?php echo $request['reqid'] ?>">
						<button type="submit" name="improve_back" class="bi bi-arrow-right-circle button btn btn-light" value="improve_back" ></button>
					</form>	
<?php	
			}  else { echo '<td class="align-middle">';}; 
		} 
?>	
			
				<button id="request_modal_button" name="editRequest" class="bi ml- 1 bi button btn btn-primary mod" value="editRequest" ><i class="bi bi-pen"></i></button>
					<div id="request_modal" class="modal">
						<div class="modal-content">
							<span class="close">&times;</span>
							<h6 class="title mb-3">Edit request</h6>
							<form action="update_requests.php" method="post">
								<div class="table-responsive">
								<table class="requests table">
									<tr>
										<td class="request_desc align-middle">Request: </td>
										<td class="align-middle">
											<input type="hidden" name="request_id" value="<?php echo $request['reqid'] ?>">
											<input type="text" class="request" name="request" value="<?php echo $request['req'] ?>" size="20" required></td>
											
									</tr>
									<tr>
										<td class="request_desc align-middle">Description: </td>
										<td class="align-middle"><textarea type="text" class="request" name="request_desc" value="<?php echo $request['req_desc']; ?>" size="300" rows="4" required><?php echo $request['req_desc']; ?></textarea></td>
									</tr>
									<tr>
										<td class="request_desc align-middle">Requested by: </td>
										<td class="align-middle"><input type="text" disabled class="request" name="requestor" value="<?php echo $request['requestor']; ?>" size="20">
											<input type="hidden" name="requestor2" value="<?php echo $_SESSION["id"] ?>"></td>
									</tr>
									<tr>
										<td class="request_desc align-middle">Responsible: </td>
										<td class="align-middle">
											<select class="request" value="" name="resp" required/>
											<span class="invalid-feedback"><?php echo $username_err; ?></span>
<?php                                       
											if(!empty($users)) {
												foreach($users as $user) {
													if ($user['username'] === $request['resp']) {
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
										<td class="request_desc align-middle">Due Date: </td>
										<td class="align-middle"><input type="date" class="request" name="due_date" value="<?php echo substr($request['dueDate'],0,10); ?>" size="20">
											<input type="hidden" name="requestor2" value="<?php echo $_SESSION["id"] ?>"></td>
									</tr>
									</table>
							</div>
								<input class="data_btn" type="submit" name="update_req" value="Save">
							</form>
						</div>
					</div>
<?php	    
			if ($request['stat'] == "2") {
				echo '</center>';
			}							
			echo '</td></tr>';
    }
} 

?>
