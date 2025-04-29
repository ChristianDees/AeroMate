<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php');
require_once('validate_session.php');

// Get information regarding logged in user
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $sql = "SELECT * FROM Passenger where id = '$id'";
  $result = $conn->query($sql);
  $row = mysqli_fetch_array($result);
  $userType = $_SESSION['userType'];
} else die();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      background: linear-gradient(to right, #e0f0ff, #cfe9ff, #b9dbff);
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-attachment: fixed;
    }

    .dashboard-wrapper {
      background: #fff;
      padding: 50px 40px;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0, 123, 255, 0.2);
      max-width: 600px;
      width: 100%;
      text-align: center;
    }

    .brand {
      font-size: 2.5rem;
      font-weight: bold;
      color: #007bff;
      margin-bottom: 30px;
    }

    .menu-button {
      margin: 12px 0;
      padding: 14px 20px;
      font-size: 1.05rem;
      font-weight: 500;
      width: 100%;
      border-radius: 10px;
      background-color: #007bff;
      color: white;
      border: none;
      transition: background-color 0.3s ease;
    }

    .menu-button:hover {
      background-color: #0056b3;
    }

    .menu-icon {
      margin-right: 10px;
    }

    .logout-btn {
      margin-top: 25px;
      padding: 10px 20px;
      font-size: 1rem;
      width: 100%;
      border-radius: 10px;
      background-color: #c82333; 
      color: white;
      font-weight: 500;
      border: none;
      transition: background-color 0.3s ease;
    }

    .logout-btn:hover {
      background-color: #a71d2a;
    }

  </style>
</head>
<body>
  <div class="dashboard-wrapper">
    <div class="brand">AeroMate <i class="fas fa-plane-departure"></i></div>

    <!-- Passenger Specific -->
    <?php if ($userType == 'passenger'): ?> 
      <!-- Book Flight -->
      <a href="book_flight.php?id=<?= $id ?>">
        <button class="menu-button"><i class="fas fa-plane menu-icon"></i> Book a Flight</button>
      </a>

      <!-- Upcoming Flights -->
      <a href="upcoming_flights.php?id=<?= $id ?>">
        <button class="menu-button"><i class="fas fa-calendar-alt menu-icon"></i> Upcoming Flights</button>
      </a>

      <!-- Flight History -->
      <a href="flight_history.php?id=<?= $id ?>">
        <button class="menu-button"><i class="fas fa-history menu-icon"></i> Flight History</button>
      </a>
    <?php endif; ?>

    <!-- Crewmember Specific -->
    <?php if ($userType == 'crewmember'): ?> 
      <!-- Assigned Flights -->
      <a href="assigned_flights.php?id=<?= $id ?>">
        <button class="menu-button"><i class="fas fa-plane menu-icon"></i> Assigned Flights </button>
      </a>
    <?php endif; ?>

    <!-- Admin Specific -->
    <?php if ($userType == 'admin'): ?> 
      <!-- Assign Crewmembers -->
      <a href="admin_assign.php?id=<?= $id ?>">
        <button class="menu-button"><i class="fas fa-users menu-icon"></i> Assign Crewmembers </button>
      </a>

      <!-- Manage Airlines -->
      <a href="admin_airlines.php?id=<?= $id ?>">
        <button class="menu-button"><i class="fas fa-building menu-icon"></i> Manage Airlines </button>
      </a>

      <!-- Manage Flights -->
      <a href="admin_flights.php?id=<?= $id ?>">
        <button class="menu-button"><i class="fas fa-plane menu-icon"></i> Manage Flights </button>
      </a>
    <?php endif; ?>

    <!-- Update Account -->
    <a href="account_interface.php?id=<?= $id ?>">
      <button class="menu-button"><i class="fas fa-user menu-icon"></i> My Profile </button>
    </a>

    <!-- Logout -->
    <a href="logout.php">
      <button class="logout-btn">
        <i class="fas fa-sign-out-alt menu-icon"></i> Logout
      </button>
    </a>
  </div>
</body>
</html>