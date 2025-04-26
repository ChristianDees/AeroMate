<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php'); 
require_once('validate_session.php');

// Get dropdown vals
$ogLocations = $conn->query("SELECT DISTINCT OriginLocation FROM Flight");
$dstLocations = $conn->query("SELECT DISTINCT DestinationLocation FROM Flight");
$airlines = $conn->query("SELECT Name FROM Airline");

$ogLocationsList = [];
$dstLocationsList = [];
$airlinesList = [];

// Add dropdown vals to lists
while ($row = mysqli_fetch_array($ogLocations)){$ogLocationsList[] = $row['OriginLocation'];}
while ($row = mysqli_fetch_array($dstLocations)){$dstLocationsList[] = $row['DestinationLocation'];}
while ($row = mysqli_fetch_array($airlines)){$airlinesList[] = $row['Name'];}

$joins = "";

// Only include non finished flights
$conditions = " WHERE Flight.FlightStatus != 'Completed'";

// Airline filter 
if (isset($_GET['airline']) && !empty($_GET['airline'])) {
    $airlineName = $_GET['airline'];
    $joins .= " INNER JOIN Airline ON Flight.AirlineID = Airline.AirlineID"; // Match in both tables
    $conditions .= " AND Airline.Name = '$airlineName'";
}

// Origin location filter
if (isset($_GET['from']) && !empty($_GET['from'])) {
    $from = $_GET['from'];
    $conditions .= " AND Flight.OriginLocation = '$from'";
}

// Destination location filter
if (isset($_GET['to']) && !empty($_GET['to'])) {
    $to = mysqli_real_escape_string($conn, $_GET['to']);
    $conditions .= " AND Flight.DestinationLocation = '$to'";
}

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $date = $_GET['date'];
    $conditions .= " AND Flight.DepartureTime LIKE '$date%'";
}

// Get results from final query
$results = $conn->query("SELECT * FROM Flight" . $joins . $conditions);
$flights = [];
while ($row = mysqli_fetch_array($results)) {$flights[] = $row;}

// Booking logic 
if (isset($_POST['bookFlightID'])) {
    // Get id from being logged in
    $passengerID = $_SESSION['user']; 
    $flightID = $_POST['bookFlightID'];

    // Check if flight is already booked
    $checkSql = "SELECT * FROM booked WHERE passengerID = ? AND flightID = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('ss', $passengerID, $flightID);
    $stmt->execute();
    $checkResults = $stmt->get_result();

    // Result exists, meaning already booked
    if ($checkResults->num_rows > 0) { $bookingMsg = "You have already booked this flight!";
    } else {
        // Flight not booked yet
        $bookFlight = "INSERT INTO booked VALUES (?, ?, 'Booked')";
        $stmtB = $conn->prepare($bookFlight);
        $stmtB->bind_param('ss', $passengerID, $flightID);
        if ($stmtB->execute()) $bookingMsg = "Flight $flightID successfully booked!";
        else $bookingMsg = "Failed to book flight. Please try again.";
        $stmtB->close();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Flight Search & Results</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      background: linear-gradient(to right, #e0f0ff, #cfe9ff, #b9dbff);
      font-family: 'Segoe UI', sans-serif;
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      padding-top: 80px;
      padding-bottom: 80px;
    }

    .brand {
      font-weight: 700;
      font-size: 2rem;
      color: #007bff;
      text-align: left;
      margin-bottom: 10px;
    }

    .flight-search-container, .results-container {
      background: white;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0, 123, 255, 0.2);
      width: 100%;
      max-width: 800px;
      margin: 0 auto 40px auto;
    }

    .form-label {
      color: #00254c;
      display: flex;
      align-items: center;
      gap: 10px;
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

    .criteria {
      font-size: 1.2rem;
      color: #00254c;
      margin-bottom: 40px;
      text-align: center;
      font-weight: 500;
    }

    .flight-item {
      border: 1px solid #ddd;
      padding: 20px;
      margin-bottom: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .flight-item:hover {
      border-color: #007bff;
    }

    .flight-details {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .input-icon {
      position: relative;
    }

    .input-icon i {
      color: #007bff;
    }

    .input-icon .fa-map-marker-alt,
    .input-icon .fa-calendar-alt {
      font-size: 1.2rem;
    }
    .title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #00254c;
        text-align: center;
        margin-bottom: 30px;
    }
  </style>
</head>
<body>

<div class="flight-search-container">

    <!-- Company logo -->
    <div class="brand">
      <a href="home.php?id=<?php echo $_SESSION['user'] ?? ''; ?>" style="text-decoration: none;">
          AeroMate <i class="fas fa-plane-departure"></i>
      </a>
    </div>
    <div class="title">Book a Flight</div>
    
    <!-- Booking Message -->
    <?php if (isset($bookingMsg)): ?>
        <div class="alert alert-info text-center">
            <?php echo $bookingMsg; ?>
        </div>
    <?php endif; ?>
    
    <!-- Book flight filter form -->
    <form action="book_flight.php" method="get">
      <!-- Origin Location Filter -->
      <div class="mb-4 input-icon">
        <label for="from" class="form-label">
          <i class="fas fa-map-marker-alt"></i> Leaving From
        </label>
        <!-- Display possible locations -->
        <select class="form-control" id="from" name="from">
          <option value="" selected>Select Departure City</option>
          <?php foreach ($ogLocationsList as $location): ?>
            <option value="<?= $location; ?>" <?= ($_GET['from'] ?? '') === $location ? 'selected' : ''; ?>><?= $location; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Destination Location Filter -->
      <div class="mb-4 input-icon">
        <label for="to" class="form-label">
          <i class="fas fa-map-marker-alt"></i> Going To
        </label>
        <!-- Display possible locations -->
        <select class="form-control" id="to" name="to">
          <option value="" selected>Select Destination City</option>
          <?php foreach ($dstLocationsList as $location): ?>
            <option value="<?= $location; ?>" <?= ($_GET['from'] ?? '') === $location ? 'selected' : ''; ?>><?= $location; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Departure Date Filter -->
      <div class="mb-4 input-icon">
        <label for="date" class="form-label">
          <i class="fas fa-calendar-alt"></i> Flight Date
        </label>
        <input type="date" class="form-control" id="date" name="date" value="<?= $_GET['date'] ?? ''; ?>">
      </div>

      <!-- Airline Name Filter -->
      <div class="mb-4">
        <label for="airline" class="form-label">Preferred Airline (Optional)</label>
        <!-- Display possible airlines -->
        <select class="form-control" id="airline" name="airline">
          <option value="" selected>Select an Airline</option>
          <?php foreach ($airlinesList as $airline): ?>
            <option value="<?= $airline; ?>" <?= ($_GET['from'] ?? '') === $airline ? 'selected' : ''; ?>><?= $airline; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Add reset button alongside the search button -->
      <div class="d-flex justify-content-between gap-3">
        <button type="submit" class="btn btn-primary w-75">Search Flights</button>
        <a href="book_flight.php" class="btn btn-secondary w-25">Reset Filters</a>
      </div>
    </form>
  </div>

  <!-- Flight Results -->
  <div class="results-container" id="results">
    <div class="flight-list">
      <!-- Check if any exist -->      
      <?php if (count($flights) > 0): ?>
        <!-- Display each flight details -->
        <?php foreach ($flights as $flight): ?>
        <div class="flight-item">
          <div class="flight-details">
            <div class="flight-info">
              <h5><strong>Flight ID: <?php echo $flight['FlightID']; ?></strong></h5>
              <p><strong>Route:</strong> <?php echo $flight['OriginLocation']; ?> to <?php echo $flight['DestinationLocation']; ?></p>
              <p><strong>Departure Time:</strong> <?php echo $flight['DepartureTime']; ?></p>
              <p><strong>Gate:</strong> <?php echo $flight['Gate']; ?></p>
              <p><strong>Assigned Aircraft:</strong> <?php echo $flight['AssignedAircraft']; ?></p>
              <p><strong>Flight Status:</strong> <?php echo $flight['FlightStatus']; ?></p>
            </div>
            <div class="flight-price">
            <!-- Book flight form -->
            <form method="post" action="book_flight.php">
              <input type="hidden" name="bookFlightID" value="<?php echo $flight['FlightID']; ?>">
              <button type="submit" class="btn btn-primary">Book Now</button>
            </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No flights found with the given criteria.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Automated scroll to results upon search -->
  <script>
    // If any params, scroll
    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('from') || urlParams.has('to') || urlParams.has('date') || urlParams.has('airline')) {
        // Get results section & goto
        const resultsSection = document.getElementById('results');
        if (resultsSection) resultsSection.scrollIntoView({ behavior: 'smooth' });
      }
    };
  </script>
</body>
</html>