<?php
require_once "session.php";                          
require_once "config.php";

// Define variables and initialize with empty values
$username = $started_at = $ended_at = "";

if (!isset($_POST['type'])) {
    $_POST['type'] = '';

}


//working.php Who is working

if ($_POST['type'] == "workingsNow") {

$now = date('Y-m-d H:i');

$h6Ago = strtotime($now);
$h6Ago = strtotime("-6 hour", $h6Ago);
$h6Ago =  date('Y-m-d H:i', $h6Ago);

    $sql = "SELECT u.id, u.username, DATE_FORMAT(started_at, '%H:%i'), started_at 
            FROM intra_worklog as w 
            JOIN intra_users as u on w.user_id = u.id 
            WHERE working > 0 ORDER BY started_at";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($user_id, $username, $start_time, $start_time_original);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $worklogs variable
            $workingsNows[] = array('user_id' => $user_id, 'username' => $username, 'start_time' => $start_time, 'start_time_original' => $start_time_original);
    	}
    	$stmt->close();
    }    
    if(!empty($workingsNows)) {
        foreach($workingsNows as $workingsNow) {
            $user_id = $workingsNow['user_id'];
            $username = $workingsNow['username'];
            $start = new DateTime($workingsNow['start_time']);
            if ($h6Ago < $workingsNow['start_time_original']) {
                $h6Ago = $workingsNow['start_time_original'];
            }           
            ?>
            <tr><td  class="align-middle un"><?php echo $username; ?><input type="hidden" name="user_id" value="<?php echo $user_id; ?>"></td>
            <td  class="align-middle"><?php echo $workingsNow['start_time']; ?></td>
            <td class="but">
                <button type="submit" name="workingsNowStop" class="bi bi-stop-fill button btn btn-danger workingsNowBtn" value="workingsNowStop" ></button>
                <div id="workingsNowInfo_modal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h6 class="title center mb-3">Set Endtime</h6>
                            <table class="requests">
                                <td>
 			                    <form method="post" action="track_work.php">
                                    <input type="datetime-local" name="eT" class="calendar" required min="<?php echo substr($workingsNow['start_time_original'],0,16); ?>" max="<?php echo date('Y-m-d H:i') ?>" value="<?php echo substr($h6Ago,0,16); ?>" >		                
                                    <input type="hidden" name="username" value="<?php echo $username; ?>">		                
                                    <button type="submit" id="workingsNowStopSend" name="workingsNowStopSend" class="bi bi-stop-fill button btn btn-danger workingsNowStopSend" value="workingsNowStopSend" ></button>
                                </form>
                                </td>
                           </table>
                    </div>
                </div>            
                    <button class="bi bi-info-circle btn btn-white workingsNowBtn" ></button>
                    <div id="worktimesNow_modal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
					           <h6 class="title mt-2 mb-3"><?php echo $username; ?>'s work today</h6>
					           <table class="tasklog table" id="tasklogsNow">
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
            </td></tr>
            <?php
        }
    }
exit;

//working.php Who was working

} elseif ($_POST['type'] == "workingsSummary") {

$date = $_POST["date"];

    
  $sql = "SELECT u.id, u.username, ROUND(SUM(TIME_TO_SEC(TIMEDIFF(ended_at,started_at)))/3600,1) 
          FROM `intra_worklog` as w 
          JOIN intra_users as u on w.user_id=u.id 
          WHERE DATE_FORMAT(started_at, '%Y-%m-%d') = ? 
          GROUP BY u.username";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("s", $date);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($user_id, $username, $wtime);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $worklogs variable
            $workingsSummarys[] = array('user_id' => $user_id, 'username' => $username, 'wtime' => $wtime);
    	}
    	$stmt->close();
    }

    if(!empty($workingsSummarys)) {
        foreach($workingsSummarys as $workingsSummary) {
            $username = $workingsSummary['username'];
            unset($workings);
            
    $sql = "SELECT w.id, u.id, u.username, DATE_FORMAT(started_at, '%Y-%m-%d %H:%i'), DATE_FORMAT(ended_at, '%Y-%m-%d %H:%i'), DATE_FORMAT(started_at, '%Y-%m-%d'), DATE_FORMAT(ended_at, '%Y-%m-%d') 
            FROM intra_worklog as w 
            JOIN intra_users as u on w.user_id = u.id 
            WHERE DATE_FORMAT(started_at, '%Y-%m-%d') = ? AND u.username = ?
            ORDER BY started_at";
            if($stmt = $mysqli->prepare($sql)){
            	$stmt->bind_param("ss", $date, $workingsSummary['username']);
            	$stmt->execute();
            	$stmt->store_result();
            	$stmt->bind_result($wid, $userid, $username, $start_time, $end_time, $sd, $ed);
            	while ($stmt->fetch()) {
                    // Append the start and end times to the $worklogs variable
                    $workings[] = array('wid' => $wid, 'userid' => $userid, 'username' => $username, 'start_time' => $start_time, 'end_time' => $end_time, 'sd' => $sd, 'ed' => $ed);
            	}
            	$stmt->close();
            }            
            ?>
            <tr><td class="align-middle"><?php echo $username; ?>
                    <input type="hidden" name="user_id" value="<?php echo $workingsSummary['user_id']; ?>">
                </td>
                <td class="align-middle"><?php echo $workingsSummary['wtime']; ?> h</td>
                <td class="but">
		        <button type="submit" name="workings" class="bi bi-info-circle button btn modal-btn" value="workings" ></button>
                <div id="workingInfo_modal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h6 class="title center mb-3"><?php echo $username ?></br></br>Work times</h6>
                        <form method="POST" action="">
                            <table class="requests">
                        	    <thead>
                        	    <tr>
							        <th>Start Time</th>
							        <th>End Time</th>
                       	        </tr>
                        	    </thead>
                                <?php

                                if(!empty($workings)) {
                                    foreach($workings as $working) {
                                ?>          
                                            <tr><td class="align-middle st"><input type="hidden" name="<?php echo $working['wid']; ?>userid" value="<?php echo $working['userid']; ?>">
                                                                         <input type="hidden" name="wid" value="<?php echo $working['wid']; ?>">
                                                                         <input type="hidden" name="<?php echo $working['wid']; ?>sd" value="<?php echo $working['sd']; ?>">
                                                                         <input type="hidden" name="<?php echo $working['wid']; ?>ed" value="<?php echo $working['ed']; ?>">
                                                                         <input type="datetime-local" name="<?php echo $working['wid']; ?>start_time" required min="<?php echo substr($workingsNow['start_time_original'],0,16); ?>" max="<?php echo date('Y-m-d H:i') ?>" value="<?php echo $working['start_time']; ?>" >		                
                                                                         <input type="hidden" name="<?php echo $working['wid']; ?>start_time" value="<?php echo $working['start_time']; ?>"></td>
                                                <td class="align-middle et"><input type="datetime-local" name="<?php echo $working['wid']; ?>end_time" required min="<?php echo substr($workingsNow['end_time_original'],0,16); ?>" max="<?php echo date('Y-m-d H:i') ?>" value="<?php echo $working['end_time']; ?>" >		                
                                                                         <input type="hidden" name="<?php echo $working['wid']; ?>end_time" value="<?php echo $working['end_time']; ?>"></td>
                                            </tr>
                                <?php
                                    }
                                }
                                ?>
                           </table>
                           <!--<input class="data_btn" type="submit" name="save_workhour_changes" value="Save">-->
                        </form>
                        <h6 class="title center mb-3">To Do's</h6>
				        <table class="tasklog table" id="tasklogsSummary">
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
            </td></tr>
            <?php
        }
    }
exit;
}



// Define variables and initialize with empty values
$username = $started_at = $ended_at = "";

$username = $_SESSION["username"];

$day = date('d');
$month = date('m');
$year = date('Y');

$sql = "SELECT started_at, working 
        FROM intra_worklog as w 
        JOIN intra_users as u on w.user_id=u.id 
        WHERE u.username = ? 
        ORDER BY started_at DESC LIMIT 1";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("s", $username);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($sT, $working);
    	$stmt->fetch();
    	$stmt->close();
    }

if ($working == 1) {
$sql = "UPDATE intra_worklog SET ended_at = ? WHERE user_id = ? and started_at = ?";
if($stmt = $mysqli->prepare($sql)){
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("sss", $param_eT,  $param_username, $sT);

    // Set parameters
    $param_eT = date('Y-m-d H:i:s');
    $param_username = $_SESSION["id"];
    
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

$sql = "SELECT started_at
             , ended_at 
        FROM intra_worklog as w 
        JOIN intra_users as u on w.user_id=u.id 
        WHERE u.username = ? and day(ended_at) = ? and month(ended_at) = ?  and year(ended_at) = ? 
        ORDER BY started_at";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("ssss", $username, $day, $month, $year);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($start_time, $end_time);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $worklogs variable
            $worklogs[] = array('start_time' => $start_time, 'end_time' => $end_time);
    	}
    	$stmt->close();
    }

if(!empty($worklogs)) {
    foreach($worklogs as $worklog) {

        $start_time = $worklog['start_time'];
        $end_time = $worklog['end_time'];
        
        // calculate the time difference in seconds
        $time_diff = strtotime($end_time) - strtotime($start_time);
        
        // convert the time difference to hours, minutes, and seconds
        $hours = floor($time_diff / 3600);
        $minutes = floor(($time_diff % 3600) / 60);
        $seconds = $time_diff % 60;
        
        echo '<tr><td>' . substr($start_time, 10, 6) .  '</td>';
        echo '<td class="end_time">' . substr($end_time, 10, 6) . '</td>';
        echo '<td>' . "$hours:$minutes" . '</td> </tr>';
    }
}

?>