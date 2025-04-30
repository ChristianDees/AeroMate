<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
session_start();
require_once('config.php'); 

// Get users type
if (isset($_GET['type']) || !empty($_SESSION['userType'])) {
  $userType = isset($_GET['type']) ? $_GET['type'] : $_SESSION['userType'];

    // Get airlines for dropdown
    $airlineSql = "SELECT DISTINCT AirlineAffiliation FROM crewmember";
    $airlineResult = $conn->query($airlineSql);

    // Get possible roles for dropdown
    $roleSql = "SELECT DISTINCT Role FROM crewmember";
    $roleResult = $conn->query($roleSql);


    // Get userID
    if (isset($_GET['id'])) {
        require_once('validate_session.php');
        $id = $_GET['id'];
      
        // Get user data
        $sql = "SELECT * FROM users WHERE id = '$id'";
        $result = $conn->query($sql);
        $row = mysqli_fetch_array($result);
        
        // Validate logged in user with requested user
        if ($_SESSION['user'] != $id) {die("Access Denied");}
    
        // Get user specific data
        if ($userType == 'passenger') {
            $passengerSql = "SELECT * FROM passenger WHERE userID = '$id'";
            $passengerResult = $conn->query($passengerSql);
            $passengerRow = mysqli_fetch_array($passengerResult);
        } elseif ($userType == 'crewmember') {
            $crewmemberSql = "SELECT * FROM crewmember WHERE userID = '$id'";
            $crewmemberResult = $conn->query($crewmemberSql);
            $crewmemberRow = mysqli_fetch_array($crewmemberResult);
        }
    }
} else {
    die('Invalid user type.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AeroMate - Create Account</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <script src="script.js"></script>
</head>
<body>
  <div class="account-create-container">
    <div class="brand">
      <a href="<?= $_SESSION['loggedIn'] ? 'dashboard.php?id=' . ($_SESSION['user'] ?? '') : 'index.php'; ?>" style="text-decoration: none;">
          AeroMate <i class="fas fa-plane-departure"></i>
      </a>
    </div>
    <div class="title">
      <?= $_SESSION['loggedIn'] ? 'My Account' : 'Create Account'; ?>
    </div>

    <!-- Account form -->
    <form action="update_account.php" method="post" onsubmit="return validatePasswords()">
      <div class="row">
      <input type="hidden" name="id" value="<?php echo $id ?? ''; ?>">
      <input type="hidden" name="type" value="<?php echo $userType ?? ''; ?>">
        <!-- Left Column -->
        <div class="col-md-6 form-column">
          <!-- First Name -->
          <div class="mb-4 input-icon">
            <label for="firstName" class="form-label">
              <i class="fas fa-user"></i> 
              First Name
            </label>
            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo $row['FirstName'] ?? ''; ?>" placeholder="John" required>
          </div>

          <!-- Email -->
          <div class="mb-4 input-icon">
            <label for="email" class="form-label">
              <i class="fas fa-envelope"></i> 
              Email Address
            </label>

            <input 
              type="<?= ($userType == 'admin') ? 'text' : 'email'; ?>" 
              class="form-control" 
              id="email" 
              name="email" 
              value="<?= $row['Email'] ?? ''; ?>" 
              placeholder="johnDoe@example.com" 
              required
            >
          </div>

          <!-- Passport Number -->
          <?php if ($userType == 'passenger'): ?>
            <div class="mb-4 input-icon">
              <label for="email" class="form-label">
                <i class="fa-solid fa-passport"></i>
                Passport Number
              </label>
              <input type="text" class="form-control" id="passportNumber" name="passportNumber" value="<?php echo $passengerRow['PassportNumber'] ?? ''; ?>" placeholder="1234567890" required>
            </div>
          <?php endif; ?>
          <!-- Password -->
          <div class="mb-4 input-icon">
            <label for="password" class="form-label">
              <i class="fas fa-key"></i> 
              Password
            </label>
            <input type="text" class="form-control" id="password" name="password" value="<?php echo $row['Password'] ?? ''; ?>" placeholder="myPassword123" required>
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
            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo $row['LastName'] ?? ''; ?>" placeholder="Doe" required>
          </div>

          <!-- Airline Affiliation Dropdown -->
          <?php if ($userType == 'crewmember'): ?>
              <div class="mb-4 input-icon">
                  <label for="airlineAffiliation" class="form-label">
                      <i class="fas fa-building"></i> 
                      Airline Affiliation
                  </label>
                  <select class="form-control" id="airlineAffiliation" name="airlineAffiliation" required>
                      <option value="">Select Airline</option>
                      
                      <?php 
                      // Check if crewmemberRow is set and contains a value for 'AirlineAffiliation'
                      if (isset($crewmemberRow) && isset($crewmemberRow['AirlineAffiliation'])):
                          // If so, use it to set the selected option
                          while ($airline = mysqli_fetch_assoc($airlineResult)): ?>
                              <option value="<?php echo $airline['AirlineAffiliation'] ?? ''; ?>" 
                                  <?php echo ($airline['AirlineAffiliation'] == $crewmemberRow['AirlineAffiliation']) ? 'selected' : ''; ?>>
                                  <?php echo $airline['AirlineAffiliation'] ?: 'Select an Airline'; ?>
                              </option>
                          <?php endwhile;
                      else:
                          // If crewmemberRow is not set, just display the default 'Select an Airline' options
                          while ($airline = mysqli_fetch_assoc($airlineResult)): ?>
                              <option value="<?php echo $airline['AirlineAffiliation'] ?? ''; ?>">
                                  <?php echo $airline['AirlineAffiliation'] ?: 'Select an Airline'; ?>
                              </option>
                          <?php endwhile;
                      endif;
                      ?>
                  </select>
              </div>
          <?php endif; ?>


          <!-- Phone -->
          <?php if ($userType == 'passenger'): ?>
            <div class="mb-4 input-icon">
                <label for="phone" class="form-label">
                <i class="fas fa-phone"></i> 
                Phone Number
                </label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $passengerRow['Phone'] ?? ''; ?>" placeholder="(123) 456-7890" required>
            </div>
          <?php endif; ?>

          <!-- Frequent Flyer Display -->
          <?php if ($userType == 'passenger'): ?>
              <div class="mb-4 input-icon">
                  <label class="form-label">
                      <i class="fas fa-check-circle"></i> 
                      Frequent Flyer
                  </label>
                  <p class="form-control-plaintext">
                      <?php
                          if (isset($passengerRow)) {
                            echo $passengerRow['FrequentFlyer'] == 1 ? 'You\'re a frequent flyer!' : 'Fly more to earn frequent flyer status.';
                          } else {
                              echo 'No';
                          }
                      ?>
                  </p>
              </div>
          <?php endif; ?>


          <!-- Confirm Password -->
          <div class="mb-4 input-icon">
            <label for="confirmPassword" class="form-label">
              <i class="fas fa-key"></i> 
              Confirm Password
            </label>
            <input type="text" class="form-control" id="confirmPassword" name="confirmPassword" value="<?php echo $row['Password'] ?? ''; ?>" placeholder="myPassword123" required>
          </div>

        </div>
        
      </div>

      <!-- Role Dropdown -->
      <?php if ($userType == 'crewmember'): ?>
          <div class="mb-4 input-icon">
              <label for="role" class="form-label">
                  <i class="fas fa-briefcase"></i> 
                  Role
              </label>
              <select class="form-control" id="role" name="role" required>
                  <option value="">Select Role</option>
                  
                  <?php 
                  // Check if crewmemberRow is set and contains a value for 'Role'
                  if (isset($crewmemberRow) && isset($crewmemberRow['Role'])):
                      // If so, use it to set the selected option
                      while ($role = mysqli_fetch_assoc($roleResult)): ?>
                          <option value="<?php echo $role['Role'] ?? ''; ?>" 
                              <?php echo ($role['Role'] == $crewmemberRow['Role']) ? 'selected' : ''; ?>>
                              <?php echo $role['Role'] ?: 'Select a Role'; ?>
                          </option>
                      <?php endwhile;
                  else:
                      // If crewmemberRow is not set, just display the default 'Select a Role' options
                      while ($role = mysqli_fetch_assoc($roleResult)): ?>
                          <option value="<?php echo $role['Role'] ?? ''; ?>">
                              <?php echo $role['Role'] ?: 'Select a Role'; ?>
                          </option>
                      <?php endwhile;
                  endif;
                  ?>
              </select>
          </div>
      <?php endif; ?>


    <button type="submit" class="btn btn-primary w-100">
        <?= $_SESSION['loggedIn'] ? 'Update Account' : 'Create Account'; ?>
    </button>

    <!-- Show the Delete Account Button only if the user is logged in -->
    <?php if ($_SESSION['loggedIn']): ?>
        <a href="delete_account.php?id=<?php echo $id; ?>" class="btn btn-danger w-100 mt-3" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
            Delete Account
        </a>
    <?php endif; ?>

    
    </form>
  </div>
</body>
</html>
