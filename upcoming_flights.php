<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php'); 
require_once('validate_session.php');

if (isset($_GET['id'])){
  $userID = $_GET['id'];
  // Get upcoming flights (not completed)
  $sql = "
      SELECT f.FlightID, f.OriginLocation, f.DestinationLocation, f.DepartureTime, f.Gate, f.AssignedAircraft, f.FlightStatus
      FROM booked b
      JOIN Flight f ON b.flightID = f.FlightID
      WHERE b.passengerID = ? AND f.FlightStatus != 'completed'
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s', $userID); 
  $stmt->execute();
  $result = $stmt->get_result();

  // Get upcoming flights into list
  $flights = [];
  while ($row = mysqli_fetch_array($result)){$flights[] = $row;}
  $stmt->close();
}

// Check if unbooked occurred
if (isset($_GET['flightID'])) {
    $flightID = $_GET['flightID'];
    $bookingMsg = "Flight $flightID successfully unbooked!";
} else die();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Upcoming Flights</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="results-container" id="results">
    <!-- Company logo -->
    <div class="brand">
      <a href="home.php?id=<?php echo $_SESSION['user'] ?? ''; ?>" style="text-decoration: none;">
          AeroMate <i class="fas fa-plane-departure"></i>
      </a>
    </div>

    <!-- Title -->
    <div class="title">
      Upcoming Flights
    </div>

    <!-- Display unbooked msg -->
    <?php if (isset($bookingMsg)): ?>
        <div class="alert alert-info text-center">
            <?php echo $bookingMsg; ?>
        </div>
    <?php endif; ?>

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
              <p><strong>Flight Status:</strong> <?php echo $flight['FlightStatus']; ?></p>
            </div>
            <a href="unbook_flight.php?flightID=<?php echo $flight['FlightID']; ?>&userID=<?php echo $userID; ?>" 
              class="btn btn-danger">
              <i class="fas fa-trash"></i> Unbook
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>You have no upcoming flights.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>