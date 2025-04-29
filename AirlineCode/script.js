/*
Authors: Lauren Alvarado, Christian Dees, Yashar Keyvan, and Aitiana Mondragon
CS 4342
April 26, 2025
*/

function validatePasswords() {
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirmPassword").value;
    if (password !== confirm) {
      alert("Passwords do not match.");
      return false;
    }
    return true;
}