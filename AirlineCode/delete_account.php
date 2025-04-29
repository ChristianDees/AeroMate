<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php');
require_once('validate_session.php');

// Check if requested id is the logged in userID
if (!isset($_GET['id']) || $_GET['id'] != $_SESSION['user']) {die("Access denied.");}
$id = $_GET['id'];

// If user is a passenger
if ($_SESSION['userType'] === 'passenger') {
    // Get passengerID
    $stmt = $conn->prepare("SELECT id FROM passenger WHERE userID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $passenger = $result->fetch_assoc();
    
    if ($passenger) {
        // Delete from booked
        $stmt = $conn->prepare("DELETE FROM booked WHERE passengerID = ?");
        $stmt->bind_param("i", $passenger['id']);
        $stmt->execute();
        
        // Delete from passenger
        $stmt = $conn->prepare("DELETE FROM passenger WHERE userID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
    
// If user is a crewmember
if ($_SESSION['userType'] === 'crewmember') {
    // Get crewmemberID 
    $stmt = $conn->prepare("SELECT id FROM crewmember WHERE userID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $crewmember = $result->fetch_assoc();
    
    if ($crewmember) {
        // Delete from has
        $stmt = $conn->prepare("DELETE FROM has WHERE id = ?");
        $stmt->bind_param("i", $crewmember['id']);
        $stmt->execute();
        
        // Delete from crewmember
        $stmt = $conn->prepare("DELETE FROM crewmember WHERE userID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Delete user from users 
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
session_destroy();
header("Location: index.php");
exit;