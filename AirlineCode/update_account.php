<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php');

// Validate session only if updating account
if (isset($_POST['id']) && !empty($_POST['id'])) require_once('validate_session.php');

// Check for updating account
$id = $_POST['id'] ?? null;
$firstName = $_POST['firstName'] ?? "";
$lastName = $_POST['lastName'] ?? "";
$email = $_POST['email'] ?? "";
$password = $_POST['password'] ?? ""; 
$confirmPassword = $_POST['confirmPassword'] ?? "";

// Passenger specific 
$passportNumber = $_POST['passportNumber'] ?? "";
$phone = $_POST['phone'] ?? "";
$frequentFlyer = isset($_POST['frequentFlyer']) ? 1 : 0; 

// Crewmember specific 
$role = $_POST['role'] ?? ""; 
$airlineAffiliation = $_POST['airlineAffiliation'] ?? ""; 

// Update user account
if ($id) {
    // Update user details 
    $queryUsers = "UPDATE users SET FirstName = ?, LastName = ?, Email = ?, Password = ? WHERE id = ?";
    $stmt = $conn->prepare($queryUsers);
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $password, $id);

    if (!$stmt->execute()) {
        $msg = "Error updating user details: " . $stmt->error;
        echo "<script>alert(" . json_encode($msg) . ");</script>";
        exit;
    }


    // Update passenger specific details
    if ($_SESSION['userType'] == 'passenger') {
        $queryPassenger = "UPDATE passenger SET PassportNumber = ?, Phone = ?, FrequentFlyer = ? WHERE userID = ?";
        $stmt = $conn->prepare($queryPassenger);
        $stmt->bind_param("sssi", $passportNumber, $phone, $frequentFlyer, $id);

        if (!$stmt->execute()) {
            $msg = "Error updating passenger details: " . $stmt->error;
            echo "<script>alert(" . json_encode($msg) . ");</script>";
            exit;
        }

    // Update crewmember specific details
    } elseif ($_SESSION['userType'] == 'crewmember') {
        $queryCrewmember = "UPDATE crewmember SET Role = ?, AirlineAffiliation = ? WHERE userID = ?";
        $stmt = $conn->prepare($queryCrewmember);
        $stmt->bind_param("ssi", $role, $airlineAffiliation, $id);
        if (!$stmt->execute()) {
            $msg = "Error updating crewmember details: " . $stmt->error;
            echo "<script>alert(" . json_encode($msg) . ");</script>";
            exit;
        }

    // Update admin specific details
    } elseif ($_SESSION['userType'] == 'admin') {
        $queryAdmin = "UPDATE users SET FirstName = ?, LastName = ?, Email = ? WHERE id = ?";
        $stmt = $conn->prepare($queryAdmin);
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $id);
        if (!$stmt->execute()) {
            $msg = "Error updating admin details: " . $stmt->error;
            echo "<script>alert(" . json_encode($msg) . ");</script>";
            exit;
        } 
    }else {
        echo "<script>alert('Unknown user type.');</script>";
        exit;
    }
    $msg = "Account updated successfully!";
    $redirectUrl = "dashboard.php?id=" . $id;

// Create new user 
} else {
    $userType = $_POST['type'] ?? "";

    // Check for duplicate email
    $checkQuery = "SELECT ID FROM users WHERE Email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $msg = "Email already exists. Please use a different email.";
        echo "<script>alert(" . json_encode($msg) . "); window.location.href='account_interface.php?type=$userType';</script>";
        exit;
    }

    // Insert new user details
    $queryUsers = "INSERT INTO users (FirstName, LastName, Email, Password, Type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($queryUsers);
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $userType);

    if (!$stmt->execute()) {
        $msg = "Error inserting user: " . $stmt->error;
        echo "<script>alert(" . json_encode($msg) . ");</script>";
        exit;
    }
    // Get new user ID
    $userId = mysqli_insert_id($conn); 
    
    // Insert passenger specific details
    if ($userType == 'passenger') {
        $queryPassenger = "INSERT INTO passenger (userID, PassportNumber, Phone, FrequentFlyer) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($queryPassenger);
        $stmt->bind_param("isss", $userId, $passportNumber, $phone, $frequentFlyer);

        if (!$stmt->execute()) {
            $msg = "Error creating passenger details: " . $stmt->error;
            echo "<script>alert(" . json_encode($msg) . ");</script>";
            exit;
        }

    // Insert crewmember specific details
    } elseif ($userType == 'crewmember') {
        $queryCrewmember = "INSERT INTO crewmember (userID, Role, AirlineAffiliation) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($queryCrewmember);
        $stmt->bind_param("iss", $userId, $role, $airlineAffiliation);
        if (!$stmt->execute()) {
            $msg = "Error creating crew member details: " . $stmt->error;
            echo "<script>alert(" . json_encode($msg) . ");</script>";
            exit;
        }
    } else {
        echo "<script>alert('Unknown user type.');</script>";
        exit;
    }
    
    // Update vals
    $msg = "Account created successfully!";
    $_SESSION['loggedIn'] = true;
    $_SESSION['user'] = $userId;
    $_SESSION['userType'] = $userType;
    $redirectUrl = "dashboard.php?id=" . $userId;
}

// Display msg and leave
echo "<script>
    alert(" . json_encode($msg) . ");
    window.location.href = '$redirectUrl';
    </script>";
exit;
?>
