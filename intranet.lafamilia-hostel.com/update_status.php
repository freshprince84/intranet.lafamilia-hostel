<?php
require_once "session.php";
require_once "config.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    if (isset($_POST['approve'])) {

        // Update the status of the request in intra_requests table
        $sql_update = "UPDATE intra_requests 
                       SET status = ? 
                       WHERE request_id = ?";

        if ($stmt_update = $mysqli->prepare($sql_update)) {
            $stmt_update->bind_param("ss", $param_newStatus, $param_reqid);
            $param_newStatus = '3';
            $param_reqid = $_POST['request_id'];
            if ($stmt_update->execute()) {

               // Prepare a SELECT statement to get the intra_requests data
                $sql_select = "SELECT * 
                               FROM intra_requests 
                               WHERE request_id = ?";
                if ($stmt_select = $mysqli->prepare($sql_select)) {

                    // Bind variables to the prepared statement as parameters
                    $stmt_select->bind_param("s", $param_reqid);
                    $param_reqid = $_POST['request_id'];

                    // Attempt to execute the prepared statement
                    if ($stmt_select->execute()) {

                        // Get the result set from the SELECT statement
                        $result = $stmt_select->get_result();

                        // Fetch the data from the result set
                        if ($result->num_rows == 1) {
                            $row = $result->fetch_assoc();

                            // Store the data in variables
                            $param_request = $row['request'];
                            $param_role = $row['role'];
                        } else {
                            echo "No record found with the given request id.";
                            exit();
                        }
                    } else {
                        echo "1Oops! Something went wrong. Please try again later.";
                        exit();
                    }
                    // Close statement
                    $stmt_select->close();
                } else {
                    echo "2Oops! Something went wrong. Please try again later.";
                    exit();
                }
                // Insert the request data into intra_tasks table
                $sql_insert = "INSERT INTO intra_tasks (task, task_desc, user_id, role, period_type, due_date, status, request_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt_insert = $mysqli->prepare($sql_insert)) {
                    $stmt_insert->bind_param("ssiiisii", $param_task, $param_task_desc, $param_user_id, $param_role, $param_period_type, $param_due_date, $param_status, $param_request_id);
 
                    $param_task = $row['request'];
                    $param_task_desc = $row['request_desc'];
                    $param_user_id = $row['responsible'];
                    $param_role = $row['role'];
                    $param_status = 1;
                    $param_due_date = $row['due_date'];
                    $param_period_type = 1;
                    $param_request_id = $param_reqid;
                    $param_task_id = $row['task_id'];
                    
                    if ($param_task_id != 999999999) {
                        if ($stmt_insert->execute()) {
                            // Success
                        } else {
                            echo "3Oops! Something went wrong. Please try again later.";
                        }
                        $stmt_insert->close();
                    }
                } else {
                    echo "4Oops! Something went wrong. Please try again later.";
                    echo $row['request'];
                    echo $row['responsible'];
                    
                }
            } else {
                echo "5Oops! Something went wrong. Please try again later.";
            }
            $stmt_update->close();
        } else {
            echo "6Oops! Something went wrong. Please try again later.";
        }
    } elseif (isset($_POST['approve_back']) || isset($_POST['improve_back'])) {

        $sql = "UPDATE intra_requests SET status = ? WHERE request_id = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("si", $param_newStatus,  $param_reqid);
            
            $param_newStatus = '2';
            $param_reqid = $_POST['request_id'];

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
    } elseif (isset($_POST['improve'])) {

        $sql = "UPDATE intra_requests SET status = ? WHERE request_id = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("si", $param_newStatus,  $param_reqid);
            
            $param_newStatus = '8';
            $param_reqid = $_POST['request_id'];

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
    } elseif (isset($_POST['deny'])) {

        $sql = "UPDATE intra_requests SET status = ? WHERE request_id = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("si", $param_newStatus,  $param_reqid);
            
            $param_newStatus = '7';
            $param_reqid = $_POST['request_id'];

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
// Close connection
$mysqli->close(); 	
}

header("Location: welcome.php");
exit();

?>