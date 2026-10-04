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
|
| Prevent PHP warnings/notices from being printed into the AJAX response.
| They are logged instead.
|
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '0');
ini_set('log_errors', '1');


/*
|--------------------------------------------------------------------------
| REQUIRED FILES
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/vendor/autoload.php';


use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


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
| JSON RESPONSE
|--------------------------------------------------------------------------
*/

function jsonResponse(
    bool $success,
    string $message,
    array $extra = []
): never {

    /*
    |--------------------------------------------------------------------------
    | Remove any accidental output buffers
    |--------------------------------------------------------------------------
    */

    while (ob_get_level() > 0) {
        ob_end_clean();
    }


    /*
    |--------------------------------------------------------------------------
    | JSON HEADER
    |--------------------------------------------------------------------------
    */

    http_response_code(
        $success ? 200 : 400
    );

    header(
        'Content-Type: application/json; charset=UTF-8'
    );

    header(
        'Cache-Control: no-store, no-cache, must-revalidate'
    );


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| START OUTPUT BUFFER
|--------------------------------------------------------------------------
|
| Prevent accidental whitespace/output from breaking JSON.
|
|--------------------------------------------------------------------------
*/

ob_start();


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    if ($isAjax) {

        jsonResponse(
            false,
            'Invalid request method.'
        );

    }


    header(
        'Location: login.php'
    );

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

if (
    $username === '' ||
    $password === ''
) {

    if ($isAjax) {

        jsonResponse(
            false,
            'Please enter your email address and password.'
        );

    }


    header(
        'Location: login.php?error=empty'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| NORMALIZE EMAIL
|--------------------------------------------------------------------------
*/

$username =
    strtolower(
        trim($username)
    );


/*
|--------------------------------------------------------------------------
| VALIDATE EMAIL
|--------------------------------------------------------------------------
*/

if (
    !filter_var(
        $username,
        FILTER_VALIDATE_EMAIL
    )
) {

    if ($isAjax) {

        jsonResponse(
            false,
            'Please enter a valid email address.'
        );

    }


    header(
        'Location: login.php?error=invalid_email'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE AUTHENTICATION
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | FIND USER
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->prepare(
        "SELECT
            id,
            username,
            password
         FROM users
         WHERE LOWER(username) = LOWER(:username)
         LIMIT 1"
    );


    $stmt->execute([
        ':username' => $username
    ]);


    $user =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    /*
    |--------------------------------------------------------------------------
    | USER NOT FOUND
    |--------------------------------------------------------------------------
    */

    if (!$user) {

        if ($isAjax) {

            jsonResponse(
                false,
                'Invalid email address or password.'
            );

        }


        header(
            'Location: login.php?error=invalid'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | GET DATABASE PASSWORD
    |--------------------------------------------------------------------------
    */

    $databasePassword =
        (string)(
            $user['password'] ?? ''
        );


    /*
    |--------------------------------------------------------------------------
    | PASSWORD VERIFY
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | This expects the database password to have been generated using:
    |
    | password_hash($password, PASSWORD_DEFAULT)
    |
    |--------------------------------------------------------------------------
    */

    if (
        $databasePassword === '' ||
        !password_verify(
            $password,
            $databasePassword
        )
    ) {

        if ($isAjax) {

            jsonResponse(
                false,
                'Invalid email address or password.'
            );

        }


        header(
            'Location: login.php?error=invalid'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL PASSWORD REHASH
    |--------------------------------------------------------------------------
    */

    if (
        password_needs_rehash(
            $databasePassword,
            PASSWORD_DEFAULT
        )
    ) {

        $newPasswordHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        $updatePassword =
            $connection->prepare(
                "UPDATE users
                 SET password = :password
                 WHERE id = :id"
            );


        $updatePassword->execute([
            ':password' =>
                $newPasswordHash,

            ':id' =>
                (int)$user['id']
        ]);

    }


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

    $mail =
        new PHPMailer(true);


    /*
    |--------------------------------------------------------------------------
    | SMTP CONFIGURATION
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


    /*
    |--------------------------------------------------------------------------
    | TLS
    |--------------------------------------------------------------------------
    */

    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_STARTTLS;


    $mail->Port =
        587;


    $mail->CharSet =
        'UTF-8';


    /*
    |--------------------------------------------------------------------------
    | SMTP TIMEOUT
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
    | TO
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
    | OTP HTML
    |--------------------------------------------------------------------------
    */

    $safeOtp =
        htmlspecialchars(
            $otp,
            ENT_QUOTES |
            ENT_SUBSTITUTE,
            'UTF-8'
        );


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

<h2 style="margin:0;">
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
    | PLAIN TEXT EMAIL
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
    | SEND EMAIL
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
            'Verification code sent successfully.',
            [
                'otp_required' => true,

                'email' =>
                    (string)$user['username']
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | NORMAL FORM FALLBACK
    |--------------------------------------------------------------------------
    */

    header(
        'Location: login.php?otp=1'
    );

    exit;


}


/*
|--------------------------------------------------------------------------
| DATABASE ERROR
|--------------------------------------------------------------------------
*/

catch (PDOException $e) {

    error_log(
        'CMO database error: ' .
        $e->getMessage()
    );


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


    if ($isAjax) {

        jsonResponse(
            false,
            'A database error occurred. Please try again later.'
        );

    }


    header(
        'Location: login.php?error=db'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| MAIL / PHPMailer ERROR
|--------------------------------------------------------------------------
*/

catch (Exception $e) {

    error_log(
        'CMO PHPMailer OTP error: ' .
        $e->getMessage()
    );


    /*
    |--------------------------------------------------------------------------
    | CLEAR OTP SESSION
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


    if ($isAjax) {

        jsonResponse(
            false,
            'We could not send the verification code. Please check your SMTP configuration.'
        );

    }


    header(
        'Location: login.php?error=email'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| UNEXPECTED ERROR
|--------------------------------------------------------------------------
*/

catch (Throwable $e) {

    error_log(
        'CMO authentication error: ' .
        $e->getMessage()
    );


    /*
    |--------------------------------------------------------------------------
    | CLEAR OTP SESSION
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


    if ($isAjax) {

        jsonResponse(
            false,
            'Unable to process the login request. Please try again.'
        );

    }


    header(
        'Location: login.php?error=system'
    );

    exit;
}