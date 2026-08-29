<?php
session_start();

// Log logout activity if user exists
$conn = new mysqli("localhost", "root", "", "airforce_info");
if (!$conn->connect_error && isset($_SESSION['username'])) {
    $user = $_SESSION['username'];

   // $log = $conn->prepare("INSERT INTO login_logs (username, action) VALUES (?, 'LOGOUT')");
   // $log->bind_param("s", $user);
    //$log->execute();
}

// Destroy session
session_unset();
session_destroy();

// Remove remember me cookie
setcookie("remember_user", "", time() - 3600, "/");

// Redirect to login page
header("Location: login.php");
exit;
