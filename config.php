<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
$host = "dbserver2.utep.edu"; 
$db = "s25_nvr_team11"; 
$username = "cdees";        // Change to YOUR username
$password = "Deadpool11";   // Change to YOUR password
$conn = new mysqli($host, $username, $password, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>
