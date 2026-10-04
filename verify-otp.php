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

    header('Location: login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| HELPER: CLEAR OTP SESSION
|--------------------------------------------------------------------------
*/

function clearOtpSession(): void
{
    unset(
        $_SESSION['otp_hash'],
        $_SESSION['otp_expires'],
        $_SESSION['otp_attempts'],
        $_SESSION['otp_user_id'],
        $_SESSION['otp_username'],
        $_SESSION['otp_remember']
    );
}


/*
|--------------------------------------------------------------------------
| CHECK PENDING OTP LOGIN
|--------------------------------------------------------------------------
|
| These values are created by auth.php after the user's
| email/password have been successfully verified.
|
*/

if (
    !isset($_SESSION['otp_hash']) ||
    !isset($_SESSION['otp_expires']) ||
    !isset($_SESSION['otp_user_id']) ||
    !isset($_SESSION['otp_username'])
) {

    header(
        'Location: login.php?error=otp_session'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK OTP EXPIRATION
|--------------------------------------------------------------------------
*/

$otpExpires = $_SESSION['otp_expires'];


/*
|--------------------------------------------------------------------------
| INVALID EXPIRATION VALUE
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($otpExpires)
) {

    clearOtpSession();

    header(
        'Location: login.php?error=otp_session'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| OTP EXPIRED
|--------------------------------------------------------------------------
*/

if (time() >= (int)$otpExpires) {

    clearOtpSession();

    header(
        'Location: login.php?otp_error=expired'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| INITIALIZE OTP ATTEMPTS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['otp_attempts'])) {

    $_SESSION['otp_attempts'] = 0;
}


/*
|--------------------------------------------------------------------------
| MAXIMUM OTP ATTEMPTS
|--------------------------------------------------------------------------
*/

$maxAttempts = 5;


if (
    (int)$_SESSION['otp_attempts'] >= $maxAttempts
) {

    clearOtpSession();

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
| VALIDATE OTP FORMAT
|--------------------------------------------------------------------------
|
| Must be exactly 6 numeric digits.
|
*/

if (
    !preg_match('/^\d{6}$/', $otp)
) {

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

$otpHash =
    (string)$_SESSION['otp_hash'];


$isValidOtp =
    password_verify(
        $otp,
        $otpHash
    );


/*
|--------------------------------------------------------------------------
| INVALID OTP
|--------------------------------------------------------------------------
*/

if (!$isValidOtp) {

    $_SESSION['otp_attempts'] =
        (int)$_SESSION['otp_attempts'] + 1;


    /*
    |--------------------------------------------------------------------------
    | MAXIMUM ATTEMPTS REACHED
    |--------------------------------------------------------------------------
    */

    if (
        (int)$_SESSION['otp_attempts'] >= $maxAttempts
    ) {

        clearOtpSession();

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
}


/*
|--------------------------------------------------------------------------
| OTP IS VALID
|--------------------------------------------------------------------------
|
| Save the pending login information BEFORE
| regenerating the session.
|
*/

$userId =
    (int)$_SESSION['otp_user_id'];


$username =
    trim(
        (string)$_SESSION['otp_username']
    );


$remember =
    !empty($_SESSION['otp_remember']);


/*
|--------------------------------------------------------------------------
| BASIC USER DATA VALIDATION
|--------------------------------------------------------------------------
*/

if (
    $userId <= 0 ||
    $username === ''
) {

    clearOtpSession();

    header(
        'Location: login.php?error=otp_session'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| REGENERATE SESSION ID
|--------------------------------------------------------------------------
|
| This prevents session fixation after successful
| authentication.
|
*/

if (!session_regenerate_id(true)) {

    clearOtpSession();

    header(
        'Location: login.php?error=session'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CREATE AUTHENTICATED SESSION
|--------------------------------------------------------------------------
*/

$_SESSION['logged_in'] = true;

$_SESSION['user_id'] =
    $userId;

$_SESSION['username'] =
    $username;


/*
|--------------------------------------------------------------------------
| OPTIONAL: LOGIN TIME
|--------------------------------------------------------------------------
*/

$_SESSION['login_time'] =
    time();


/*
|--------------------------------------------------------------------------
| CLEAR OTP DATA
|--------------------------------------------------------------------------
|
| This is extremely important.
|
| Once the OTP has been successfully used,
| it cannot be reused.
|
*/

clearOtpSession();


/*
|--------------------------------------------------------------------------
| REMEMBER ME
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| This is compatible with your current test system.
|
| For production, we should replace this with a
| random server-side remember token.
|
*/

$cookieOptions = [

    'expires' =>
        time() + (86400 * 30),

    'path' =>
        '/',

    'secure' =>
        (
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        ),

    'httponly' =>
        true,

    'samesite' =>
        'Lax'
];


if ($remember) {

    setcookie(
        'remember_user',
        $username,
        $cookieOptions
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

            'path' =>
                '/',

            'secure' =>
                (
                    !empty($_SERVER['HTTPS']) &&
                    $_SERVER['HTTPS'] !== 'off'
                ),

            'httponly' =>
                true,

            'samesite' =>
                'Lax'
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