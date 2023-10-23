<?php

require_once "session.php";                          
require_once "config.php";
include "head.php";

$title="Who is working?";

echo "<title>" . $title . "</title>";
echo "</head>";
require_once "header.php";
?>
	<div class="win win-start">
		<div class="box">
			<div class="row">
				<div class="col text-center">
                                        <h6 class="title mb-3">Now</h6>
					<div class="worklog">
					<table class="table" id="workings">
					    <thead>
						<tr>
							<th>Name</th>
							<th>Start Time</th>
							<th>Options</th>
						</tr>
						</thead>
						<tbody id="workings-now-container">
					    </tbody>
					</table>
					</div>
				</div>
			</div>
		</div>
		<div id="workingsSummarysModalChange" class="box">
			<div class="row">
				<div class="col">
					<div class="worklog mb-1">
					<input type="date" id="date-select" class="calendar" name="date" min="2023-02-01" max="<?php echo date('Y-m-d') ?>" value="">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col text-center">
					<div class="worklog">
					<table class="table" id="workingsSummarys">
					    <thead>
						<tr>
							<th>Name</th>
							<th>Worked</th>
							<th>Details</th>
						</tr>
						</thead>
						<tbody id="workings-container">
					    </tbody>
					</table>
					</div>
				</div>
			</div>
		</div>
		<div class="box">
			<div class="row">
                <canvas id="workTimeToday" style="width:100%;max-width:500px"></canvas>
            </div>            
        </div>    		
    </div>

<?php include "footer.php"; ?>
<script src="js/header_role.js"></script>
<script>
var home = <?php echo json_encode($_SERVER['PHP_SELF']); ?>;
var userId = <?php echo json_encode($_SESSION["id"]); ?>;
</script>
<script src="js/working.js"></script>

<!--
<script>
var xValues = [];
var yValues = [];
var barColors = "#29b8e6";

<?php
/*
  foreach($workedhours as $workedhour) {
    echo "xValues.push('".$workedhour['month']."');";
    echo "yValues.push(".$workedhour['hours'].");";
  }
  */
?>

new Chart("workTimeToday", {
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
-->
</body>
</html>