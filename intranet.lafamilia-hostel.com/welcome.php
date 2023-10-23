<?php
require_once "session.php";
require_once "config.php";
include "head.php";

// Define variables and initialize with empty values
$req = $userid = $username = $username_err = $started_at = $ended_at = "";
$aday = date('d');
$amonth = date('m');
$ayear = date('Y');

$today = date('Y-m-d');

$inAWeek = $today;
$inAWeek = strtotime($inAWeek);
$inAWeek = strtotime("+7 day", $inAWeek);
$inAWeek =  date('Y-m-d', $inAWeek);


  $sql = "SELECT DATE_FORMAT(ended_at, '%d.%m'), ROUND(SUM(TIME_TO_SEC(TIMEDIFF(ended_at,started_at)))/3600,1) 
          FROM `intra_worklog` as w 
          JOIN intra_users as u on w.user_id=u.id 
          WHERE u.username = ? AND month(ended_at) = ? 
          GROUP BY day(ended_at)";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("ss", $_SESSION["username"], $amonth);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($wday, $whours);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $worklogs variable
            $worklogs[] = array('wday' => $wday, 'whours' => $whours);
    	}
    	$stmt->close();
    }

  $sql = "SELECT monthname(ended_at), ROUND(SUM(TIME_TO_SEC(TIMEDIFF(ended_at,started_at)))/3600,1) 
          FROM intra_worklog as w 
          JOIN intra_users as u on w.user_id=u.id 
          WHERE u.username = ? 
          GROUP BY month(ended_at)";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("s", $_SESSION["username"]);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($month, $hours);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $workhours variable
            $workhours[] = array('month' => $month, 'hours' => $hours);
    	}
    	$stmt->close();
    }

  $sql = "SELECT YEAR(status_ts) AS year, monthname(status_ts) AS month, COUNT(task_id) AS task_count
          FROM intra_tasklog
          WHERE user_id = ?
            AND status = 5
         GROUP BY YEAR(status_ts),
                  MONTH(status_ts),
                  status
         ORDER BY YEAR(status_ts),
                  MONTH(status_ts),
                  status";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("i", $_SESSION["id"]);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($year, $month, $task_count);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $taskstats variable
            $tasks[] = array('year' => $year, 'month' => $month, 'task_count' => $task_count);
    	}
    	$stmt->close();
    }

  $sql = "SELECT id, username FROM intra_users";
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

if($_SERVER["REQUEST_METHOD"] === "POST"){
 
    if (isset($_POST["save_req"])) {
        $req = $_POST["request"];
        $req_desc = $_POST["request_desc"];
        $requestor2 = $_POST["requestor2"];
        $resp = $_POST["resp"];
        $due_date = $_POST["due_date"];
        if (!isset($_POST["createTask"])) {
            $task_id = 999999999;
            $sql = "INSERT INTO intra_requests (request, request_desc, requested_by, responsible, due_date, task_id) VALUES (?, ?, ?, ?, ?, ?)";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("sssssi", $req, $req_desc, $requestor2, $resp, $due_date, $task_id);
                $stmt->execute();
                $stmt->close();
            }
            unset($task_id);
        } else {
            $sql = "INSERT INTO intra_requests (request, request_desc, requested_by, responsible, due_date) VALUES (?, ?, ?, ?, ?)";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("sssss", $req, $req_desc, $requestor2, $resp, $due_date);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
// Close connection
$mysqli->close();
}
$title="Dashboard";
if (date('H')<12) { 
    $welcome = 'Hola ' . $_SESSION["username"] . '. Que tengas buen dia!'; 
} elseif ((date('H')>=12)) { 
    $welcome = 'Que tengas una buena tarde ' . $_SESSION["username"] . '!'; 
}

echo "<title>" . $title . "</title>";
echo "</head>";
require_once "header.php";
?>
	<div class="win-start">
	    <div class="box mr-2" style="max-width:500px">
	        <div class="row chart">
                <canvas id="hoursWorked" style="width:20%;max-width:500px"></canvas>
            </div>
	        <div class="row">
		   		<div class="col text-center">
                    <button id="months_modal_button" class="bi bi-info-circle btn btn-white sub_btn mod" ></button>
                    <div id="month_modal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h6 class="title mb-3">Your work this month</h6>
		   			        <table class="worklog table" id="worklogs">
		   			        	<tr>
		   			        		<th>Day</th>
		   			        		<th>Time Worked</th>
		   			        	</tr>
<?php
                                if(!empty($worklogs)) {
                                    foreach($worklogs as $worklog) {
                                        echo '<tr><td class="align-middle">' . $worklog['wday'] . '</td>';
                                        echo '<td class="align-middle">' . $worklog['whours'] . ' h</td></tr>';
                                    }
                                }
?>
		   			        </table>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
	    <div class="box" style="max-width:500px">
	        <div class="row chart">
                <canvas id="tasks" style="width:20%;max-width:500px"></canvas>
            </div>
	        <div class="row">
		   		<div class="col text-center">
                </div>
            </div>            
        </div>
        <div class="box box-wide">
            <div class="row align-items-center">
                <div class="col-3 text-start">
                    <button id="request_modal_button" class="bi btn sub_btn mod">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="rgb(37, 165, 207)" class="bi bi-plus-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                    </button>
                    <div id="request_modal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h6 class="title mb-3">Add your request</h6>
                            <form action="" method="post">
                                <div class="table-responsive">
                                    <table class="requests table">
                            	        <tr>
                            	    	    <td class="request_desc align-middle">Request: </td>
                            	    		<td class="align-middle"><input type="text" class="request" name="request" value="<?php echo $req; ?>" size="20" required></td>
                            	        </tr>
                            	    	<tr>
                            	    		<td class="request_desc align-middle">Description: </td>
                            	    		<td class="align-middle"><textarea type="text" class="request" name="request_desc" value="Describe your request. Make sure to explain in terms of quality, time & costs." size="300" rows="4" required>"Describe your request. Make sure to explain in terms of quality, time & costs."</textarea></td>
                            	    	</tr>
                            	    	<tr>
                            	    		<td class="request_desc align-middle">Requested by: </td>
                            	    	    <td class="align-middle"><input type="text" disabled class="request" name="requestor" value="<?php echo $_SESSION["username"]; ?>" size="20">
                            	    	        <input type="hidden" name="requestor2" value="<?php echo $_SESSION["id"] ?>"></td>
                            	    	</tr>
                            	    	<tr>
                            	    		<td class="request_desc align-middle">Responsible: </td>
                            	    		<td class="align-middle">
                            	    		    <select class="request" value="<?php echo $username; ?>" name="resp" required/>
                                                <span class="invalid-feedback"><?php echo $username_err; ?></span>
			                                       	        <option value="" selected></option> 
			                                       <?php if(!empty($users)) {
                                            foreach($users as $user) {
			                                       ?>
			                                       	        <option value=<?php echo $user['userid'] . '>' . $user['username']; ?></option>
			                                       <?php   }
			                                             }
			                                        ?>     
			                                       	    </select>
                                            </td>
                            	    	</tr>
                            	    	<tr>
                            	    		<td class="request_desc align-middle">Due Date: </td>
                            	    	    <td class="align-middle"><input type="date" class="request" name="due_date" value="<?php echo $inAWeek ?>" size="20">
                            	    	        <input type="hidden" name="requestor2" value="<?php echo $_SESSION["id"] ?>"></td>
                            	    	</tr>
                            	    	<tr>
                            	    		<td class="request_desc align-middle">Create To Do: </td>
                            	    	    <td class="align-middle"><input type="checkbox" class="request" name="createTask" value=""</td>
                            	    	</tr>
                                    </table>
                                </div>
                                <input class="data_btn" type="submit" name="save_req" value="Save">
                            </form>
                        </div>
                    </div>            
                </div>
                <div class="col">
                    <h6 class="title">Requests</h6>
                </div>
                <div class="col-3 text-end">
                    <button onclick="archiveReq()" class="btn sub_btn"><i class="bi bi-archive"></i></button>
                </div>
            </div>
			<div class="row">
				<div class="col text-center">
                    				    
<!--                        <div class="tab">
                            <button onclick="archive()" style="float:right;">Archive</button>
                        </div>
-->				        <div class="mb-3">

				        </div>
				        <div class="scrollbox">
                        <table class="table" id="requests">
                            <thead>
                              <tr>
                                <th>Request<br><input type="text" class="search_input" onkeyup="filterTable()" placeholder="Filter"></th>
                                <th>Req. by<br><input type="text" class="search_input" onkeyup="filterTable()" placeholder="Filter"></th>
                                <th>Resp.<br><input type="text" class="search_input" onkeyup="filterTable()" placeholder="Filter"></th>
                                <th>Status<br><input type="text" class="search_input" onkeyup="filterTable()" placeholder="Filter"></th>
                                <th>Until<br><input type="text" class="search_input" onkeyup="filterTable()" placeholder="Filter"></th>
                                <?php if ($_SESSION['role'] < 99) { ?>
                                  <th class="align-middle">Opt.</th>
                                <?php } ?>
                              </tr>
                            </thead>
                            <tbody class="scrollbox">
                                <tr><td><div style="display: flex; justify-content: center; align-items: center; position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%);"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div></td></tr>                                        

                            </tbody>
                        </table>
                        </div>
				</div>
			</div>
		</div>
		<div class="box">
			<div class="row">
				<div class="col text-center">
					<h6 class="title mb-3">Coming up next</h6>
				        <div class="mb-5">
					       <h3>Tours</h3>
                        </div>
                </div>
            </div>
		</div>
   </div>

<?php include "footer.php"; ?>
<script src="js/header_role.js"></script>
<script>
var home = <?php echo json_encode($_SERVER['PHP_SELF']); ?>;
var userId = <?php echo json_encode($_SESSION["id"]); ?>;
</script>
<script>
var xValues = [];
var yValues = [];
var barColors = "#29b8e6";

<?php
  foreach($tasks as $task) {
    echo "xValues.push('".$task['month']."');";
    echo "yValues.push(".$task['task_count'].");";
  }
?>

new Chart("tasks", {
  type: "bar",
  data: {
    labels: xValues,
    datasets: [{
      backgroundColor: barColors,
      data: yValues
    }]
  },
  options: {
    legend: {display: false},
    title: {
      display: true,
      text: "Tasks done"
    }
  }
});
</script>
<script>
var xValues = [];
var yValues = [];
var barColors = "#29b8e6";

<?php
  foreach($workhours as $workhour) {
    echo "xValues.push('".$workhour['month']."');";
    echo "yValues.push(".$workhour['hours'].");";
  }
?>

new Chart("hoursWorked", {
  type: "bar",
  data: {
    labels: xValues,
    datasets: [{
      backgroundColor: barColors,
      data: yValues
    }]
  },
  options: {
    legend: {display: false},
    title: {
      display: true,
      text: "Hours worked"
    }
  }
});
</script>
<script src="js/we.js"></script>
</body>
</html>