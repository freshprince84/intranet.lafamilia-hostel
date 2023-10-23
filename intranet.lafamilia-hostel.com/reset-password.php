<?php
// Initialize the session
session_start();
 
 
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
 
$userid = $username = $password = "";
$username_err = $password_err = "";

if (isset($_GET['password_reset'])) {
    // Password has been reset. Show success message.
    $login_not = 'Password updated successfully.';
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["resetPw"])) { 
        // Validate new password
        if(empty(trim($_POST["new_password"]))){
            $new_password_err = "Please enter the new password.";     
        } elseif(strlen(trim($_POST["new_password"])) < 6){
            $new_password_err = "Password must have atleast 6 characters.";
        } else{
            $new_password = trim($_POST["new_password"]);
        }
        
        // Validate confirm password
        if(empty(trim($_POST["confirm_password"]))){
            $confirm_password_err = "Please confirm the password.";
        } else{
            $confirm_password = trim($_POST["confirm_password"]);
            if(empty($new_password_err) && ($new_password != $confirm_password)){
                $confirm_password_err = "Password did not match.";
            }
        }
            
        // Check input errors before updating the database
        if(empty($new_password_err) && empty($confirm_password_err)){
    
            // Prepare an update statement
            $sql_update = "UPDATE intra_users SET password = ? WHERE id = ?";
            
            if ($stmt_update = $mysqli->prepare($sql_update)) {
                // Bind variables to the prepared statement as parameters
                $stmt_update->bind_param("si", $param_password, $param_id);
                
                // Set parameters
                $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                if (isset($_POST["userid"])) {
                    $param_id = $_POST["userid"];
    error_log("Admin: " . $param_id);
                } else {
                    $param_id = $_SESSION["id"];
    error_log("User: " . $param_id);
                }
                if (isset($_SESSION["id"])) {
                    $location = "location: settings.php?password_reset=1";
                    
                } else {
                    $location = "location: login.php?password_reset=1";
                }
                            
                    // Attempt to execute the prepared statement
                    if ($stmt_update->execute()) {
                        
                        header($location);
                        exit();
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
    
                // Close statement
                $stmt_update->close();
            }
        }
        
        // Close connection
    $mysqli->close(); 	
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet"
        href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Reset Password</title>
</head>
<body>
    <div class="topbar topbar-fixed">
		<img class="head-logo" src="https://www.lafamilia-hostel.com/wp/wp-content/uploads/2022/09/hostel1-01-scaled-e1662067915888.jpg" alt="" width="100">
    </div>        
<div class="win">
	<div class="box">
<?php 
        if(!empty($login_not)) {
            echo '<div class="alert alert-success">' . $login_not . '</div>';
        }        
?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="row">
                    <div class="col text-center">
                    <h2>Reset Password</h2>
                    <p>Please fill out this form to reset your password.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="labels">New Password</label>
                    <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                    <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                </div>
                <div class="form-group">
                    <label class="labels">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Submit" name="resetPw">
<?php
                    if (isset($_POST["resetUserPw"])) {
?>
                        <input type="hidden" name="userid" value="<?php echo $_POST['id']?>">
<?php
                    }
?>
                    <a class="btn btn-link ml-2" href="settings.php">Back</a>
                </div>
            </form>
    </div>
</div>
</body>
</html>