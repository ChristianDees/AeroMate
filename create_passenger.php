<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php'); 

$email = isset($_GET['email']) ? $_GET['email'] : "";
if (isset($_POST['Submit'])) {
    // Get form data 
    $id = isset($_POST['id']) ? $_POST['id'] : "0"; // CHANGE THIS TO NOTHING BECUZ IT NEEDS TO BE AUTOINCRIMENTED
    $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : "";
    $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : "";
    $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : "";
    $passportNumber = isset($_POST['passportNumber']) ? $_POST['passportNumber'] : "";
    $email = isset($_POST['email']) ? $_POST['email'] : $email;
    $phone = isset($_POST['phone']) ? $_POST['phone'] : "";
    $frequentFlyer = isset($_POST['frequentFlyer']) ? 1 : 0;

    // Procedure statement for insertion
    $callProcedure = "CALL AddPassenger(?, ?, ?, ?, ?, ?, ?, ?)";

    // Bind procedure
    if ($stmt = $conn->prepare($callProcedure)) {
        $stmt->bind_param(
            "issssssi",
            $id,
            $firstName,
            $lastName,
            $nationality,
            $passportNumber,
            $email,
            $phone,
            $frequentFlyer
        );
        
        // Execute the statement and alert
        if ($stmt->execute()) {
          $msg = "Successfully created passenger!";
          echo "<script>
              alert(" . json_encode($msg) . ");
              window.location.href = 'home.php?id=" . $id . "';
          </script>";
          exit; 
        } else {
            $msg = "Error executing procedure: " . $stmt->error;
            echo "<script>alert(" . json_encode($msg) . ");</script>";
        }
        $stmt->close();
    } else $msg = "Error preparing statement: " . $conn->error;

    // Show result
    echo "<script>alert(" . json_encode($msg) . ");</script>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Create Account</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="styles.css">

</head>
<body>
  <div class="account-create-container">
    <!-- Company logo + title -->
    <div class="brand">
      <a href="index.php" style="text-decoration: none;">
          AeroMate <i class="fas fa-plane-departure"></i>
      </a>
    </div>
    <div class="title">Create Account</div>
    <form action="create_passenger.php" method="post"> 
      <div class="row">
        <!-- Left Column -->
        <div class="col-md-6 form-column">
          <!-- First Name -->
          <div class="mb-4 input-icon">
            <label for="firstName" class="form-label">
              <i class="fas fa-user"></i> 
              First Name
            </label>
            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="John" required>
          </div>

          <!-- Nationality -->
          <div class="mb-4 input-icon">
            <label for="nationality" class="form-label">
              <i class="fas fa-globe"></i> 
              Nationality
            </label>
            <input type="text" class="form-control" id="nationality" name="nationality" placeholder="Enter your nationality" required>
          </div>

          <!-- Email -->
          <div class="mb-4 input-icon">
            <label for="email" class="form-label">
              <i class="fas fa-envelope"></i> 
              Email Address
            </label>
            <input type="email" class="form-control" id="email"  name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="johnDoe@example.com" required>
          </div>
        </div>

        <!-- Last Name -->
        <div class="col-md-6 form-column">
          <div class="mb-4 input-icon">
            <label for="lastName" class="form-label">
              <i class="fas fa-user"></i> 
              Last Name
            </label>
            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Doe" required>
          </div>

          <!-- Passport -->
          <div class="mb-4 input-icon">
            <label for="passportNumber" class="form-label">
              <i class="fas fa-passport"></i> 
              Passport Number
            </label>
            <input type="text" class="form-control" id="passportNumber" name="passportNumber" placeholder="123456789" required>
          </div>

          <!-- Phone -->
          <div class="mb-4 input-icon">
            <label for="phone" class="form-label">
              <i class="fas fa-phone"></i> 
              Phone Number
            </label>
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="(123) 456-7890" required>
          </div>
        </div>
      </div>

      <!-- Frequent Flyer -->
      <div class="mb-4">
        <label for="frequentFlyer" class="form-label">
          <input type="checkbox" id="frequentFlyer" name="frequentFlyer" class="form-check-input">
          <span class="form-check-label">I am a frequent flyer</span>
        </label>
      </div>

      <button type="submit" name="Submit" class="btn btn-primary w-100">Create Account</button>
    </form>
  </div>
</body>
</html>