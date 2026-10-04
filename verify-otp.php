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
| ONLY ALLOW POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: login.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK OTP SESSION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['otp_hash']) ||
    !isset($_SESSION['otp_expires']) ||
    !isset($_SESSION['otp_user_id']) ||
    !isset($_SESSION['otp_username'])
) {

    header(
        'Location: login.php?otp_error=session'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK OTP EXPIRATION
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($_SESSION['otp_expires']) ||
    time() >= (int)$_SESSION['otp_expires']
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
| INITIALIZE ATTEMPTS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['otp_attempts'])) {

    $_SESSION['otp_attempts'] = 0;
}


/*
|--------------------------------------------------------------------------
| MAXIMUM ATTEMPTS
|--------------------------------------------------------------------------
*/

if (
    (int)$_SESSION['otp_attempts'] >= 5
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


/*
|--------------------------------------------------------------------------
| GET OTP
|--------------------------------------------------------------------------
*/

$otp = trim(
    (string)($_POST['otp'] ?? '')
);


/*
|--------------------------------------------------------------------------
| OTP FORMAT
|--------------------------------------------------------------------------
*/

if (!preg_match(
    '/^\d{6}$/',
    $otp
)) {

    header(
        'Location: login.php?otp=1&otp_error=invalid'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

$valid = password_verify(
    $otp,
    (string)$_SESSION['otp_hash']
);


/*
|--------------------------------------------------------------------------
| OTP SUCCESS
|--------------------------------------------------------------------------
*/

if ($valid) {


    /*
    |--------------------------------------------------------------------------
    | GET USER DATA BEFORE SESSION REGENERATION
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
    |
    | Prevents session fixation.
    |
    */

    session_regenerate_id(true);


    /*
    |--------------------------------------------------------------------------
    | CREATE AUTHENTICATED SESSION
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
    | AUTHENTICATION TIME
    |--------------------------------------------------------------------------
    */

    $_SESSION['login_time'] =
        time();


    /*
    |--------------------------------------------------------------------------
    | CLEAR OTP DATA
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
    | REMEMBER ME
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | This cookie is created ONLY after successful OTP verification.
    |
    | This keeps it from bypassing the OTP step.
    |
    */

    if ($remember) {

        setcookie(
            'remember_user',
            $username,
            [
                'expires' =>
                    time() + (86400 * 30),

                'path' => '/',

                'secure' =>
                    (
                        !empty($_SERVER['HTTPS']) &&
                        $_SERVER['HTTPS'] !== 'off'
                    ),

                'httponly' => true,

                'samesite' => 'Lax'
            ]
        );

    } else {

        /*
        |--------------------------------------------------------------------------
        | DELETE OLD REMEMBER COOKIE
        |--------------------------------------------------------------------------
        */

        setcookie(
            'remember_user',
            '',
            [
                'expires' =>
                    time() - 3600,

                'path' => '/',

                'secure' =>
                    (
                        !empty($_SERVER['HTTPS']) &&
                        $_SERVER['HTTPS'] !== 'off'
                    ),

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
}


/*
|--------------------------------------------------------------------------
| INVALID OTP
|--------------------------------------------------------------------------
*/

$_SESSION['otp_attempts'] =
    (int)$_SESSION['otp_attempts'] + 1;


/*
|--------------------------------------------------------------------------
| MAXIMUM ATTEMPTS REACHED
|--------------------------------------------------------------------------
*/

if (
    (int)$_SESSION['otp_attempts'] >= 5
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


/*
|--------------------------------------------------------------------------
| INVALID OTP
|--------------------------------------------------------------------------
*/

header(
    'Location: login.php?otp=1&otp_error=invalid'
);

exit;