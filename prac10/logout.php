<?php
require_once 'config.php';

// Only process logout if user is logged in
if (isLoggedIn()) {
    // Log the logout action
    if (isset($_SESSION['username'])) {
        logLoginAttempt($_SESSION['username'] . ' (logout)', true);
    }
    
    // Call the logout function from config.php
    logout();
}

// Redirect to login page with logout message
header('Location: login.php?logout=1');
exit();
?>