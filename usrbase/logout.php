<?php
session_start();
session_destroy();
header("Location: entryvault.php");
exit();
?>