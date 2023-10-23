<?php

require_once "config.php";



if($_SERVER["REQUEST_METHOD"] === "POST"){
 
    if (isset($_POST["update_req"])) {
        $req_id = $_POST["request_id"];
        $req = $_POST["request"];
        $req_desc = $_POST["request_desc"];
        $requestor2 = $_POST["requestor2"];
        $resp = $_POST["resp"];
        $due_date = $_POST["due_date"];
        $timestamp = strtotime($due_date);
        $due_date = date("Y-m-d H:i:s", $timestamp);

        $sql = "UPDATE intra_requests 
                SET request = ?, request_desc = ?, requested_by = ?, responsible = ?, due_date = ?
                WHERE request_id = ? ";

            if($stmt = $mysqli->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("sssssi", $req, $req_desc, $requestor2, $resp, $due_date, $req_id);
            
                // Attempt to execute the prepared statement
                $stmt->execute();
               // Close statement
                $stmt->close();
            }
    }
// Close connection
$mysqli->close();

header('Location: welcome.php');
exit;
}


?>
