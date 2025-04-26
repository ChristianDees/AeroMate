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
  $id = $_GET['id'];
  $sql = "SELECT * FROM Passenger where id = '$id'";
  $result = $conn->query($sql);
  $row = mysqli_fetch_array($result);
} else die();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Modify Account</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="account-create-container">
    <!-- Company logo + title -->
    <div class="brand">
      <a href="home.php?id=<?php echo $_SESSION['user'] ?? ''; ?>" style="text-decoration: none;">
          AeroMate <i class="fas fa-plane-departure"></i>
      </a>
    </div>
    <div class="title">My Account</div>

    <!-- Profile form -->
    <form action="update_profile.php" method="post">
      <input type="hidden" name="id" id="id" value="<?php echo $row['id'] ?>">
      <div class="row">

        <!-- Left Column -->
        <div class="col-md-6 form-column">
          <!-- First Name -->
          <div class="mb-4 input-icon">
            <label for="firstName" class="form-label">
              <i class="fas fa-user"></i> 
              First Name
            </label>
            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo $row['FirstName'] ?>" required>
          </div>

          <!-- Nationality -->
          <div class="mb-4 input-icon">
            <label for="nationality" class="form-label">
              <i class="fas fa-globe"></i> 
              Nationality
            </label>
            <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo $row['Nationality'] ?>" required>
          </div>

          <!-- Email -->
          <div class="mb-4 input-icon">
            <label for="email" class="form-label">
              <i class="fas fa-envelope"></i> 
              Email Address
            </label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['Email'] ?>" required>
          </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-6 form-column">
          <!-- Last Name -->
          <div class="mb-4 input-icon">
            <label for="lastName" class="form-label">
              <i class="fas fa-user"></i>
              Last Name
            </label>
            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo $row['LastName'] ?>" required>
          </div>

          <!-- Passport -->
          <div class="mb-4 input-icon">
            <label for="passportNumber" class="form-label">
              <i class="fas fa-passport"></i> 
              Passport Number
            </label>
            <input type="text" class="form-control" id="passportNumber" name="passportNumber" value="<?php echo $row['PassportNumber'] ?>" required>
          </div>

          <!-- Phone -->
          <div class="mb-4 input-icon">
            <label for="phone" class="form-label">
              <i class="fas fa-phone"></i> 
              Phone Number
            </label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $row['Phone'] ?>" required>
          </div>
        </div>
      </div>

      <!-- Frequent Flyer -->
      <div class="mb-4">
        <label for="frequentFlyer" class="form-label">
          <input 
            type="checkbox" 
            id="frequentFlyer" 
            name="frequentFlyer"
            class="form-check-input"
            value="1"
            <?php if (!empty($row['FrequentFlyer']) && $row['FrequentFlyer'] == '1') echo 'checked'; ?>
          >
          <span class="form-check-label">I am a frequent flyer</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary w-100">Update Account</button>
    </form>
  </div>
</body>
</html>