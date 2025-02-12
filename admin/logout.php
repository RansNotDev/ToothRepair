<?php
session_start();
include_once('../database/db_connection.php'); 
session_destroy();
// Redirect to login page
header("Location: admin_login.php");
exit();
?>