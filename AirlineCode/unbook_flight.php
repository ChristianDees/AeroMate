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
    $flightID = $_GET['flightID'];
    $userID = $_GET['userID'];

    // Get passengerID
    $getPID = "SELECT id FROM Passenger WHERE userID = ?";
    $stmtPID = $conn->prepare($getPID);
    $stmtPID->bind_param('s', $userID);
    $stmtPID->execute();
    $resultPID = $stmtPID->get_result();
    
    // Begin unbooking
    if ($row = mysqli_fetch_array($resultPID)) {
        $passengerID = $row['id'];
        $stmtPID->close();

        // Delete from booked
        $sql = "DELETE FROM Booked WHERE flightID = ? AND passengerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $flightID, $passengerID);
        $stmt->execute();
        $stmt->close();

        // Redirect back to upcoming_flights
        header("Location: upcoming_flights.php?id=" . urlencode($userID) . "&flightID=" . urlencode($flightID));
        exit();

    } else {
        $stmtPID->close();
        die("Passenger not found.");
    }
} else die("Invalid request.");
?>
