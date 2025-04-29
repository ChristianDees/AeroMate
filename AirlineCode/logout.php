<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
$_SESSION['loggedIn'] = false;
session_destroy(); 
header("Location: index.php");
exit();