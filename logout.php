<?php

session_start();

/*
 * ==========================================================
 * LOGOUT
 * ==========================================================
 *
 * PostgreSQL/PDO is used by the application.
 * No MySQLi connection is needed here.
 */


/*
 * Destroy session
 */
session_unset();
session_destroy();


/*
 * Remove "Remember Me" cookie
 */
setcookie(
    "remember_user",
    "",
    time() - 3600,
    "/"
);


/*
 * Redirect to login page
 */
header("Location: login.php");
exit;

?>