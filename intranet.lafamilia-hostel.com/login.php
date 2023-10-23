<?php
// Initialize the session
session_start();


// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$userid = $username = $password = "";
$username_err = $password_err = $login_err = "";

if (isset($_GET['password_reset'])) {
    // Password has been reset. Show success message.
//    $login_not = "Your password has been reset. Please log in with your new password.";
}

$sql = "SELECT id, username FROM intra_users where active > 0";

if($stmt = $mysqli->prepare($sql)){
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // Store result
        $stmt->store_result();
        // Bind result variables
	    $stmt->bind_result($userid, $username);
            while ($stmt->fetch()) {
                // Append the start and end times to the $worklogs variable
                $users[] = array('userid' => $userid, 'username' => $username);
            }
	        $stmt->close();
    }
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $userid = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        
        $sql = "SELECT id, username, password, role, salary FROM intra_users WHERE id = ?";
        
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_userid);
            
            // Set parameters
            $param_userid = $userid;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if($stmt->num_rows == 1){                    
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password, $role, $sal);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            if (isset($_POST["ses_role"])) {
                                $_SESSION["role"] = $_POST["ses_role"];
                            } else {
                                $_SESSION["role"] = 99;
                            }
                            
                            $_SESSION["salary"] = $sal;
                            
                            // Redirect user to welcome page
                            header("location: welcome.php");
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
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
    <title>Login</title>
</head>
<body style="overflow-x: hidden; overflow-y: hidden;">
    <div class="win">
    <div class="box box-start">
        
<?php 
        if (!empty($login_not)) {
            echo '<div class="alert alert-success">' . $login_not . '</div>';
        } elseif (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
            
        }
?>

        <form class="formBlockDisplay" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
            <div class="login row">
                <div class="col text-center">
                    <img class="mb-1" src="https://www.lafamilia-hostel.com/wp/wp-content/uploads/2022/09/hostel1-01-scaled-e1662067915888.jpg" alt="" width="160">
                    <h5 class="title mb-3">¡Bienvenido de vuelta!</h5>
                </div>
            </div>
            <div class="form-group">
                <label class="labels">Username</label>
				    <select id="user-DD" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?> form-control-lg" value="<?php echo $username; ?>" name="username" onchange="roleDD(this)" required/>
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
				        <option value="" selected></option>
<?php            if(!empty($users)) {
                    foreach($users as $user) {
?>
				        <option value=<?php echo $user['userid'] . '>' . $user['username']; ?></option>
<?php
                    }
			      }
			 ?>     
				    </select>
            </div>
            <div id="role-DD" class="form-group" style="display:none;">
                    
			</div>
            <div class="form-group">
                <label class="labels">Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?> form-control-lg">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <a href="register.php" class="btn btn-secondary ">Registrar cuenta</a>
        </form>
        <div class="row">
                <div class="col text-center">
                    <span style="color: #2295F3; display: block; margin-top: 14px; font-weight: bold;">¿Has olvidado tu contraseña? -> Avisale al Administrador</span>
                </div>
            </div>
    </div>
    </div>
    
<script>
function roleDD(un) {

    var un = un.value;

    $.ajax({
        url: "fetch_roles.php",
        type: "POST",
        data: {
            userid: un,
            login: 1
        },
        success: function(data) {
            $('#role-DD').html(data);
        }
    });
}
</script>

    
</body>
</html>