<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once("config.php");
$_SESSION['logged_in'] = false;

if (!empty($_POST) && isset($_POST['Submit'])) {
  // Get email entered
  $email = isset($_POST['email']) ? $_POST['email'] : "";
  $queryUser = "SELECT * FROM Passenger WHERE email = ?";
  $stmt = $conn->prepare($queryUser);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $resultUser = $stmt->get_result();
  // Check if user exists
  if ($resultUser->num_rows > 0) {
    // Log user in if exists
    $row = mysqli_fetch_array($resultUser);
    $_SESSION['user'] = $row['id'];
    $_SESSION['logged_in'] = true;

    // Get user name
    $firstName = $row['FirstName'];
    $lastName = $row['LastName'];

    // Display welcome message
    echo "<script>
            alert('Welcome, $firstName $lastName');
            window.location.href = 'home.php?id=" . $row['id'] . "';
          </script>";
  } else header("Location: create_passenger.php?email=" . urlencode($email)); // Redirect to create account if new
  exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      background: linear-gradient(to right, #e0f0ff, #cfe9ff, #b9dbff);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      position: relative;
      margin: 0;
    }

    .login-container {
      background: white;
      padding: 100px 80px;  
      border-radius: 20px;  
      box-shadow: 0 8px 40px rgba(0, 123, 255, 0.2);  
      width: 100%;
      max-width: 700px;  
      z-index: 1;
    }

    .brand {
      font-weight: 700;
      font-size: 3.5rem;  
      color: #007bff;
      text-align: center;
      margin-bottom: 20px; 
    }

    .slogan {
      font-size: 1.5rem;
      color: #00254c;
      text-align: center;
      margin-bottom: 40px;
      font-weight: 500;
      line-height: 1.4;
    }

    .form-label {
      color: #00254c;
    }

    .form-control:focus {
      box-shadow: none;
      border-color: #007bff;
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .btn-secondary {
      background-color: #6c757d;
      border: none;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    .d-flex {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 20px;
    }

  </style>
</head>
<body>
  <div class="login-container">
    <!-- Logo + slogan -->
    <div class="brand">AeroMate <i class="fas fa-plane-departure"></i></div>
    <div class="slogan">Let's explore the world, one flight at a time.</div> 
    <!-- Login form -->
    <form action="index.php" method="post">
      <!-- Email login input --> 
      <div class="mb-4">
        <label for="email" class="form-label">Sign in or create account</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
      </div>
      <button type="submit" name="Submit" class="btn btn-primary w-100">Continue</button>
    </form>
  </div>

</body>
</html>
