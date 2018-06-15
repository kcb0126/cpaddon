<?php
// You may copy this PHP section to the top of file which needs to access after login.
session_start(); // Use session variable on this page. This function must put on the top of page.
session_destroy();

header("location:index.php");

?>

