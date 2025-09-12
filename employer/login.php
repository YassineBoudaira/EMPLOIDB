<?php
// Employer-specific login redirect
// This page redirects to the main login system

// Include configuration
include '../include/config.php';
include '../include/sess.php';

// Check if user is already logged in as employer
if (Security::isLoggedIn() && $_SESSION['role'] === 'employer') {
    header('Location: dashboard.php');
    exit();
}

// Redirect to main login page with employer context
$redirect_url = '../login.php?role=employer';
header('Location: ' . $redirect_url);
exit();
?>
