<?php
require_once "session.php";

// Check if the 'role' parameter is present in the POST data
if (isset($_POST['role']) && $_POST['role'] !== '') {
    // Set the 'role' session variable to the selected value
    $_SESSION['role'] = $_POST['role'];
    // Redirect to the 'settings.php' page
} else {
    // Log the POST data to the PHP error log
    error_log(print_r($_POST, true));
}

?>
