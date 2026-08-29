<?php

session_start();

// Destroy the current session
session_unset();
session_destroy();

// Remove "Remember Me" cookie
setcookie(
    "remember_user",
    "",
    time() - 3600,
    "/"
);

// Redirect to login page
header("Location: login.php");
exit;

?>