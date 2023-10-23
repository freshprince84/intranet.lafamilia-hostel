<?php

require_once "session.php";                          
require_once "config.php";
include "head.php";         

$title="Work Report";
echo "<title>" . $title . "</title>";
echo "</head>";
require_once "header.php";    

$username = "";
$month = date('m');

  $sql = "SELECT month(ended_at), monthname(ended_at), quinzena, concat(month(ended_at), quinzena) 
          FROM intra_worklog as w 
          WHERE quinzena > 0 
          GROUP BY concat(month(ended_at), quinzena) 
          ORDER BY month(ended_at) desc, quinzena desc";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($month_number, $rmonth, $quinzena, $rmonthQui);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $workhours variable
            $months[] = array('month_number' => $month_number, 'rmonth' => $rmonth, 'quinzena' => $quinzena, 'rmonthQui' => $rmonthQui);
    	}
    	$stmt->close();
    }
    
  $sql = "SELECT monthname(ended_at), SUM(ROUND(TIME_TO_SEC(TIMEDIFF(ended_at,started_at))/3600,2) * d_salary) 
          FROM intra_worklog 
          GROUP BY month(ended_at)";
    if($stmt = $mysqli->prepare($sql)){
    	$stmt->execute();
    	$stmt->store_result();
    	$stmt->bind_result($month, $mwage);
    	while ($stmt->fetch()) {
            // Append the start and end times to the $workhours variable
            $monthsals[] = array('month' => $month, 'mwage' => $mwage);
    	}
    	$stmt->close();
    }
?>                                                    		    

<div class="win win-start">
    <div class="box box-wide">
        <div class="tab">
<?php
        if(!empty($months)) {
            $c=0;
            foreach($months as $month) {
?>
                <button id="<?php echo $month["rmonth"] . ' / ' . substr($month["rmonthQui"], 0, 3); ?>" class="tablinks" onclick="openQuinzena(event, '<?php echo $month["rmonth"] . ' / ' . $month["quinzena"]; ?>' )"><?php echo substr($month["rmonth"], 0, 3) . ' / ' . $month["quinzena"]; ?></button>
<?php       
                if($c == 0) {
                    $activeId =  $month["rmonth"] . ' / ' . substr($month["rmonthQui"], 0, 3);
                }
                $c++;
            }
        }
        echo '</div>';
        if(!empty($months)) {
            foreach($months as $month) {
                $sql = "SELECT u.username
                              ,ROUND(SUM(TIME_TO_SEC(TIMEDIFF(ended_at,started_at)))/3600,1)
                              ,d_salary
                              ,idnr
                              ,ban
                        FROM intra_worklog as w 
                        JOIN intra_users as u on w.user_id = u.id
                        WHERE concat(month(ended_at), quinzena) = ? 
                        GROUP BY w.user_id
                        ORDER BY u.username";
                if ($stmt = $mysqli->prepare($sql)){
                  	$stmt->bind_param("s", $month['rmonthQui']);        
                  	$stmt->execute();
                  	$stmt->store_result();
                  	$stmt->bind_result($username, $hours, $sal, $idnr, $ban);
                  	while ($stmt->fetch()) {
                          // Append the start and end times to the $workhours variable
                          $workhours[] = array('username' => $username, 'hours' => $hours, 'sal' => $sal,  'idnr' => $idnr,  'ban' => $ban);
                  	}
                  	$stmt->close();
                }
?>

        <div id="<?php echo $month['rmonth'] .' / '. $month["quinzena"] ?>" class="row tabcontent">
            <div class="col text-center">
                <div class="worklog">
                <table class="worklog table" id="worklogs">
                    <thead>
                    <tr>
    		            <th class="right">User</th>
    		            <th class="right">Time Worked</th>
    		            <th class="right">Net Time Worked</th>
    		            <th class="right">Salary</th>
    		            <th class="right">ZDF</th>
                    </tr>
                    </thead>
                    <tbody>
<?php
                    if(!empty($workhours)) {
    		            foreach($workhours as $workhour) { 
                        $totalNetHours = 0;
?>
                        <script>
<?php
                            $param_un = $workhour['username'];
                            $sql = "SELECT DATE_FORMAT(ended_at, '%d.%m'), ROUND(SUM(TIME_TO_SEC(TIMEDIFF(ended_at,started_at)))/3600,1)
                                    FROM `intra_worklog` as w 
                                    JOIN intra_users as u on w.user_id=u.id 
                                    WHERE u.username = ? AND concat(month(ended_at), quinzena) = ? 
                                    GROUP BY day(ended_at)";
                            if ($stmt = $mysqli->prepare($sql)){
                                $stmt->bind_param("ss", $param_un, $month['rmonthQui']);
                                $stmt->execute();
                                $stmt->store_result();
                                $stmt->bind_result($wday, $whours);
                                while ($stmt->fetch()) {
                                    // Append the start and end times to the quinzenaDailyWork variable
                                    $quinzenaDailyWorks[] = array('wday' => $wday, 'whours' => $whours);
                                }
                                $stmt->close();
                            }
?>
                        
                            $(document).ready(function() {
                        
                                var xValues = [];
                                var yValues = [];
                                var yValuesModified = [];
                                var barColors = "#29b8e6";
                        
<?php
                                foreach($quinzenaDailyWorks as $quinzenaDailyWork) {
                                    echo "xValues.push('".$quinzenaDailyWork['wday']."');";
                                    echo "yValues.push('".$quinzenaDailyWork['whours']."');";
                                    if ($quinzenaDailyWork['whours'] >= 4) {
                                        echo "yValuesModified.push('".($quinzenaDailyWork['whours'] - 1)."');"; // Subtract 1 from whours
                                        $totalNetHours += ($quinzenaDailyWork['whours'] -1); // Summiere die Werte für netHours auf
                                    } else {
                                        echo "yValuesModified.push('".$quinzenaDailyWork['whours']."');"; // Keep the original value
                                        $totalNetHours += $quinzenaDailyWork['whours']; // Summiere die Werte für netHours auf
                                    }
                                }
                                $quinzenaDailyWorks = [];
?>
                        
                                new Chart("qdW-<?php echo $workhour['username'] . $month['rmonthQui']; ?>", {
                                    type: "bar",
                                    data: {
                                        labels: xValues,
                                        datasets: [{
                                            backgroundColor: barColors,
                                            data: yValues
                                        },{
                                            backgroundColor: '#ff0000', // Choose a color for the additional bar
                                            data: yValuesModified
                                        }]
                                    },
                                    options: {
                                        legend: {display: false},
                                        title: {
                                            display: true,
                                            text: "Hours worked / day"
                                        },
                                        responsive: true,
                                        responsiveAnimationDuration: 0,
                                        scales: {
                                            yAxes: [{
                                                ticks: {
                                                    beginAtZero: true,
                                                    callback: function(value, index, values) {
                                                        if(parseInt(value) >= 1000){
                                                            return 'h ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'");
                                                        } else {
                                                            return 'h ' + value;
                                                        }
                                                    }
                                                }
                                            }]
                                        }
                                    }
                                });
                            });
                        </script>
                            <tr><td class="right"><?php echo $workhour['username']; ?></td>
                                <td class="right"><?php echo $workhour['hours']; ?></td>
                                <td class="right"><?php echo $totalNetHours; ?></td>
                                <td class="right"><span class="unit">$</span><?php echo number_format(($totalNetHours * $workhour['sal']),0,",","'"); ?></td>
                                <td class="right">
                                  <button id="bankInfo_modal_button" class="bi bi-info-circle btn btn-white btn-sm sub_btn"></button>
                                        <div id="bankInfo_modal" class="modal">
                                            <div class="modal-content">
                                                <span class="close">&times;</span>
                                                <h6 class="title center mb-3">Details: <?php echo $workhour['username']; ?></h6>
                                                    <table class="table">
                                                	    <tr>
                                                		    <td style="width:20%">ID Nr: </td>
                                                		    <td class="request_desc mr-1"><?php echo $workhour['idnr']; ?></td>
                                                			<td class="request_desc">Bankaccount Nr: </td>
                                                			<td class="request_desc"><?php echo $workhour['ban']; ?></td>
                                                		</tr>
                                                		<tr>
                                                            <canvas id="qdW-<?php echo $workhour['username'] . $month['rmonthQui']; ?>" style="width:100%;max-width:700px;"></canvas>
                                              		</tr>
                                                    </table>
                                            </div>
                                        </div>
                               </td>
                            </tr>
<?php
                        } $workhours = [];
                    } 
?>                                    
                </tbody>
    	        </table>
    	        </div>
            </div>
        </div>    
    <?php   }
        } ?>
    </div>
        <div class="box">
	        <div class="row">
                <canvas id="monthlySalaries" style="width:100%;max-width:700px"></canvas>
            </div>            
        </div>
		<div class="box"><div class="mb-2"></div></div>
</div>

<?php include "footer.php"; ?>
<script src="js/header_role.js"></script>
<script>
var home = <?php echo json_encode($_SERVER['PHP_SELF']); ?>;
var userId = <?php echo json_encode($_SESSION["id"]); ?>;
var activeId = <?php echo json_encode($activeId); ?>;
</script>
<script src="js/wl.js"></script>

<script>
$(document).ready(function() {

var xValues = [];
var yValues = [];
var barColors = "#29b8e6";

<?php
  foreach($monthsals as $monthsal) {
    echo "xValues.push('".$monthsal['month']."');";
    echo "yValues.push('".$monthsal['mwage']."');";
  }
?>

new Chart("monthlySalaries", {
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
      text: "Salaries"
    },
      responsive: true,
      responsiveAnimationDuration: 0,
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true,
            callback: function(value, index, values) {
              if(parseInt(value) >= 1000){
                return '$ ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'");
              } else {
                return '$ ' + value;
              }
            }
          }
        }]
      }
  }
});
});
</script>
</body>
</html>