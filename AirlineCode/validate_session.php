<!--
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
-->

<?php
// Chcek if logged in, if not redirect to log in page
if (!isset($_SESSION['loggedIn']) || empty($_SESSION['loggedIn'])) { 
    echo "<script>
            alert('To access this page, please log in');
            window.location.href = 'index.php';
          </script>";
    exit(); 
} 
?>
