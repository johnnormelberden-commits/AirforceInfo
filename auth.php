<?php

session_start();

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
|
| db.php handles the TiDB / MySQL PDO connection.
|
*/

require_once 'db.php';


/*
|--------------------------------------------------------------------------
| GET LOGIN FORM VALUES
|--------------------------------------------------------------------------
*/

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$remember = isset($_POST['remember']);


/*
|--------------------------------------------------------------------------
| BASIC VALIDATION
|--------------------------------------------------------------------------
*/

if ($username === '' || $password === '') {

    header("Location: login.php?error=1");
    exit;

}


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

try {

    /*
     * Find the user by username.
     *
     * Backticks around username are used because this
     * avoids possible conflicts with SQL keywords.
     */

    $stmt = $connection->prepare(
        "SELECT
            id,
            username,
            password
         FROM users
         WHERE `username` = :username
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

    if (
        $user &&
        isset($user['password']) &&
        password_verify(
            $password,
            $user['password']
        )
    ) {

        /*
         * ==================================================
         * REGENERATE SESSION ID
         * ==================================================
         *
         * Prevents session fixation after login.
         */

        session_regenerate_id(true);


        /*
         * ==================================================
         * SET LOGIN SESSION
         * ==================================================
         */

        $_SESSION['logged_in'] = true;

        $_SESSION['username'] = $user['username'];

        $_SESSION['user_id'] = (int) $user['id'];


        /*
         * ==================================================
         * REMEMBER ME COOKIE
         * ==================================================
         */

        if ($remember) {

            setcookie(
                "remember_user",
                $user['username'],
                [
                    'expires'  => time() + (86400 * 30),
                    'path'     => '/',
                    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );

        } else {

            /*
             * Remove an existing Remember Me cookie
             * when the user does not select Remember Me.
             */

            setcookie(
                "remember_user",
                "",
                [
                    'expires'  => time() - 3600,
                    'path'     => '/',
                    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );

        }


        /*
         * ==================================================
         * LOGIN SUCCESS
         * ==================================================
         */

        header("Location: index.php");
        exit;

    }


    /*
     * ======================================================
     * INVALID LOGIN
     * ======================================================
     */

    header("Location: login.php?error=1");
    exit;


} catch (PDOException $e) {

    /*
     * Do not expose database errors to users.
     *
     * During development, you can temporarily log
     * $e->getMessage() to your server logs.
     */

    error_log(
        "Login database error: " . $e->getMessage()
    );

    header("Location: login.php?error=db");
    exit;

}
