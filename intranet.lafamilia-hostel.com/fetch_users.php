<?php
require_once "session.php";
require_once "config.php";

if (isset($_POST['userid']) && is_numeric($_POST['userid'])) {
    $user_id = $_POST['userid'];
    
    $sql = "SELECT u.username, u.idnr, u.ban, u.salary
            FROM intra_users as u
            LEFT JOIN intra_roles as r on u.role = r.role_id
            WHERE u.id = ?";
    
    if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("i", $user_id);
    		$stmt->execute();
    		$stmt->store_result();
    		$stmt->bind_result($username, $idnr, $ban, $salary);
    		$stmt->fetch();
    		$stmt->close();
    	}
    
    $sql = "SELECT role_id, role_desc
            FROM intra_roles
            WHERE role_id >= " . $_SESSION['role'];
    
    if($stmt = $mysqli->prepare($sql)){
    		$stmt->execute();
    		$stmt->store_result();
    		$stmt->bind_result($lirole_id, $lirole_desc);
    		while ($stmt->fetch()) {
    	        // Append the start and end times to the $users_roles variable
    	        $liusers_roles[] = array('lirole_id' => $lirole_id, 'lirole_desc' => $lirole_desc);
    		}
    		$stmt->close();
    	}
    
    $sql = "SELECT ur.role_id, r.role_desc 
            FROM intra_users_roles as ur
            LEFT JOIN intra_roles as r on ur.role_id = r.role_id
            WHERE ur.user_id = ?";
    
    if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("i", $user_id);
    		$stmt->execute();
    		$stmt->store_result();
    		$stmt->bind_result($role_id, $role_desc);
    		while ($stmt->fetch()) {
    	        // Append the start and end times to the $users_roles variable
    	        $users_roles[] = array('role_id' => $role_id, 'role_desc' => $role_desc);
    		}
    		$stmt->close();
    }
?>
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
                <tr>
                    <td class="align-middle">Roles: </td>
                    <td class="text-left">
<?php
                    if (!empty($liusers_roles)) {
                        foreach ($liusers_roles as $liusers_role) {
                            echo '<label><input type="checkbox" name="roles[]" value="' .  $liusers_role['lirole_id'] . '"';
                            if (in_array($liusers_role['lirole_id'], array_column($users_roles, 'role_id'))) {
                                echo ' checked="checked"';
                            }
                            echo '>' . $liusers_role['lirole_desc'] . '</label><br>';
                        }
                    }
?>
                   </td>
                </tr>
                <tr>
                    <td class="align-middle form_desc">Salary: </td>
                    <td><input type="hidden" name="id" value="<?php echo $user_id ?>">
                        <input type="text" name="salary" value="<?php echo $salary; ?>" size="10"><span class="unit">$/h</span></td>
                </tr>
            </table>
        </div>
        <input type="submit" name="save" class="btn-primary" value="Save">
    </form>
    <div style="display: inline-flex; align-items: center;">
        <form action="reset-password.php" method="post">
            <input type="hidden" name="id" value="<?php echo $user_id; ?>">
            <input type="submit" class="btn-warning" name="resetUserPw" value="Reset User Password">
        </form>
	    <button id="del_user_modal_button_<?php echo $user_id ?>" name="deleteUser" class="bi ml-1 bi button btn btn-danger mod delete-button" value="deleteUser"><i class="bi bi-trash"></i></button>
	</div>
	<div id="del_user_modal_<?php echo $user_id ?>" class="modal">
		<div class="modal-content">
			<span class="close">&times;</span>
			<h6 class="title mb-3">Really delete user?</h6>
			<div class="confirm-delete-user">
                <form action="" method="post">
                    <div class="table-responsive">
                        <table class="requests table">
                            <tr>
                                <td class="align-middle"><input type="hidden" name="id" value="<?php echo $user_id ?>">
                                    <input type="submit" name="delete" class="btn-danger" value="Yes, delete"></td>
                            </tr>
                
                        </table>
                    </div>
                </form>				                        			    
			</div>
		</div>
	</div>

<?php    
} else {
    if (isset($_POST["arch"])) {
        $act = 0;
    } else {
        $act = 1;
    }
    unset($_POST["arch"]);
    $sql = "SELECT id, username, r.role_id, r.role_desc 
            FROM intra_users as u
            LEFT JOIN intra_roles as r on u.role = r.role_id
            WHERE active = " . $act . 
          " ORDER BY u.username";
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
}
?>