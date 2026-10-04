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
| DATABASE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/db.php';


/*
|--------------------------------------------------------------------------
| MAIL CONFIG
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/mail_config.php';


/*
|--------------------------------------------------------------------------
| PHPMailer
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| PHPMailer USE
|--------------------------------------------------------------------------
*/

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


/*
|--------------------------------------------------------------------------
| JSON RESPONSE HELPER
|--------------------------------------------------------------------------
*/

function jsonResponse(
    bool $success,
    string $message,
    array $extra = [],
    int $status = 200
): never {

    http_response_code($status);

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| AJAX REQUEST
|--------------------------------------------------------------------------
*/

$isAjax =
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower(
        (string)$_SERVER['HTTP_X_REQUESTED_WITH']
    ) === 'xmlhttprequest';


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    if ($isAjax) {

        jsonResponse(
            false,
            'Invalid request method.',
            [],
            405
        );

    }

    header('Location: login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| GET LOGIN DATA
|--------------------------------------------------------------------------
*/

$username = trim(
    (string)($_POST['username'] ?? '')
);


$password = (string)(
    $_POST['password'] ?? ''
);


$remember =
    isset($_POST['remember']) &&
    $_POST['remember'] === '1';


/*
|--------------------------------------------------------------------------
| EMPTY INPUT
|--------------------------------------------------------------------------
*/

if ($username === '' || $password === '') {

    if ($isAjax) {

        jsonResponse(
            false,
            'Please enter your email address and password.',
            [],
            400
        );

    }

    header('Location: login.php?error=empty');

    exit;
}


/*
|--------------------------------------------------------------------------
| EMAIL VALIDATION
|--------------------------------------------------------------------------
*/

if (!filter_var(
    $username,
    FILTER_VALIDATE_EMAIL
)) {

    if ($isAjax) {

        jsonResponse(
            false,
            'Please enter a valid email address.',
            [],
            400
        );

    }

    header('Location: login.php?error=invalid_email');

    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE AUTHENTICATION
|--------------------------------------------------------------------------
*/

try {

    $stmt = $connection->prepare(
        "SELECT
            id,
            username,
            password
         FROM users
         WHERE username = :username
         LIMIT 1"
    );


    $stmt->execute(
        [
            ':username' => $username
        ]
    );


    $user =
        $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | INVALID LOGIN
    |--------------------------------------------------------------------------
    */

    if (!$user) {

        if ($isAjax) {

            jsonResponse(
                false,
                'Invalid email address or password.',
                [],
                401
            );

        }

        header('Location: login.php?error=invalid_login');

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD
    |--------------------------------------------------------------------------
    |
    | CURRENT TEST SYSTEM:
    |
    | Your database currently contains the password directly.
    |
    | If your database has a password_hash() value instead,
    | replace this section with password_verify().
    |
    |--------------------------------------------------------------------------
    */

    $databasePassword =
        (string)($user['password'] ?? '');


    if (!hash_equals(
        $databasePassword,
        $password
    )) {

        if ($isAjax) {

            jsonResponse(
                false,
                'Invalid email address or password.',
                [],
                401
            );

        }

        header('Location: login.php?error=invalid_login');

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE OLD OTP DATA
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
    | GENERATE OTP
    |--------------------------------------------------------------------------
    */

    $otp =
        (string)random_int(
            100000,
            999999
        );


    /*
    |--------------------------------------------------------------------------
    | OTP EXPIRATION
    |--------------------------------------------------------------------------
    */

    $otpExpires =
        time() + (10 * 60);


    /*
    |--------------------------------------------------------------------------
    | STORE OTP SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION['otp_hash'] =
        password_hash(
            $otp,
            PASSWORD_DEFAULT
        );


    $_SESSION['otp_expires'] =
        $otpExpires;


    $_SESSION['otp_attempts'] =
        0;


    $_SESSION['otp_user_id'] =
        (int)$user['id'];


    $_SESSION['otp_username'] =
        (string)$user['username'];


    $_SESSION['otp_remember'] =
        $remember;


    /*
    |--------------------------------------------------------------------------
    | CREATE MAILER
    |--------------------------------------------------------------------------
    */

    try {

        $mail =
            new PHPMailer(true);


        /*
        |--------------------------------------------------------------------------
        | SMTP
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host =
            'smtp.gmail.com';

        $mail->SMTPAuth =
            true;

        $mail->Username =
            SMTP_USERNAME;

        $mail->Password =
            SMTP_PASSWORD;

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port =
            587;

        $mail->CharSet =
            'UTF-8';


        /*
        |--------------------------------------------------------------------------
        | CONNECTION TIMEOUT
        |--------------------------------------------------------------------------
        |
        | Prevent the browser from waiting forever.
        |
        |--------------------------------------------------------------------------
        */

        $mail->Timeout =
            15;


        /*
        |--------------------------------------------------------------------------
        | FROM
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            MAIL_FROM_EMAIL,
            MAIL_FROM_NAME
        );


        /*
        |--------------------------------------------------------------------------
        | RECIPIENT
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            (string)$user['username']
        );


        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $mail->Subject =
            'CMO Information System - Verification Code';


        /*
        |--------------------------------------------------------------------------
        | SAFE OTP
        |--------------------------------------------------------------------------
        */

        $safeOtp =
            htmlspecialchars(
                $otp,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );


        /*
        |--------------------------------------------------------------------------
        | HTML
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);


        $mail->Body = '

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

</head>


<body style="
margin:0;
padding:0;
background:#f4f7fb;
font-family:Arial,Helvetica,sans-serif;
">

<div style="
max-width:600px;
margin:40px auto;
background:#ffffff;
border:1px solid #e2e8f0;
border-radius:12px;
overflow:hidden;
">


<div style="
background:#0b5796;
color:#ffffff;
padding:25px;
text-align:center;
">

<h2 style="
margin:0;
font-size:22px;
">

CMO Information System

</h2>


<p style="
margin:8px 0 0;
font-size:13px;
">

Philippine Air Force

</p>

</div>


<div style="
padding:35px;
color:#24364c;
">

<h3>
Verification Code
</h3>


<p style="
font-size:14px;
line-height:1.6;
">

A login attempt was made for your
CMO Information System account.

</p>


<p style="
font-size:14px;
line-height:1.6;
">

Enter the following verification code
to complete your login:

</p>


<div style="
margin:30px 0;
padding:20px;
background:#eef7ff;
border:1px solid #cfe8ff;
border-radius:10px;
text-align:center;
">


<div style="
color:#6b7c93;
font-size:11px;
margin-bottom:8px;
text-transform:uppercase;
letter-spacing:1px;
">

Verification Code

</div>


<div style="
color:#0b5796;
font-size:36px;
font-weight:bold;
letter-spacing:8px;
">

' . $safeOtp . '

</div>

</div>


<p style="
font-size:13px;
color:#6b7c93;
">

This code expires in
<strong>10 minutes</strong>.

</p>


<p style="
font-size:13px;
color:#6b7c93;
">

If you did not attempt to sign in,
you can safely ignore this email.

</p>

</div>


<div style="
padding:18px;
background:#f8fafc;
border-top:1px solid #edf0f3;
text-align:center;
color:#8d99a6;
font-size:11px;
">

CMO Information System
<br>
Philippine Air Force

</div>


</div>

</body>

</html>

';


        /*
        |--------------------------------------------------------------------------
        | PLAIN TEXT
        |--------------------------------------------------------------------------
        */

        $mail->AltBody =
            "CMO Information System\n\n" .
            "Your verification code is: " .
            $otp .
            "\n\n" .
            "This code will expire in 10 minutes.\n\n" .
            "If you did not attempt to sign in, " .
            "you can safely ignore this email.";


        /*
        |--------------------------------------------------------------------------
        | SEND
        |--------------------------------------------------------------------------
        */

        $mail->send();


        /*
        |--------------------------------------------------------------------------
        | AJAX SUCCESS
        |--------------------------------------------------------------------------
        */

        if ($isAjax) {

            jsonResponse(
                true,
                'Verification code sent to your email.',
                [
                    'email' =>
                        (string)$user['username']
                ]
            );

        }


        /*
        |--------------------------------------------------------------------------
        | NON AJAX FALLBACK
        |--------------------------------------------------------------------------
        */

        header(
            'Location: login.php?otp=1'
        );

        exit;


    } catch (Exception $mailException) {


        /*
        |--------------------------------------------------------------------------
        | LOG REAL ERROR
        |--------------------------------------------------------------------------
        */

        error_log(
            'PHPMailer OTP error: ' .
            $mailException->getMessage()
        );


        /*
        |--------------------------------------------------------------------------
        | CLEAR OTP
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
        | AJAX ERROR
        |--------------------------------------------------------------------------
        */

        if ($isAjax) {

            /*
            | Do NOT expose SMTP password or
            | sensitive server information.
            */

            jsonResponse(
                false,
                'We could not send the verification code. Please check your mail configuration and try again.',
                [],
                500
            );

        }


        header(
            'Location: login.php?error=email'
        );

        exit;
    }


} catch (PDOException $databaseException) {


    /*
    |--------------------------------------------------------------------------
    | LOG DATABASE ERROR
    |--------------------------------------------------------------------------
    */

    error_log(
        'Database authentication error: ' .
        $databaseException->getMessage()
    );


    if ($isAjax) {

        jsonResponse(
            false,
            'A database error occurred. Please try again later.',
            [],
            500
        );

    }


    header(
        'Location: login.php?error=db'
    );

    exit;
}