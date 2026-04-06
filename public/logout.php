<?php
session_start();  // Start the session

// Destroy the session to log the user out
session_unset();   // Unset all session variables
session_destroy(); // Destroy the session

// Redirect the user to the homepage or login page after logout
include "../helper/html_helper.php";
redirect("/public/home.php");
exit();  // Ensure no further code is executed
?>
