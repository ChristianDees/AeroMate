<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php');
require_once('validate_session.php');

if (isset($_POST['id'])){

    // Get info for updated user vals
    $id = isset($_POST['id']) ? $_POST['id'] : "";
    $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : "";
    $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : "";
    $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : "";
    $passportNumber = isset($_POST['passportNumber']) ? $_POST['passportNumber'] : "";
    $email = isset($_POST['email']) ? $_POST['email'] : "";
    $phone = isset($_POST['phone']) ? $_POST['phone'] : "";
    $frequentFlyer = isset($_POST['frequentFlyer']) ? 1 : 0;

    $query = "UPDATE Passenger SET id='$id', firstName='$firstName', lastName='$lastName', nationality='$nationality', passportNumber='$passportNumber', email='$email', phone='$phone', frequentFlyer='$frequentFlyer' WHERE id = '$id'";
    
    // Execute query and alert outcome 
    if (mysqli_query($conn, $query)) {
        $msg = "Successfully updated profile!";
        echo "<script>
            alert(" . json_encode($msg) . ");
            window.location.href = 'home.php?id=" . $id . "';
        </script>";
        exit; 
    } else {
        $msg = "Error updating profile: " . $stmt->error;
        echo "<script>alert(" . json_encode($msg) . ");</script>";
    }
}
else {
  echo "No passenger id received on request at update profile";
  die();
}

?>