<?php
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';

// Destroy advertiser session
if (isset($_SESSION['advertiser_id'])) {
    unset($_SESSION['advertiser_id']);
    unset($_SESSION['advertiser_company']);
    unset($_SESSION['advertiser_email']);
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
?>
