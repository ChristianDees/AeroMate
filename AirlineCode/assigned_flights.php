<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php'); 
require_once('validate_session.php');

if (isset($_GET['id'])) {
  $employeeID = $_GET['id']; 

  // Get assigned flights 
  $sql = "SELECT * FROM EmployeeFlightAssignments WHERE UserID = ?;";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s', $employeeID); 
  $stmt->execute();
  $result = $stmt->get_result();
  $flights = [];
  while ($row = mysqli_fetch_array($result)) $flights[] = $row;
  $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Assigned Flights</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="results-container" id="results">
    <!-- Company logo -->
    <div class="brand">
      <a href="dashboard.php?id=<?php echo $_SESSION['user'] ?? ''; ?>" style="text-decoration: none;">
          AeroMate <i class="fas fa-plane-departure"></i>
      </a>
    </div>

    <!-- Title -->
    <div class="title">
      Assigned Flights
    </div>

    <!-- Display flights if exist -->
    <div class="flight-list">
      <?php if (count($flights) > 0): ?>
        <!-- Display each flight detail -->
        <?php foreach ($flights as $flight): ?>
        <div class="flight-item" id="flight-<?php echo $flight['FlightID']; ?>">
          <div class="flight-details">
            <div class="flight-info">
              <h5><strong>Flight ID: <?php echo $flight['FlightID']; ?></strong></h5>
              <p><strong>Route:</strong> <?php echo $flight['OriginLocation']; ?> to <?php echo $flight['DestinationLocation']; ?></p>
              <p><strong>Departure Time:</strong> <?php echo $flight['DepartureTime']; ?></p>
              <p><strong>Gate:</strong> <?php echo $flight['Gate']; ?></p>
              <p><strong>Assigned Aircraft:</strong> <?php echo $flight['AssignedAircraft']; ?></p>
              <p><strong>Status:</strong> <?php echo $flight['FlightStatus']; ?></p>
              <p><strong>Role:</strong> <?php echo $flight['Role']; ?></p>
            </div>
            
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>You have no assigned upcoming flights.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>