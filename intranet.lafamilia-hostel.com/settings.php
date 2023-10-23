<?php
require_once "session.php";                          
require_once "config.php";
require_once "head.php";
$title="Settings";
echo "<title>" . $title . "</title>";
echo "</head>";
require_once "header.php";
$sql = "SELECT id, username, r.role_id, r.role_desc 
        FROM intra_users as u
        LEFT JOIN intra_roles as r on u.role = r.role_id
        WHERE active > 0
        ORDER BY u.username";
if($stmt = $mysqli->prepare($sql)){
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($user_id, $username, $role_id, $role_desc);
	while ($stmt->fetch()) {
        // Append the start and end times to the $worklogs variable
        $wages[] = array('user_id' => $user_id, 'username' => $username, 'role_desc' => $role_desc);
	}
	$stmt->close();
}
$sql = "SELECT username, idnr, ban 
        FROM intra_users 
        WHERE username = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("s", $_SESSION['username']);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($un, $idnr, $ban);
	$stmt->fetch();
	$stmt->close();
}
if($_SERVER["REQUEST_METHOD"] === "POST"){
    if (isset($_POST["save"])) {
        
        $salary = $_POST["salary"];
        $id = $_POST["id"];
        
        $sql = "UPDATE intra_users 
                SET salary= ? 
                WHERE id= ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ss", $salary, $id);
        
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
        
         // Delete user's unchecked roles
        $roles = isset($_POST["roles"]) ? $_POST["roles"] : [];
        $sql = "DELETE FROM intra_users_roles 
                WHERE user_id = ? AND role_id NOT IN (".implode(",", array_map("intval", $roles)).")";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $id);
            if (!$stmt->execute()) {
                echo "Oops! Something went wrong. Please try again later.";
                exit();
            }
            $stmt->close();
        }
        if (isset($_POST["roles"]) && is_array($_POST["roles"])) {
            foreach ($_POST["roles"] as $role_id) {
                $sql = "INSERT IGNORE INTO intra_users_roles (user_id, role_id) VALUES (?, ?)";
                if ($stmt = $mysqli->prepare($sql)) {
                    $stmt->bind_param("ii", $id, $role_id);
                    if (!$stmt->execute()) {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    $stmt->close();
                }
            }
        }
        
    }   else if (isset($_POST["delete"])) {

        $id = $_POST["id"];
        $sql = "UPDATE intra_users 
                SET active = 0 
                WHERE id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $id);
            if(!$stmt->execute()) {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
        
    }   else if (isset($_POST["save_ud"])) {

        $idnr = $_POST["idnr"];
        $ban = $_POST["banr"];
        $ban = str_replace(' ','',$ban);
        $username = $_POST["username"];
        $un = $_POST["un"];

        
        $sql = "UPDATE intra_users 
                SET username = ?, idnr = ?,  ban= ? 
                WHERE username= ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $un, $idnr, $ban, $username);
        
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // store result
                $_SESSION["username"] = $un;
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
?>
	<div class="win win-start">
		<div class="box">
            <div class="tab">
                <button id="set_tab_usr_but" class="set_tab tablinks" onclick="set_tab_switch(event, 'set_tab_usr')">
                    Me                            
                </button>
<?php
        $activeId = "set_tab_usr_but";
        if($_SESSION["role"] <= 1) {
?>
                <button id="set_tab_adm_but" class="set_tab tablinks" onclick="set_tab_switch(event, 'set_tab_adm')">
                    Users    
                </button>
            </div>  
<?php
        }
?>
		        <div id="set_tab_usr" class="row justify-content-center tabcontent">
                 <form action="" method="post">
                    <div class="table-responsive">
                        <table class="requests table">
                            <tr>
                                <td class="align-middle form_desc">Name: </td>
                                <td><input type="text" name="username" value="<?php echo $username; ?>" size="10"></td>
                            </tr>
                	        <tr>
                	        	<td class="align-middle form_desc">ID Nr.: </td>
                	        	<td><input type="text" name="idnr" value="<?php echo $idnr; ?>" size="20">
                	        </tr>
                	        <tr>
                	        	<td class="align-middle form_desc">Bankaccount Number: </td>
                	        	<td><input type="text" name="banr" value="<?php echo substr($ban, 0, 4) . ' ' . substr($ban, 4, 4) . ' ' . substr($ban, 8, 4) . ' ' . substr($ban, 12, 4); ?>" size="19">
                	        </tr>
                        </table>
                    </div>
                    <input type="submit" name="save" class="btn-primary" value="Save">
                </form>
               </div>
		        <div id="set_tab_adm" class="row justify-content-center tabcontent">
                    <div class="row pt-3 pb-4">
                        <div class="col-3 align-self-center">
                            <label>Edit User</label>
                        </div>
                        <div class="col-7">
                            <select id="user-dropdown" class="form-control">
                                <option value="Select user" selected>Select User</option>
                                <?php
                                if (!empty($wages)) {
                                    foreach ($wages as $wage) {
                                        echo '<option value="' . $wage['user_id'] . '">' . $wage['username'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-2 text-end">
                            <button onclick="archiveUsers()" class="btn sub_btn"><i class="bi bi-archive"></i></button>
                        </div>
                    </div>
                    <div id="view-content" class="col-md-12" style="display: none;">
    			        <div class="user-edit pb-2">
			                
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
var activeId = <?php echo json_encode($activeId); ?>;
</script>
<script src="js/settings.js"></script>
</body>
</html>