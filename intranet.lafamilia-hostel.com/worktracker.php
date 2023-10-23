<?php
require_once "session.php";                          
require_once "config.php";
include "head.php";

// Define variables and initialize with empty values
$username = $started_at = $ended_at = $task = "";
$day = date('d');

$sql = "SELECT working 
        FROM intra_worklog as w 
        JOIN intra_users as u on w.user_id=u.id 
        WHERE u.username = ? 
        ORDER BY started_at DESC LIMIT 1";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("s", $_SESSION["username"]);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($started);
    	$stmt->fetch();
    	$stmt->close();
    }

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] === "POST"){
	if (isset($_POST['button1'])) {
		// Prepare an insert statement
		$sql = "INSERT INTO intra_worklog (user_id, started_at, working, d_salary) 
		        VALUES (?, ?, ?, ?)";		         
		if($stmt = $mysqli->prepare($sql)){
			// Bind variables to the prepared statement as parameters
			$stmt->bind_param( "isss", $param_userid, $param_sT, $started, $_SESSION["salary"]);
			
			// Set parameters
			$param_userid = $_SESSION["id"];
			$param_sT = date('Y-m-d H:i:s');
            if ($_POST['button1']=='working_nightshift') {
    			$started = 2;
            } else {
    			$started = 1;
            }
            
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
        $sql = "SELECT DATE_FORMAT(started_at, '%H:%i'), DATE_FORMAT(ended_at, '%H:%i') 
                FROM intra_worklog as w JOIN intra_users as u on w.user_id=u.id 
                WHERE u.username = ? 
                ORDER BY started_at desc LIMIT 1";
        if($stmt = $mysqli->prepare($sql)){
			$stmt->bind_param("s", $_SESSION["username"]);
			$stmt->execute();
	           $stmt->store_result();
	           $stmt->bind_result($start_time, $end_time);
            while ($stmt->fetch()) {
                // Append the start and end times to the $worklogs variable
                $worklogs[] = array('start_time' => $start_time, 'end_time' => $end_time);
            }
	           $stmt->close();
        }
    } elseif (isset($_POST['button2'])) {
        // Prepare a select statement
        $sql = "SELECT started_at 
                FROM intra_worklog as w 
                JOIN intra_users as u on w.user_id=u.id 
                WHERE u.username = ? 
                ORDER BY started_at DESC LIMIT 1";
        if($stmt = $mysqli->prepare($sql)){
        	$stmt->bind_param("s", $_SESSION["username"]);
        	$stmt->execute();
        	$stmt->store_result();
        	$stmt->bind_result($sT);
        	$stmt->fetch();
        	$stmt->close();
        }
        
        $sql = "UPDATE intra_worklog 
                SET ended_at = ? , working = ? , quinzena = ? 
                WHERE user_id = ? and started_at = ?";
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssss", $param_eT, $started , $qui , $param_userid, $sT);
            
            // Set parameters
            $param_eT = date('Y-m-d H:i:s');
            $qui = 0;
            if(date('d') <= 15) {
                $qui = 1;
            } else if(date('d') > 15) {
                $qui = 2;
            }
            $param_userid = $_SESSION["id"];
            
            $started = 0;
            
            if (!empty($_SESSION['started_at'])) {
                $start = new DateTime($_SESSION['started_at']);
            } else {
                $start = new DateTime($sT);
            }
            
            //Calculate Hours
#            $start = new DateTime($_SESSION['started_at']);
            $end = new DateTime($param_eT);
            $interval = $start->diff($end);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
            	$_SESSION['ended_at'] = $param_eT;
                // store result
                $stmt->store_result();
                unset($_SESSION['started_at']);
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            $stmt->close();
        }
    } elseif (isset($_POST["save_task"])) {
        $task = $_POST["task"];
        $task_desc = $_POST["task_desc"];
        $role = $_POST["ses_role"];
        $period_type = $_POST["period_type"];
        $status = 1;

        $sql = "INSERT INTO intra_tasks (task, task_desc, role, period_type, status) VALUES (?, ?, ?, ?, ?)";

            if($stmt = $mysqli->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ssiii", $task, $task_desc, $role, $period_type, $status);
            
                // Attempt to execute the prepared statement
                $stmt->execute();
               // Close statement
                $stmt->close();
            }
    }
// Close connection
$mysqli->close(); 	
}
$title="Track your work";
echo "<title>" . $title . "</title>";
echo "</head>";
require_once "header.php";
?>
	<div class="win win-start">
		<div class="box">
			<div class="d-flex flex-wrap justify-content-center align-items-center">
				<div class="col-auto">
<?php  
                if (date('H' ) >= 5 && date('H') < 20) {
?>     
				    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				        <button type="submit" name="button1" class="button btn btn-lg btn-success mr-3" <?php if($started > 0) { echo "disabled"; } ?> value="Button1" >
					           <i class="fa fa-thin fa-play"></i>
				        </button>
				        <button id="stop-button" type="submit" name="button2" class="mr-1 button btn btn-lg btn-danger ml-3" <?php if($started < 1) { echo "disabled"; } ?> value="Button2" >
					           <i class="fa fa-thin fa-stop"></i>
			    	    </button>
			           </form>
<?php
                } elseif (date('H') < 5 || date('H') >= 20) {
?>     
				        <button name="nightshift_modal" id="nightshift_modal" class="button btn btn-lg btn-success mr-3 sub_btn" <?php if($started > 0) { echo "disabled"; } ?> value="nightshift_modal" >
					           <i class="fa fa-thin fa-play"></i>
				        </button>            
                        <div id="nightshift_modal" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
					            <h6 class="title mb-3">Nightshift?</h6>
					            <span class="mb-3">Press yes if you will be working at 5 o'clock.<br>If you press "No", your work tracker will be stopped at 5am.</span>
					            <table class="worklog">
					           	    <tbody>
				                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				                            <tr><td>
				                               <button type="submit" name="button1" class="mb-3 ml-3 button btn btn-danger" <?php if($started > 0) { echo "disabled"; } ?> value="Button1" >
					                               No
				                               </button>
				                               </td>
				                               <td>
				                               <button type="submit" name="button1" class="mb-3 ml-3 button btn btn-success" <?php if($started > 0) { echo "disabled"; } ?> value="working_nightshift" >
					                               Yes, Nightshift
				                               </button>
				                               </td>
				                            </tr>
			                            </form>
					           	    </tbody>
					            </table>
                            </div>
                        </div>
				        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:inline-block;">
				            <button id="stop-button" type="submit" name="button2" class="mr-1 button btn btn-lg btn-danger ml-3" <?php if($started < 1) { echo "disabled"; } ?> value="Button2" >
					           <i class="fa fa-thin fa-stop"></i>
			    	        </button>
			            </form>
<?php
                    }
?>                      
				</div>
                        <button id="worktimes_modal_button" class="bi bi-info-circle btn btn-white sub_btn" ></button>
                        <div id="worktimes_modal" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
					            <h6 class="title mb-3">Today <?php echo '('.date('D').', '.date('d.M y').')' ?></h6>
					            <table class="worklog table" id="worklogs">
					            	<thead>
					            	<tr>
					            		<th>Start Time</th>
					            		<th>End Time</th>
					            		<th>Time Worked</th>
					            	</tr>
					            	</thead>
					            	<tbody>
					            	</tbody>
					            </table>
					            <h6 class="title mt-2 mb-3">To Do's</h6>
					            <table class="tasklog table" id="tasklogs">
					            	<thead>
					            	<tr>
					            		<th>Task</th>
					            		<th>Progress</th>
					            	</tr>
					            	</thead>
					            	<tbody>
					            	</tbody>
					            </table>
                            </div>
                        </div>
			</div>
		</div>
		<div class="box todo">
		    
            <div class="row align-items-center">
                <div class="col text-center">
				    <h6 class="title">My personal To Do's</h6>
                </div>		    
                <div class="col-3 text-end">
                    <button onclick="archivePT()" class="btn"><i class="bi bi-archive"></i></button>
                </div>
            </div>		    
		    
		    
			<div class="row">
				<div class="col text-center">
		            <table class="table" id="personal_tasks">
		              	<thead>
		              		<tr>
		              			<th>Task</th>
<?php
                                if($_SESSION["role"] <= 1) {
                                    echo '<th>User</th>';
                                }
?> 
                                <th>Status</th>
		              			<th>Until</th>
		              			<th>Action</th>
		              		</tr>
		              	</thead>
		              	<tbody>
		              	</tbody>
		            </table>
                </div>    
            </div>    
        </div>    
		<div class="box todo">
			<div class="row">
				<div class="col text-center">
                            <div class="container">
                              <div class="row mb-2 d-flex align-items-center" style="width: 100%;">

<?php
                        if($_SESSION["role"] == 0) {
?>
                                <div class="col-1 text-start">
                                    <button id="request_modal_button" class="bi btn sub_btn mod">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="rgb(37, 165, 207)" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                        </svg>
                                    </button>
                                    <div id="request_modal" class="modal">
                                        <div class="modal-content">
                                            <span class="close">&times;</span>
                                            <h6 class="title mb-3">Add task</h6>
                                            <form action="" method="post">
                                                <div class="table-responsive">
                                                    <table class="requests table">
                                            	        <tr>
                                            	    	    <td class="task_desc align-middle">Task: </td>
                                            	    		<td class="align-middle"><input type="text" class="task" name="task" value="<?php echo $task; ?>" size="30" required></td>
                                            	        </tr>
                                            	    	<tr>
                                            	    		<td class="task_desc align-middle">Description: </td>
                                            	    		<td class="align-middle"><textarea type="text" class="request" name="task_desc" value="Describe your task. Make sure to explain in terms of quality, time & costs." size="400" rows="4" required>"Describe your request. Make sure to explain in terms of quality, time & costs."</textarea></td>
                                            	    	</tr>
                                            	    	<tr>
                                            	    		<td class="task_desc align-middle">Role: </td>
                                            	    		<td class="align-middle">
                                            	    		    <div class="task-role-DD"></div>
                                                            </td>
                                            	    	</tr>
                                            	    	<tr>
                                            	    		<td class="task_desc align-middle">Period Type: </td>
                                            	    		<td class="align-middle">
                                            	    	    <select class="form-control" name="period_type" required=""><option value="Period Type">Period Type</option><option value="2">Daily</option><option value="3">Weekly</option></select>
                                                            </td>
                                            	    	</tr>
                                                    </table>
                                                </div>
                                                <input class="data_btn" type="submit" name="save_task" value="Save">
                                            </form>
                                        </div>
                                    </div>            
                                </div>

<?php
                        }
?>
                                <div class="col-8 text-center">
                                  <h6 class="title mb-1">Daily to do's for my area</h6>
                                </div>
<!--                            <div class="col-3 text-right">
                                    <div id="task-role-DD" class="task-role-DD"></div>
                                </div> -->
                              </div>
                            </div>
		                <table class="table" id="tasks">
		                	<thead>
		                		<tr>
		                			<th>Task</th>
<?php
                                        if($_SESSION["role"] <= 1) {
                                            echo '<th>Role</th>';
                                        }
?>       
                                    <th>Status</th>
		                			<th>Action</th>
		                		</tr>
		                	</thead>
		                	<tbody>
		                	</tbody>
		                </table>                    
				</div>
			</div>
		</div>
		<div class="box"><div class="mb-2"></div></div>
    </div>

<?php include "footer.php"; ?>

<script>var started = '<?php echo $started; ?>';</script>";
<script src="js/header_role.js"></script>
<script>
var home = <?php echo json_encode($_SERVER['PHP_SELF']); ?>;
var userId = <?php echo json_encode($_SESSION["id"]); ?>;
</script>
<script src="js/wt.js"></script>

<script>
$(document).ready(function() {
    $.ajax({
        url: "fetch_roles.php",
        type: "POST",
        data: {userid: <?php echo $_SESSION["id"]; ?>,
               login: 0
        },
        success: function(data) {
            $('.task-role-DD').html(data);
        }
    });
});
</script>
</body>
</html>