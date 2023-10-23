<?php
require_once "config.php";

// Define variables and initialize with empty values
$userid = $_POST['userid'];
$continue = false;

$sql = "SELECT id 
        FROM `intra_users_roles` 
        WHERE `user_id` = ?";

    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("s", $userid);
    	$stmt->execute();
    	$stmt->store_result();
        
        if($stmt->num_rows > 1){
            $continue = true;
        }
        $stmt->close();
    }
    
if($continue == true){    

    $sql = "SELECT ur.role_id, r.role_desc 
            FROM intra_users_roles as ur 
            JOIN intra_roles as r 
            ON ur.role_id=r.role_id 
            WHERE ur.user_id = ? and r.role_id != 99
            ORDER BY ur.role_id";
    
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $userid);
        	$stmt->execute();
        	$stmt->store_result();
        	$stmt->bind_result($roleid, $role);
        	while ($stmt->fetch()) {
    
                $roles[] = array('roleid' => $roleid, 'role' => $role);
        	}
        	$stmt->close();
        }
    
    if (!empty($roles)) {
        if ($_POST['login'] == 1) {
            echo '<label class="labels">Role</label>';
            echo '<select class="form-control form-control-lg" name="ses_role" required/>';
                foreach($roles as $role) {
                    echo '<option value=' . $role['roleid'] . '>' . $role['role'] . '</option>';
                }
            echo '</select>';
        } else {
            echo '<select class="form-control" name="ses_role" required/>';
            echo '<option value="Role">Role</option>';
                foreach($roles as $role) {
                    echo '<option value=' . $role['roleid'] . '>' . $role['role'] . '</option>';
                }
            echo '</select>';
        }
    } 
}
?>
