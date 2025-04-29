<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php');
require_once('validate_session.php');

// Adding new airline
if (isset($_POST['addAirline'])) {
    $airlineName = $_POST['airlineName'] ?? "";
    $headquartersAddress = $_POST['headquartersAddress'] ?? "";
    $contactInformation = $_POST['contactInformation'] ?? ""; 
    $fleetSize = $_POST['fleetSize'] ?? "";

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO airline (Name, HeadquartersAddress, ContactInformation, fleetsize) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        // Bind params and execute
        $stmt->bind_param("sssi", $airlineName, $headquartersAddress, $contactInformation, $fleetSize);
        $insertResult = $stmt->execute();
        $stmt->close();
        $msg = $insertResult ? "Airline $airlineName added successfully!" : "Failed to add airline.";
    } else $msg = "Database error: Unable to prepare statement.";
}

// Delete airline
if (isset($_POST['deleteAirline'])) {
    $airlineID = $_POST['airlineID'];
    // Delete statement
    $stmt = $conn->prepare("DELETE FROM airline WHERE AirlineID = ?");
    if ($stmt) {
        // Bind and execute
        $stmt->bind_param("i", $airlineID);
        $stmt->execute();
        // Check if deleted
        if ($stmt->affected_rows > 0) $msg = "Airline $airlineID deleted successfully!";
        else $msg = "Failed to delete airline.";
        $stmt->close();
    } else $msg = "Failed to prepare the statement.";
}


// Modify airline
if (isset($_POST['modifyAirline'])) {
    $airlineID = $_POST['airlineID'];
    $airlineName = $_POST['airlineName'];
    $headquartersAddress = $_POST['headquartersAddress'];
    $contactInformation = $_POST['contactInformation'];
    $fleetSize = $_POST['fleetSize'];
    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE airline SET Name = ?, HeadquartersAddress = ?, ContactInformation = ?, fleetsize = ? WHERE AirlineID = ?");
    if ($stmt) {
        // Bind and execute
        $stmt->bind_param("sssii", $airlineName, $headquartersAddress, $contactInformation, $fleetSize, $airlineID);
        $stmt->execute();

        // Check if updated
        if ($stmt->affected_rows > 0) $msg = "Airline modified successfully!"; 
        else $msg = "No changes made or airline not found.";
        $stmt->close();
    } else $msg = "Failed to prepare update statement.";
}

// Get all airlines for display
$airlines = mysqli_query($conn, "SELECT * FROM airline");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Admin Airlines</title>
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

    .airline-search-container, .results-container {
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

    .airline-item {
      border: 1px solid #ddd;
      padding: 20px;
      margin-bottom: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .airline-item:hover {
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

<div class="airline-search-container">
    <!-- Company logo -->
    <div class="brand">
        <a href="dashboard.php?id=<?php echo $_SESSION['user'] ?? ''; ?>" style="text-decoration: none;">
            AeroMate <i class="fas fa-plane-departure"></i>
        </a>
    </div>
    <div class="title">Manage Airlines</div>

    <!-- Add/Modify/Delete Message -->
    <?php if (isset($msg)): ?>
        <div class="alert alert-info text-center">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <!-- Add Airline Form -->
    <form method="post" action="admin_airlines.php">

        <!-- Name -->
        <div class="mb-3 form-group">
            <label for="airlineName" class="form-label">
                <i class="fas fa-building"></i> Airline Name
            </label>
            <input type="text" class="form-control" name="airlineName" id="airlineName" required>
        </div>

        <!-- Headquarters -->
        <div class="mb-3 form-group">
            <label for="headquartersAddress" class="form-label">
                <i class="fas fa-map-marker-alt"></i> Headquarters Address
            </label>
            <input type="text" class="form-control" name="headquartersAddress" id="headquartersAddress" required>
        </div>

        <!-- Contact Info -->
        <div class="mb-3 form-group">
            <label for="contactInformation" class="form-label">
                <i class="fas fa-phone-alt"></i> Contact Information
            </label>
            <input type="text" class="form-control" name="contactInformation" id="contactInformation" required>
        </div>

        <!-- Fleet Size -->
        <div class="mb-3 form-group">
            <label for="fleetSize" class="form-label">
                <i class="fas fa-plane"></i> Fleet Size
            </label>
            <input type="number" class="form-control" name="fleetSize" id="fleetSize" required>
        </div>

        <button type="submit" name="addAirline" class="btn btn-primary w-100">Add Airline</button>
    </form>

    <br>

    <!-- Display all airlines -->
    <div class="list-group">
        <?php while ($airline = mysqli_fetch_array($airlines)): ?>
            <div class="airline-item position-relative mb-3 p-3 border rounded shadow-sm">

                <!-- Modify Button -->
                <div class="position-absolute" style="top: 10px; right: 10px;">
                    <button type="button" class="btn btn-success btn-sm modify-btn" data-bs-toggle="modal" 
                            data-bs-target="#modifyModal<?php echo $airline['AirlineID']; ?>">
                        <i class="fas fa-edit"></i> Modify
                    </button>
                </div>

                <!-- Delete Button -->
                <div class="position-absolute" style="top: 10px; right: 100px;"> 
                    <form method="post" action="admin_airlines.php" style="display:inline;">
                        <input type="hidden" name="airlineID" value="<?php echo $airline['AirlineID']; ?>">
                        <button type="submit" name="deleteAirline" class="btn btn-danger btn-sm delete-btn">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>

                <!-- Airline Info -->
                <div>
                    <h5><strong>Name: <?php echo $airline['Name']; ?></strong></h5>
                    <p><strong>Headquarters Address:</strong> <?php echo $airline['HeadquartersAddress']; ?></p>
                    <p><strong>Contact Information:</strong> <?php echo $airline['ContactInformation']; ?></p>
                    <p><strong>Fleet Size:</strong> <?php echo $airline['fleetsize']; ?> aircraft</p>
                </div>
            </div>

            <!-- Modify Modal -->
            <div class="modal fade" id="modifyModal<?php echo $airline['AirlineID']; ?>" tabindex="-1" aria-labelledby="modifyModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    
                        <!-- Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="modifyModalLabel">Modify Airline Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body">
                            <!-- Modification form -->
                            <form method="post" action="admin_airlines.php">

                                <!-- Pass the airline ID -->
                                <input type="hidden" name="airlineID" value="<?php echo $airline['AirlineID']; ?>">

                                <!-- Name -->
                                <div class="mb-3 form-group">
                                    <label for="modifyAirlineName" class="form-label">
                                        <i class="fas fa-building"></i> Airline Name
                                    </label>
                                    <input type="text" class="form-control" name="airlineName" id="modifyAirlineName" value="<?php echo $airline['Name']; ?>" required>
                                </div>

                                <!-- Headquarters Address -->
                                <div class="mb-3 form-group">
                                    <label for="modifyHeadquartersAddress" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i> Headquarters Address
                                    </label>
                                    <input type="text" class="form-control" name="headquartersAddress" id="modifyHeadquartersAddress" value="<?php echo $airline['HeadquartersAddress']; ?>" required>
                                </div>

                                <!-- Contact Information -->
                                <div class="mb-3 form-group">
                                    <label for="modifyContactInformation" class="form-label">
                                        <i class="fas fa-phone-alt"></i> Contact Information
                                    </label>
                                    <input type="text" class="form-control" name="contactInformation" id="modifyContactInformation" value="<?php echo $airline['ContactInformation']; ?>" required>
                                </div>

                                <!-- Fleet Size -->
                                <div class="mb-3 form-group">
                                    <label for="modifyFleetSize" class="form-label">
                                        <i class="fas fa-plane"></i> Fleet Size
                                    </label>
                                    <input type="number" class="form-control" name="fleetSize" id="modifyFleetSize" value="<?php echo $airline['fleetsize']; ?>" required>
                                </div>

                                <button type="submit" name="modifyAirline" class="btn btn-success w-100">Modify Airline</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        <?php endwhile; ?>
    </div>

</div>
</body>
</html>