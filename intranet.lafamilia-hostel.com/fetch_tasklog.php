<?php
require_once "session.php";                          
require_once "config.php";

// Define variables and initialize with empty values
$user_id = $username = $username_err = $started_at = $ended_at = "";


if (isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];
} else {
    $user_id = $_SESSION["id"];
}

if (isset($_POST["date"])) {
    $date = $_POST["date"];
    $day = date('d', strtotime($date));
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    
} else {
    $day = date('d');
    $month = date('m');
    $year = date('Y');
}


$sql = "SELECT t.task
      , GROUP_CONCAT(DISTINCT s.status_desc, ' since ', LPAD(HOUR(tl.status_ts),2,'0'), ':', LPAD(MINUTE(tl.status_ts),2,'0') ORDER BY tl.status_ts ASC SEPARATOR ' -> ') as statuses
        FROM intra_tasklog as tl
        JOIN intra_tasks as t on tl.task_id=t.task_id
        JOIN intra_status as s on tl.status=s.status_id
        JOIN intra_users as u on tl.user_id=u.id
        WHERE u.id = ? AND DAY(tl.status_ts) = ? AND MONTH(tl.status_ts) = ?  AND YEAR(tl.status_ts) = ?
        GROUP BY tl.task_id";

    if($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param("isss", $user_id , $day, $month, $year);
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($task, $statuses);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $tasklogs variable
            $tasklogs[] = array('task' => $task, 'statuses' => $statuses);
    	}
    	$stmt->close();
    }

if(!empty($tasklogs)) {
    foreach($tasklogs as $tasklog) {

/*        // calculate the time difference in seconds
        $time_diff = strtotime($end_time) - strtotime($start_time);
        
        // convert the time difference to hours, minutes, and seconds
        $hours = floor($time_diff / 3600);
        $minutes = floor(($time_diff % 3600) / 60);
        $seconds = $time_diff % 60;
*/        
        echo '<tr><td>' . $tasklog['task'] .  '</td>';
        echo '<td>' . $tasklog['statuses'] . '</td>';
        echo '</tr>';
    }
}

?>