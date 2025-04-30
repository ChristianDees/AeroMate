<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php');
require_once('validate_session.php');

// Adding a flight
if (isset($_POST['addFlight'])) {
    $airlineName = $_POST['airlineName'] ?? "";
    $departureTime = $_POST['departureTime'] ?? ""; 
    $gate = $_POST['gate'] ?? ""; 
    $originLocation = $_POST['originLocation'] ?? ""; 
    $destinationLocation = $_POST['destinationLocation'] ?? ""; 
    $assignedAircraft = $_POST['assignedAircraft'] ?? ""; 
    $flightStatus = $_POST['flightStatus'] ?? ""; 
    $arrivalTime = $_POST['arrivalTime'] ?? "";
    $airlineID = $_POST['airlineID'] ?? ""; 

    // Insert flight stuff
    // Get max id and +1
    $result = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(FlightID, 2) AS UNSIGNED)) AS maxID FROM flight");
    $row = mysqli_fetch_array($result);
    $maxId = $row['maxID'] ?? 100; 
    $nextIdNumber = $maxId + 1;
    $nextFlightID = 'F' . $nextIdNumber;

    // Format times 
    $departureTimeFormatted = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $departureTime) . ':00'));
    $arrivalTimeFormatted = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $arrivalTime) . ':00'));

    // Bind params and insert flight first
    $insertQuery = $conn->prepare("INSERT INTO flight (FlightID, DepartureTime, Gate, OriginLocation, DestinationLocation, AssignedAircraft, FlightStatus, AirlineID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertQuery->bind_param("ssssssss", $nextFlightID, $departureTimeFormatted, $gate, $originLocation, $destinationLocation, $assignedAircraft, $flightStatus, $airlineID);
    $insertResult = $insertQuery->execute();

    // If flight is inserted successfully, then insert into departure_arrival table
    if ($insertResult) {
        // Bind params and insert arrival time
        $insertArrivalQuery = $conn->prepare("INSERT INTO departure_arrival (FlightID, DepartureTime, ArrivalTime) VALUES (?, ?, ?)");
        $insertArrivalQuery->bind_param("sss", $nextFlightID, $departureTimeFormatted, $arrivalTimeFormatted); 
        $insertArrivalResult = $insertArrivalQuery->execute();

        // Check if both insertions were successful
        $msg = ($insertArrivalResult) ? "Flight $nextFlightID added successfully!" : "Failed to add flight arrival time.";
    } else {
        $msg = "Failed to add flight.";
    }
}

// Delete a flight
if (isset($_POST['deleteFlight'])) {
    $flightID = $_POST['flightID'];

    // Delete from booked
    $deleteBookedQuery = $conn->prepare("DELETE FROM booked WHERE FlightID = ?");
    $deleteBookedQuery->bind_param("s", $flightID); 
    $deleteBookedResult = $deleteBookedQuery->execute();

    // Check if arrival deletion was successful before deleting flight
    if ($deleteBookedResult) {

        // Delete from departure_arrival
        $deleteDPquery = $conn->prepare("DELETE FROM departure_arrival WHERE FlightID = ?");
        $deleteDPquery->bind_param("s", $flightID); 
        $deleteDPresult = $deleteDPquery->execute();

        if ($deleteDPresult) {
            // Delete the flight
            $deleteQuery = $conn->prepare("DELETE FROM flight WHERE FlightID = ?");
            $deleteQuery->bind_param("s", $flightID); 
            $deleteResult = $deleteQuery->execute();

            $msg = $deleteResult ? "Flight $flightID deleted successfully!" : "Failed to delete flight.";
        } else $msg = "Failed to delete related depature_arrival records.";
    } else $msg = "Failed to delete related booked records.";
}


// Modify flight
if (isset($_POST['modifyFlight'])) {
    $flightID = $_POST['flightID'];
    $departureTime = $_POST['departureTime'] ?? ""; 
    $gate = $_POST['gate'] ?? "";
    $originLocation = $_POST['originLocation'] ?? "";
    $destinationLocation = $_POST['destinationLocation'] ?? "";
    $assignedAircraft = $_POST['assignedAircraft'] ?? "";
    $flightStatus = $_POST['flightStatus'] ?? "";
    $arrivalTime = $_POST['arrivalTime'] ?? ""; 
    $airlineID = $_POST['airlineID'] ?? ""; 

    // Format time
    $departureTimeFormatted = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $departureTime) . ':00'));
    $arrivalTimeFormatted = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $arrivalTime) . ':00'));

    // Update flight data
    $updateQuery = "UPDATE flight SET DepartureTime='$departureTimeFormatted', Gate='$gate', OriginLocation='$originLocation', DestinationLocation='$destinationLocation', AssignedAircraft='$assignedAircraft', FlightStatus='$flightStatus', AirlineID='$airlineID' WHERE FlightID = '$flightID'";
    $updateResult = mysqli_query($conn, $updateQuery);

    // Update arrival time 
    $updateArrivalQuery = "UPDATE departure_arrival SET ArrivalTime='$arrivalTimeFormatted' WHERE FlightID = '$flightID'";
    $updateArrivalResult = mysqli_query($conn, $updateArrivalQuery);

    // FrequentFlyer update logic ONLY IF flight is now marked as Completed
    if (strtolower($flightStatus) === 'completed') {
        // Get all PassengerIDs linked to this flight
        $getPassengersQuery = "SELECT PassengerID FROM Booked WHERE FlightID = ?";
        $stmt = $conn->prepare($getPassengersQuery);
        $stmt->bind_param('s', $flightID);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = mysqli_fetch_array($result)) {
            $passengerID = $row['PassengerID'];

            // Count how many completed flights this passenger has
            $countCompleted = "SELECT COUNT(*) as completedCount FROM Booked JOIN Flight ON Booked.FlightID = Flight.FlightID WHERE Booked.PassengerID = ? AND Flight.FlightStatus = 'Completed'";
            $stmtCount = $conn->prepare($countCompleted);
            $stmtCount->bind_param('s', $passengerID);
            $stmtCount->execute();
            $resultCount = $stmtCount->get_result();

            if ($countRow = $resultCount->fetch_assoc()) {
                $completedFlights = $countRow['completedCount'];
                $ffStatus = ($completedFlights >= 3) ? 1 : 0;

                // Update frequentFlyer field
                $updateFF = "UPDATE Passenger SET frequentFlyer = ? WHERE id = ?";
                $stmtUpdate = $conn->prepare($updateFF);
                $stmtUpdate->bind_param('is', $ffStatus, $passengerID);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            $stmtCount->close();
        }
        $stmt->close();
    }

    $msg = ($updateResult && $updateArrivalResult) ? "Flight $flightID updated successfully!" : "Failed to modify flight.";
}


$airlinesQuery = "SELECT AirlineID, Name FROM airline";
$airlinesResult = mysqli_query($conn, $airlinesQuery);

// Initial query to fetch all flights with airline name
$flightsQuery = "SELECT FlightDetails.*, Airline.Name AS AirlineName FROM FlightDetails LEFT JOIN Airline ON FlightDetails.AirlineID = Airline.AirlineID";

// Apply filters if the search form is submitted
if (isset($_POST['searchFlight'])) {
    $filters = [];

    // Get filters
    if (!empty($_POST['departureTime'])) {
        $departureTime = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['departureTime']) . ':00'));
        $filters[] = "DepartureTime = '$departureTime'";
    }
    if (!empty($_POST['gate'])) {
        $gate = mysqli_real_escape_string($conn, $_POST['gate']);
        $filters[] = "Gate LIKE '%$gate%'";
    }
    if (!empty($_POST['originLocation'])) {
        $originLocation = mysqli_real_escape_string($conn, $_POST['originLocation']);
        $filters[] = "OriginLocation LIKE '%$originLocation%'";
    }
    if (!empty($_POST['destinationLocation'])) {
        $destinationLocation = mysqli_real_escape_string($conn, $_POST['destinationLocation']);
        $filters[] = "DestinationLocation LIKE '%$destinationLocation%'";
    }
    if (!empty($_POST['assignedAircraft'])) {
        $assignedAircraft = mysqli_real_escape_string($conn, $_POST['assignedAircraft']);
        $filters[] = "AssignedAircraft LIKE '%$assignedAircraft%'";
    }
    if (!empty($_POST['flightStatus'])) {
        $flightStatus = mysqli_real_escape_string($conn, $_POST['flightStatus']);
        $filters[] = "FlightStatus LIKE '%$flightStatus%'";
    }
    if (!empty($_POST['arrivalTime'])) {
        $arrivalTime = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['arrivalTime']) . ':00'));
        $filters[] = "ArrivalTime = '$arrivalTime'";
    }
    if (!empty($_POST['airlineID'])) {
        $airlineID = mysqli_real_escape_string($conn, $_POST['airlineID']);
        $filters[] = "FlightDetails.AirlineID = '$airlineID'"; 
    }

    // Add filters 
    if (!empty($filters)) $flightsQuery .= " WHERE " . implode(" AND ", $filters);

    $msg = "Search results filtered by entered criteria.";
}

// Execute the query and get the results
$flights = mysqli_query($conn, $flightsQuery);



$passengersByFlight = [];
// Get all passengers for each flight
$query = "SELECT * FROM BookedPassengerDetails";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $passengersByFlight[$row['flightID']][] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Admin Flights</title>
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

    .form-label i{
      color: #007bff;
      
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

    .title {
      font-size: 2.5rem;
      font-weight: bold;
      color: #00254c;
      text-align: center;
      margin-bottom: 30px;
    }

    .modify-btn {
      background-color: #28a745;
      border: none;
    }

    .modify-btn:hover {
      background-color: #218838;
    }

    .delete-btn {
      background-color: #dc3545;
      border: none;
    }

    .delete-btn:hover {
      background-color: #c82333;
    }

  </style>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="flight-search-container">
    <div class="brand">
        <a href="dashboard.php?id=<?php echo $_SESSION['user'] ?? ''; ?>" style="text-decoration: none;">
            AeroMate <i class="fas fa-plane-departure"></i>
        </a>
    </div>
    <div class="title">Manage Flights</div>

    <!-- Add/Modify/Delete Message -->
    <?php if (isset($msg)): ?>
        <div class="alert alert-info text-center">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <!-- Add Flight Form-->
    <form method="post" action="admin_flights.php" id="flightForm">

        <!-- Departure Time -->
        <div class="mb-3 form-group">
            <label for="departureTime" class="form-label">
                <i class="fas fa-clock"></i> Departure Time
            </label>
            <input type="datetime-local" class="form-control" name="departureTime" id="departureTime" required>
        </div>

        <!-- Gate -->
        <div class="mb-3 form-group">
            <label for="gate" class="form-label">
                <i class="fas fa-door-open"></i> Gate
            </label>
            <input type="text" class="form-control" name="gate" id="gate" required>
        </div>

        <!-- Origin Location -->
        <div class="mb-3 form-group">
            <label for="originLocation" class="form-label">
                <i class="fas fa-location-arrow"></i> Origin Location
            </label>
            <input type="text" class="form-control" name="originLocation" id="originLocation" required>
        </div>

        <!-- Destination Location -->
        <div class="mb-3 form-group">
            <label for="destinationLocation" class="form-label">
                <i class="fas fa-location-dot"></i> Destination Location
            </label>
            <input type="text" class="form-control" name="destinationLocation" id="destinationLocation" required>
        </div>

        <!-- Assigned Aircraft -->
        <div class="mb-3 form-group">
            <label for="assignedAircraft" class="form-label">
                <i class="fas fa-plane"></i> Assigned Aircraft
            </label>
            <input type="text" class="form-control" name="assignedAircraft" id="assignedAircraft" required>
        </div>

        <!-- Flight Status -->
        <div class="mb-3 form-group">
            <label for="flightStatus" class="form-label">
                <i class="fas fa-flag-checkered"></i> Flight Status
            </label>
            <input type="text" class="form-control" name="flightStatus" id="flightStatus" required>
        </div>

        <!-- Arrival Time -->
        <div class="mb-3 form-group">
            <label for="arrivalTime" class="form-label">
                <i class="fas fa-clock"></i> Arrival Time
            </label>
            <input type="datetime-local" class="form-control" name="arrivalTime" id="arrivalTime" required>
        </div>

        <!-- Airline -->
        <div class="mb-3 form-group">
            <label for="airlineID" class="form-label">
                <i class="fas fa-building"></i> Airline
            </label>
            <select class="form-control" name="airlineID" id="airlineID" required>
                <option value="" disabled selected>Select an airline</option>
                <?php while ($airline = mysqli_fetch_array($airlinesResult)): ?>
                    <option value="<?php echo $airline['AirlineID']; ?>">
                        <?php echo ($airline['Name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" name="searchFlight" class="btn btn-secondary w-100" id="searchBtn">Search Flights</button>
            <button type="submit" name="addFlight" class="btn btn-primary w-100" id="addBtn">Add Flight</button>
        </div>

    </form>

    <br>

    <!-- Display all flights -->
    <div class="list-group" id="results-section">
        <?php while ($flight = mysqli_fetch_array($flights)): ?>
            <div class="flight-item position-relative mb-3 p-3 border rounded shadow-sm">

                <!-- Delete -->
                <div class="position-absolute" style="top: 10px; right: 10px;"> 
                    <form method="post" action="admin_flights.php" style="display:inline;">
                        <input type="hidden" name="flightID" value="<?php echo $flight['FlightID']; ?>">
                        <button type="submit" name="deleteFlight" class="btn btn-danger btn-sm delete-btn">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>

                <!-- Modify -->
                <div class="position-absolute" style="top: 10px; right: 100px;">
                    <button type="button" class="btn btn-success btn-sm modify-btn" data-bs-toggle="modal" 
                            data-bs-target="#modifyModal<?php echo $flight['FlightID']; ?>">
                        <i class="fas fa-edit"></i> Modify
                    </button>
                </div>

                <!-- View Passengers -->
                <div class="position-absolute" style="top: 10px; right: 190px;">
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#viewPassengersModal<?php echo $flight['FlightID']; ?>">
                        <i class="fas fa-users"></i> View Passengers
                    </button>
                </div>


                <!-- Flight details -->
                <div>
                    <h5><strong>Flight ID: <?php echo $flight['FlightID']; ?></strong></h5>
                    <p><strong>Departure Time:</strong> <?php echo $flight['DepartureTime']; ?></p>
                    <p><strong>Arrival Time:</strong> <?php echo $flight['ArrivalTime']; ?></p>
                    <p><strong>Gate:</strong> <?php echo $flight['Gate']; ?></p>
                    <p><strong>Origin Location:</strong> <?php echo $flight['OriginLocation']; ?></p>
                    <p><strong>Destination Location:</strong> <?php echo $flight['DestinationLocation']; ?></p>
                    <p><strong>Assigned Aircraft:</strong> <?php echo $flight['AssignedAircraft']; ?></p>
                    <p><strong>Flight Status:</strong> <?php echo $flight['FlightStatus']; ?></p>
                    <p><strong>Airline:</strong> <?php echo $flight['AirlineName']; ?></p> 
                </div>
            </div>

            <!-- Modify Modal -->
            <div class="modal fade" id="modifyModal<?php echo $flight['FlightID']; ?>" tabindex="-1" aria-labelledby="modifyModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modifyModalLabel">Modify Flight Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <!-- Modify form -->
                            <form method="post" action="admin_flights.php">
                                <input type="hidden" name="flightID" value="<?php echo $flight['FlightID']; ?>">

                                <!-- Departure Time -->
                                <div class="mb-3 form-group">
                                    <label for="modifyDepartureTime" class="form-label">
                                        <i class="fas fa-clock"></i> Departure Time
                                    </label>
                                    <input type="datetime-local" class="form-control" name="departureTime" value="<?php echo $flight['DepartureTime']; ?>" required>
                                </div>

                                <!-- Gate -->
                                <div class="mb-3 form-group">
                                    <label for="modifyGate" class="form-label">
                                        <i class="fas fa-door-open"></i> Gate
                                    </label>
                                    <input type="text" class="form-control" name="gate" value="<?php echo $flight['Gate']; ?>" required>
                                </div>

                                <!-- Origin Location -->
                                <div class="mb-3 form-group">
                                    <label for="modifyOriginLocation" class="form-label">
                                        <i class="fas fa-location-arrow"></i> Origin Location
                                    </label>
                                    <input type="text" class="form-control" name="originLocation" value="<?php echo $flight['OriginLocation']; ?>" required>
                                </div>

                                <!-- Destination Location -->
                                <div class="mb-3 form-group">
                                    <label for="modifyDestinationLocation" class="form-label">
                                        <i class="fas fa-location-dot"></i> Destination Location
                                    </label>
                                    <input type="text" class="form-control" name="destinationLocation" value="<?php echo $flight['DestinationLocation']; ?>" required>
                                </div>

                                <!-- Assigned Aircraft -->
                                <div class="mb-3 form-group">
                                    <label for="modifyAssignedAircraft" class="form-label">
                                        <i class="fas fa-plane"></i> Assigned Aircraft
                                    </label>
                                    <input type="text" class="form-control" name="assignedAircraft" value="<?php echo $flight['AssignedAircraft']; ?>" required>
                                </div>

                                <!-- Flight Status -->
                                <div class="mb-3 form-group">
                                    <label for="modifyFlightStatus" class="form-label">
                                        <i class="fas fa-flag-checkered"></i> Flight Status
                                    </label>
                                    <input type="text" class="form-control" name="flightStatus" value="<?php echo $flight['FlightStatus']; ?>" required>
                                </div>

                                <!-- Arrival Time -->
                                <div class="mb-3 form-group">
                                    <label for="modifyArrivalTime" class="form-label">
                                        <i class="fas fa-clock"></i> Arrival Time
                                    </label>
                                    <input type="datetime-local" class="form-control" name="arrivalTime" value="<?php echo $flight['ArrivalTime']; ?>" required>
                                </div>

                                <!-- Airline -->
                                <div class="mb-3 form-group">
                                    <label for="modifyAirlineID" class="form-label">
                                        <i class="fas fa-building"></i> Airline ID
                                    </label>
                                    <select class="form-control" name="airlineID" required>
                                        <option value="" disabled>Select an airline</option>
                                        <?php
                                        $airlinesResult = mysqli_query($conn, $airlinesQuery); 
                                        while ($airline = mysqli_fetch_array($airlinesResult)):
                                        ?>
                                            <option value="<?php echo $airline['AirlineID']; ?>"
                                                <?php if ($airline['AirlineID'] == $flight['AirlineID']) echo 'selected'; ?>>
                                                <?php echo ($airline['Name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>

                                </div>

                                <button type="submit" name="modifyFlight" class="btn btn-success w-100">Modify Flight</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Passengers Modal -->
            <div class="modal fade" id="viewPassengersModal<?php echo $flight['FlightID']; ?>" tabindex="-1" aria-labelledby="viewPassengersLabel<?php echo $flight['FlightID']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Passengers on Flight <?php echo $flight['FlightID']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Table for each passengers passengerID, first name, last name -->
                        <div class="modal-body">
                            <?php if (!empty($passengersByFlight[$flight['FlightID']])): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Passenger ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Frequent Flyer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($passengersByFlight[$flight['FlightID']] as $p): ?>
                                            <tr>
                                                <td><?php echo $p['PassengerID']; ?></td>
                                                <td><?php echo ($p['FirstName']); ?></td>
                                                <td><?php echo ($p['LastName']); ?></td>
                                                <td><?php echo ($p['FrequentFlyer'] == 1) ? 'Yes' : 'No'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No passengers found for this flight.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script>
    const searchBtn = document.getElementById('searchBtn');
    const flightForm = document.getElementById('flightForm');
    
    const requiredFields = [
        'departureTime',
        'gate',
        'originLocation',
        'destinationLocation',
        'assignedAircraft',
        'flightStatus',
        'arrivalTime',
        'airlineID'
    ];

    // Set all fields as not required when searching for a flight
    searchBtn.addEventListener('click', function () {
        requiredFields.forEach(fieldId => {
            document.getElementById(fieldId).required = false;
        });
    });
</script>

</body>
</html>