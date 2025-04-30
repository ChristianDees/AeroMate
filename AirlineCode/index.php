<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once("config.php");
$_SESSION['loggedIn'] = false;

if (!empty($_POST) && isset($_POST['Submit'])) {

  // Get user details
  $email = $_POST['email'] ?? "";
  $pass = $_POST['password'] ?? "";
  $userType = $_POST['type'] ?? "";
  $_SESSION['userType'] = $userType;
  $queryUser = "SELECT * FROM users WHERE email = ? AND password = ? and type = ?";
  $stmt = $conn->prepare($queryUser);
  $stmt->bind_param("sss", $email, $pass, $userType);
  $stmt->execute();
  $resultUser = $stmt->get_result();

  // If user exists
  if ($resultUser->num_rows > 0) {
    $row = $resultUser->fetch_assoc();
    $_SESSION['loggedIn'] = true;
    $_SESSION['user'] = $row['id'];
    $firstName = $row['FirstName'];
    $lastName = $row['LastName'];
    // Welcome the user with their name
    echo "<script>
            alert('Welcome, $firstName $lastName');
            window.location.href = 'dashboard.php?id=" . $row['id'] . "';
          </script>";
  } else $loginError = "Incorrect email or password. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AeroMate Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      background: linear-gradient(to right, #e0f0ff, #cfe9ff, #b9dbff);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
    }

    .login-container {
      background: white;
      padding: 60px 50px;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0, 123, 255, 0.2);
      width: 100%;
      max-width: 500px;
    }

    .brand {
      font-weight: 700;
      font-size: 3rem;
      color: #007bff;
      text-align: center;
      margin-bottom: 10px;
    }

    .slogan {
      font-size: 1.25rem;
      color: #00254c;
      text-align: center;
      margin-bottom: 30px;
      font-weight: 500;
    }

    .form-label {
      color: #00254c;
    }

    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
      border-color: #007bff;
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
      font-weight: 500;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .btn-outline-secondary {
      border: 2px solid #6c757d;
      font-weight: 500;
      color: #6c757d;
    }

    .btn-outline-secondary:hover {
      background-color: #6c757d;
      color: white;
    }

    .d-grid {
      margin-top: 25px;
    }

    .alert {
      margin-top: 20px;
      font-weight: 600;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- Company logo + slogan -->
    <div class="brand">AeroMate <i class="fas fa-plane-departure"></i></div>
    <div class="slogan">Let's explore the world, one flight at a time.</div>

    <!-- Login form -->
    <form id="loginForm" action="index.php" method="post">
      <!-- User type -->
      <div class="mb-3">
        <label for="type" class="form-label">Login Type</label>
        <select class="form-select" id="type" name="type" required>
          <option value="" disabled selected>Select your role</option>
          <option value="passenger">Passenger</option>
          <option value="crewmember">Crewmember</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label for="text" class="form-label">Email address</label>
        <input type="text" class="form-control" id="email" name="email" placeholder="Enter email" required>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
      </div>

      <!-- Log in -->
      <div class="d-grid gap-2">
        <button type="submit" name="Submit" class="btn btn-primary">Log In</button>
        <button type="button" id="signupBtn" class="btn btn-outline-secondary">Create New Account</button>
      </div>

      <!-- Display login error -->
      <?php if (isset($loginError)): ?>
        <div class="alert alert-danger">
          <?php echo $loginError; ?>
        </div>
      <?php endif; ?>
    </form>
  </div>

  <script>
    // Popup alert before signing up
    document.getElementById('signupBtn').addEventListener('click', function () {
      const type = document.getElementById('type').value;
      const email = document.getElementById('email').value;
      if (!type) {
        alert("Please select your role before signing up.");
        return;
      }
      window.location.href = `account_interface.php?type=${encodeURIComponent(type)}&email=${encodeURIComponent(email)}`;
    });

    // Change fields when Admin 
    function updateEmailFieldBehavior() {
      const type = document.getElementById('type').value;
      const signupBtn = document.getElementById('signupBtn');
      const emailField = document.getElementById('email');
      const emailLabel = document.querySelector('label[for="email"]');
      // Change email to username
      if (type === 'admin') {
        signupBtn.disabled = true;
        emailField.setAttribute('type', 'text');
        emailField.setAttribute('placeholder', 'Enter username');
        emailLabel.textContent = 'Username';
      } else {
        signupBtn.disabled = false;
        //emailField.setAttribute('type', 'email');
        emailField.setAttribute('placeholder', 'Enter email');
        emailLabel.textContent = 'Email address';
      }
    }
    document.getElementById('type').addEventListener('change', updateEmailFieldBehavior);
    window.onload = updateEmailFieldBehavior;
</script>


</body>
</html>
