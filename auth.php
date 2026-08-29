<?php

session_start();

/*
 * ==========================================================
 * DATABASE CONNECTION
 * ==========================================================
 *
 * The PostgreSQL connection is handled by db.php.
 *
 * db.php uses these Render Environment Variables:
 *
 * DB_HOST
 * DB_PORT
 * DB_NAME
 * DB_USER
 * DB_PASSWORD
 *
 * ==========================================================
 */

require_once 'db.php';


/*
 * ==========================================================
 * GET LOGIN FORM VALUES
 * ==========================================================
 */

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$remember = isset($_POST['remember']);


/*
 * ==========================================================
 * CHECK USERNAME
 * ==========================================================
 */

try {

    $stmt = $connection->prepare(
        "SELECT id, username, password
         FROM users
         WHERE username = :username
         LIMIT 1"
    );

    $stmt->execute([
        ':username' => $username
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    /*
     * ======================================================
     * VERIFY PASSWORD
     * ======================================================
     */

    if ($user && password_verify($password, $user['password'])) {

        /*
         * Login successful
         */
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];


        /*
         * ==================================================
         * REMEMBER ME
         * ==================================================
         */

        if ($remember) {

            setcookie(
                "remember_user",
                $user['username'],
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );

        }


        /*
         * ==================================================
         * REDIRECT AFTER SUCCESSFUL LOGIN
         * ==================================================
         */

        header("Location: index.php");
        exit;

    }


    /*
     * ======================================================
     * INVALID USERNAME OR PASSWORD
     * ======================================================
     */

    header("Location: login.php?error=1");
    exit;


} catch (PDOException $e) {

    /*
     * Do not expose database error details.
     */
    die("Unable to process login.");

}