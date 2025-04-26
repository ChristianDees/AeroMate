<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php'); 
require_once('validate_session.php');

if (isset($_GET['flightID']) && isset($_GET['userID'])) {
    // Get flight id and user id
    $flightID = $_GET['flightID'];
    $userID = $_GET['userID'];

    // Delete a booked flight associated with user
    $sql = "DELETE FROM booked WHERE flightID = ? AND passengerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $flightID, $userID); 
    $stmt->execute();
    $stmt->close();

    // Redirect back to upcoming flights
    header("Location: upcoming_flights.php?id=" . urlencode($userID) . "&flightID=" . urlencode($flightID));

} else die();
?>


