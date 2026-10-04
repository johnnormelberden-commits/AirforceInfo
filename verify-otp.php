<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| REQUIRED FILE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/db.php';


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| OTP
|--------------------------------------------------------------------------
*/

$otp = trim(
    (string)($_POST['otp'] ?? '')
);


/*
|--------------------------------------------------------------------------
| VALIDATE OTP FORMAT
|--------------------------------------------------------------------------
*/

if (!preg_match('/^\d{6}$/', $otp)) {

    header(
        'Location: login.php?otp=1&otp_error=invalid'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK OTP SESSION
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['otp_hash']) ||
    empty($_SESSION['otp_expires']) ||
    empty($_SESSION['otp_user_id']) ||
    empty($_SESSION['otp_username'])
) {

    /*
     * There is no valid login waiting for OTP.
     */

    header(
        'Location: login.php?error=otp_session'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK EXPIRATION
|--------------------------------------------------------------------------
*/

if (
    time() >
    (int)$_SESSION['otp_expires']
) {

    unset(
        $_SESSION['otp_hash'],
        $_SESSION['otp_expires'],
        $_SESSION['otp_attempts'],
        $_SESSION['otp_user_id'],
        $_SESSION['otp_username'],
        $_SESSION['otp_remember']
    );


    header(
        'Location: login.php?otp_error=expired'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| OTP ATTEMPTS
|--------------------------------------------------------------------------
*/

$attempts =
    (int)($_SESSION['otp_attempts'] ?? 0);


/*
|--------------------------------------------------------------------------
| MAXIMUM ATTEMPTS
|--------------------------------------------------------------------------
*/

if ($attempts >= 5) {

    unset(
        $_SESSION['otp_hash'],
        $_SESSION['otp_expires'],
        $_SESSION['otp_attempts'],
        $_SESSION['otp_user_id'],
        $_SESSION['otp_username'],
        $_SESSION['otp_remember']
    );


    header(
        'Location: login.php?otp_error=attempts'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

if (
    !password_verify(
        $otp,
        (string)$_SESSION['otp_hash']
    )
) {

    $_SESSION['otp_attempts'] =
        $attempts + 1;


    /*
    |--------------------------------------------------------------------------
    | CHECK IF THIS WAS FINAL ATTEMPT
    |--------------------------------------------------------------------------
    */

    if (
        $_SESSION['otp_attempts'] >= 5
    ) {

        unset(
            $_SESSION['otp_hash'],
            $_SESSION['otp_expires'],
            $_SESSION['otp_attempts'],
            $_SESSION['otp_user_id'],
            $_SESSION['otp_username'],
            $_SESSION['otp_remember']
        );


        header(
            'Location: login.php?otp_error=attempts'
        );

        exit;
    }


    header(
        'Location: login.php?otp=1&otp_error=invalid'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| OTP IS CORRECT
|--------------------------------------------------------------------------
*/

$userId =
    (int)$_SESSION['otp_user_id'];

$username =
    (string)$_SESSION['otp_username'];

$remember =
    !empty($_SESSION['otp_remember']);


/*
|--------------------------------------------------------------------------
| REGENERATE SESSION ID
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED SESSION
|--------------------------------------------------------------------------
*/

$_SESSION['logged_in'] =
    true;

$_SESSION['user_id'] =
    $userId;

$_SESSION['username'] =
    $username;


/*
|--------------------------------------------------------------------------
| OPTIONAL REMEMBER FLAG
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This does NOT automatically authenticate the user.
|
| It only remembers that the user selected
| "Remember me" during this login.
|
|--------------------------------------------------------------------------
*/

$_SESSION['remember_me'] =
    $remember;


/*
|--------------------------------------------------------------------------
| REMOVE OTP DATA
|--------------------------------------------------------------------------
*/

unset(
    $_SESSION['otp_hash'],
    $_SESSION['otp_expires'],
    $_SESSION['otp_attempts'],
    $_SESSION['otp_user_id'],
    $_SESSION['otp_username'],
    $_SESSION['otp_remember']
);


/*
|--------------------------------------------------------------------------
| OPTIONAL REMEMBER COOKIE
|--------------------------------------------------------------------------
|
| DO NOT use this cookie by itself to log somebody in.
|
| We only store the username for convenience.
|
|--------------------------------------------------------------------------
*/

if ($remember) {

    setcookie(
        'remember_user',
        $username,
        [
            'expires' =>
                time() + (30 * 24 * 60 * 60),

            'path' => '/',

            'secure' =>
                isset($_SERVER['HTTPS']) &&
                $_SERVER['HTTPS'] !== 'off',

            'httponly' => true,

            'samesite' => 'Lax'
        ]
    );

} else {

    setcookie(
        'remember_user',
        '',
        [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' =>
                isset($_SERVER['HTTPS']) &&
                $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}


/*
|--------------------------------------------------------------------------
| LOGIN COMPLETE
|--------------------------------------------------------------------------
*/

header(
    'Location: index.php'
);

exit;