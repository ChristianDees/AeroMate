<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342958
April 26, 2025
-->

<?php
session_start();
require_once('config.php');
require_once('validate_session.php');

// Assign crewmember to a flight
if (isset($_POST['assignCrew'])) {
    $flightID = $_POST['flightID'];
    $crewMemberID = $_POST['crewMemberID'];

    // Get crews assignments
    $assignedCrew = [];
    $result = mysqli_query($conn, "SELECT * FROM CrewAssignments");

    while ($row = mysqli_fetch_array($result)) {
        $fid = $row['flightID'];
        $assignedCrew[$fid]['crewFirstNames'][] = $row['FirstName'];
        $assignedCrew[$fid]['crewLastNames'][] = $row['LastName'];
        $assignedCrew[$fid]['crewRoles'][] = ucfirst($row['Role']);
        $assignedCrew[$fid]['crewIDs'][] = $row['crewID'];
    }

    // Gather role usage for this flight
    $existingRoles = [];
    $roleCount = ['Flight Attendant' => 0];
    if (isset($assignedCrew[$flightID])) {
        foreach ($assignedCrew[$flightID]['crewRoles'] as $role) {
            $role = ucfirst($role);
            if (!isset($roleCount[$role])) $roleCount[$role] = 1;
            else $roleCount[$role]++;
            $existingRoles[] = $role;
        }
    }

    // Get role of the crewmember being assigned
    $crewRoleResult = mysqli_query($conn, "SELECT Role FROM Crewmember WHERE id = '$crewMemberID'");
    $crewRoleRow = mysqli_fetch_array($crewRoleResult);
    $crewRole = ucfirst($crewRoleRow['Role']); 

    // Validate that crewmember can be assigned that flight
    $canAssign = true;
    if ($crewRole === 'Pilot' && in_array('Pilot', $existingRoles)) {
        $assignMsg = "A pilot is already assigned to this flight.";
        $canAssign = false;
    } elseif ($crewRole === 'Copilot' && in_array('Copilot', $existingRoles)) {
        $assignMsg = "A copilot is already assigned to this flight.";
        $canAssign = false;
    } elseif ($crewRole === 'Flight Attendant' && $roleCount['Flight Attendant'] >= 2) {
        $assignMsg = "This flight already has two flight attendants.";
        $canAssign = false;
    }

    // Prevent duplicate entry
    if ($canAssign) {

        // Get departure and arrival times of the flight being assigned
        $newFlightQuery = mysqli_query($conn, "
            SELECT f.DepartureTime, da.ArrivalTime 
            FROM flight f 
            JOIN departure_arrival da ON f.FlightID = da.FlightID 
            WHERE f.FlightID = '$flightID'
        ");
        $newFlight = mysqli_fetch_assoc($newFlightQuery);
        $newDep = $newFlight['DepartureTime'];
        $newArr = $newFlight['ArrivalTime'];
    
        // Get all flights this crewmember is already assigned to
        $overlapCheckQuery = mysqli_query($conn, "
            SELECT f.FlightID, f.DepartureTime, da.ArrivalTime
            FROM has h
            JOIN flight f ON f.FlightID = h.flightID
            JOIN departure_arrival da ON f.FlightID = da.FlightID
            WHERE h.id = '$crewMemberID' AND f.FlightID != '$flightID'
        ");
    
        $conflictFound = false;
        while ($assignedFlight = mysqli_fetch_assoc($overlapCheckQuery)) {
            $assignedDep = $assignedFlight['DepartureTime'];
            $assignedArr = $assignedFlight['ArrivalTime'];
    
            if (
                ($newDep < $assignedArr) && ($newArr > $assignedDep)
            ) {
                $conflictFound = true;
                $conflictingFlightID = $assignedFlight['FlightID'];
                break;
            }
        }

        // Overlap occurred
        if ($conflictFound) {
            $assignMsg = "Cannot assign crew member to flight $flightID: Overlapping with flight $conflictingFlightID.";
            $canAssign = false;
        }
    }
    
}

// Unassign crewmember
if (isset($_POST['unassignCrew'])) {
    $flightID = $_POST['flightID'];
    $crewMemberID = $_POST['crewMemberID'];
    $delete = mysqli_query($conn, "DELETE FROM has WHERE flightID = '$flightID' AND id = '$crewMemberID'");
    $assignMsg = $delete ? "Crewmember $crewMemberID unassigned successfully!" : "Failed to unassign crewmember.";
}

// Get all uncompleted flights to display
$flights = mysqli_query($conn, "SELECT * FROM flight WHERE FlightStatus != 'Completed'");

// Get all available crewmembers
$crewMembersResult = mysqli_query($conn, "SELECT c.id, u.FirstName, u.LastName, c.Role FROM Crewmember c JOIN Users u ON u.id = c.userID");
$allCrewMembers = [];
while ($row = mysqli_fetch_assoc($crewMembersResult)) {$allCrewMembers[] = $row;}

// Re-get crews assignments
$assignedCrew = [];
$result = mysqli_query($conn, "SELECT * FROM CrewAssignments");

while ($row = mysqli_fetch_array($result)) {
    $fid = $row['flightID'];
    $assignedCrew[$fid]['crewFirstNames'][] = $row['FirstName'];
    $assignedCrew[$fid]['crewLastNames'][] = $row['LastName'];
    $assignedCrew[$fid]['crewRoles'][] = ucfirst($row['Role']);
    $assignedCrew[$fid]['crewIDs'][] = $row['crewID'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroMate - Admin Assign Crew</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #e0f0ff, #cfe9ff, #b9dbff);
            font-family: 'Segoe UI', sans-serif;
            padding-top: 80px;
            padding-bottom: 80px;
        }
        .brand {
            font-weight: 700;
            font-size: 2rem;
            color: #007bff;
            text-align: left;
            margin-bottom: 20px;
        }
        .container {
            max-width: 900px;
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
        .flight-item {
            border: 1px solid #ddd;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        .flight-item h5 {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .flight-item p {
            margin-bottom: 10px;
        }
        .alert-info {
            margin-bottom: 30px;
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
        <a href="dashboard.php?id=<?php echo $_SESSION['user'] ?? ''; ?>" style="text-decoration: none;">
            AeroMate <i class="fas fa-plane-departure"></i>
        </a>
    </div>
    <div class="title">Assign Crewmembers</div>

    <!-- Assignment Message -->
    <?php if (isset($assignMsg)): ?>
        <div class="alert alert-info text-center">
            <?php echo $assignMsg; ?>
        </div>
    <?php endif; ?>

    <!-- Display each flight w/ assigned crew -->
    <div class="list-group">
        <?php while ($flight = mysqli_fetch_assoc($flights)): ?>
            <div class="flight-item">
                <h5><strong>Flight ID: <?php echo $flight['FlightID']; ?></strong></h5>
                <p><strong>Route:</strong> <?php echo $flight['OriginLocation']; ?> to <?php echo $flight['DestinationLocation']; ?></p>
                <p><strong>Departure Time:</strong> <?php echo $flight['DepartureTime']; ?></p>
                <p><strong>Assigned Aircraft:</strong> <?php echo $flight['AssignedAircraft']; ?></p>
                <p><strong>Status:</strong> <?php echo $flight['FlightStatus']; ?></p>

                <!-- Check for missing roles -->
                <?php
                $missingRoles = [];
                if (isset($assignedCrew[$flight['FlightID']])) {
                    if (!in_array('Pilot', $assignedCrew[$flight['FlightID']]['crewRoles'])) $missingRoles[] = 'Pilot';
                    if (!in_array('Copilot', $assignedCrew[$flight['FlightID']]['crewRoles'])) $missingRoles[] = 'Copilot';
                    if (count(array_keys($assignedCrew[$flight['FlightID']]['crewRoles'], 'Flight Attendant')) < 2) $missingRoles[] = 'Flight Attendant';
                } else $missingRoles = ['Pilot', 'Copilot', 'Flight Attendant'];
                
                // Display missing roles
                if (count($missingRoles) > 0): ?>
                    <p><strong>Missing Crew:</strong> <?php echo implode(', ', $missingRoles); ?></p>
                <?php else: ?>
                    <p><strong>All crew members assigned!</strong></p>
                <?php endif; ?>

                <!-- List assigned crew members -->
                <h6><strong>Assigned Crew:</strong></h6>
                <ul class="list-group mb-3">
                    <?php
                    if (isset($assignedCrew[$flight['FlightID']])) {
                        foreach ($assignedCrew[$flight['FlightID']]['crewFirstNames'] as $key => $firstName) {
                            $lastName = $assignedCrew[$flight['FlightID']]['crewLastNames'][$key];
                            $role = $assignedCrew[$flight['FlightID']]['crewRoles'][$key];
                            $crewMemberID = $assignedCrew[$flight['FlightID']]['crewIDs'][$key];
                            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                    $firstName $lastName ($role) 
                                    <form method='post' style='display:inline;'>
                                        <input type='hidden' name='flightID' value='{$flight['FlightID']}'>
                                        <input type='hidden' name='crewMemberID' value='$crewMemberID'>
                                        <button type='submit' name='unassignCrew' class='btn btn-danger btn-sm ms-auto'>Unassign</button>
                                    </form>
                                </li>";
                        }
                    }
                    ?>
                </ul>

                <!-- Crew Assignment Form -->
                <form method="post" action="admin_assign.php">
                    <input type="hidden" name="flightID" value="<?php echo $flight['FlightID']; ?>">
                    <input type="hidden" name="departureTime" value="<?php echo $flight['DepartureTime']; ?>">
                    
                    <!-- Select Crew Member -->
                    <div class="mb-3">
                        <select class="form-control" name="crewMemberID" required>
                            <option value="" disabled selected>Select Crew Member</option>
                            <?php foreach ($allCrewMembers as $crew): ?>
                                <?php if (in_array(ucfirst($crew['Role']), $missingRoles)): ?>
                                    <option value="<?= $crew['id'] ?>">
                                        <?= $crew['FirstName'] . ' ' . $crew['LastName'] ?> (<?= ucfirst($crew['Role']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="assignCrew" class="btn btn-primary w-100">Assign Crew Member</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>