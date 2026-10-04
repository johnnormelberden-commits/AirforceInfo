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
| ERROR HANDLING
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '0');
ini_set('log_errors', '1');

error_reporting(E_ALL);


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

try {

    require_once __DIR__ . '/db.php';

} catch (Throwable $e) {

    error_log(
        'CMO OTP DATABASE ERROR: ' .
        $e->getMessage()
    );

    header(
        'Location: login.php?error=server'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {

    header('Location: login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| GET OTP
|--------------------------------------------------------------------------
*/

$otp =
    trim(
        (string) (
            $_POST['otp'] ?? ''
        )
    );


/*
|--------------------------------------------------------------------------
| VALIDATE OTP
|--------------------------------------------------------------------------
*/

if (
    !preg_match(
        '/^[0-9]{6}$/',
        $otp
    )
) {

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
    (int) $_SESSION['otp_expires']
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
| ATTEMPT LIMIT
|--------------------------------------------------------------------------
*/

$attempts =
    (int) (
        $_SESSION['otp_attempts'] ?? 0
    );


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

$otpValid =
    password_verify(
        $otp,
        (string) $_SESSION['otp_hash']
    );


if (!$otpValid) {

    $attempts++;

    $_SESSION['otp_attempts'] =
        $attempts;


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


    header(
        'Location: login.php?otp=1&otp_error=invalid'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| OTP VERIFIED
|--------------------------------------------------------------------------
*/

$userId =
    (int) $_SESSION['otp_user_id'];


$username =
    (string) $_SESSION['otp_username'];


$remember =
    !empty(
        $_SESSION['otp_remember']
    );


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


$_SESSION['login_time'] =
    time();


$_SESSION['remember'] =
    $remember;


/*
|--------------------------------------------------------------------------
| REMOVE OTP SESSION DATA
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
| LOGIN COMPLETE
|--------------------------------------------------------------------------
*/

header(
    'Location: index.php'
);

exit;