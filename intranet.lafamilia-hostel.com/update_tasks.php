<?php

require_once "config.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $task_id = $_POST["task_id"];
    if (isset($_POST["update_tsk"])) {
        $task = $_POST["task"];
        if (!isset($_POST["role"])) {
            $task_desc = $_POST["task_desc"];
            $resp = $_POST["resp"];
            $due_date = $_POST["due_date"];
            $timestamp = strtotime($due_date);
            $due_date = date("Y-m-d H:i:s", $timestamp);
    
            $sql = "UPDATE intra_tasks 
                    SET task = ?, task_desc = ?, user_id = ?,  due_date = ?
                    WHERE task_id = ? ";
    
            if($stmt = $mysqli->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ssssi", $task, $task_desc, $resp, $due_date, $task_id);
            
                // Attempt to execute the prepared statement
                $stmt->execute();
               // Close statement
                $stmt->close();
            }
        } elseif (isset($_POST["role"])) {
            $role = $_POST["role"];
    
            $sql = "UPDATE intra_tasks 
                    SET task = ?, role = ?
                    WHERE task_id = ? ";
    
            if($stmt = $mysqli->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("sii", $task, $role, $task_id);
            
                // Attempt to execute the prepared statement
                $stmt->execute();
               // Close statement
                $stmt->close();
            }            
        }
    } elseif (isset($_POST["delete_tsk"])) {
        $sql = "DELETE FROM intra_tasks 
                WHERE task_id = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("i", $task_id);
        
            // Attempt to execute the prepared statement
            $stmt->execute();
           // Close statement
            $stmt->close();
        }
    } else {
        $user_id = $_POST["user_id"];
        if (isset($_POST["open"])) {
            $status_id = $_POST["open"];
    
        } elseif (isset($_POST["inProgress"])) {
            $status_id = $_POST["inProgress"];
    
        } elseif (isset($_POST["qm"])) {
            $status_id = $_POST["qm"];
    
        } elseif (isset($_POST["done"])) {
            $status_id = $_POST["done"];
        }
        
        $sql = "UPDATE intra_tasks 
                SET status = ?
                WHERE task_id = ? ";
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ii", $status_id, $task_id);
        
            // Attempt to execute the prepared statement
            $stmt->execute();
           // Close statement
            $stmt->close();
        }
        $sql = "INSERT INTO intra_tasklog (user_id, task_id, status)
                VALUES (?, ?, ?)";
        if($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("iii", $user_id, $task_id, $status_id);
        
            // Attempt to execute the prepared statement
            $stmt->execute();
           // Close statement
            $stmt->close();
        }   
    
    }
/*
    $sql = "INSERT INTO intra_worklog_tasks
            (worklog_id, task_id, status_id)
            SET worklog_id, task_id, status_id = ?
            JOIN intra_
            WHERE task_id = ? ";
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ii", $status_id, $task_id);
        
            // Attempt to execute the prepared statement
            $stmt->execute();
           // Close statement
            $stmt->close();
            
            
select id, task_id, status, w.user_id from intra_worklog as w
right join intra_tasks as t on w.user_id = t.user_id
where t.user_id = 3 AND working = 1            
            
            
            
        }
*/        
        

// Close connection
$mysqli->close();

header('Location: worktracker.php');
exit;
}

?>
