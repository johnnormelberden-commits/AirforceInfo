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
| PHP ERROR HANDLING
|--------------------------------------------------------------------------
|
| Do not allow PHP warnings/notices to corrupt AJAX JSON responses.
|
*/

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);


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
| AJAX DETECTION
|--------------------------------------------------------------------------
*/

$isAjax =
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) ===
    'xmlhttprequest';


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

    http_response_code(
        $success ? 200 : 400
    );

    header(
        'Content-Type: application/json; charset=UTF-8'
    );

    header(
        'Cache-Control: no-store, no-cache, must-revalidate'
    );

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
| NORMAL REDIRECT ERROR
|--------------------------------------------------------------------------
*/

function redirectError(string $error): never
{
    header(
        'Location: login.php?error=' .
        urlencode($error)
    );

    exit;
}


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

    header('Location: login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$username = trim(
    (string) ($_POST['username'] ?? '')
);

$password = (string) (
    $_POST['password'] ?? ''
);

$remember =
    isset($_POST['remember']) &&
    (string) $_POST['remember'] === '1';


/*
|--------------------------------------------------------------------------
| EMPTY DATA
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

    redirectError('empty');
}


/*
|--------------------------------------------------------------------------
| EMAIL VALIDATION
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

    redirectError('invalid_email');
}


/*
|--------------------------------------------------------------------------
| DATABASE AUTHENTICATION
|--------------------------------------------------------------------------
*/

try {

    $stmt = $connection->prepare(
        "
        SELECT
            id,
            username,
            password
        FROM users
        WHERE username = :username
        LIMIT 1
        "
    );

    $stmt->execute([
        ':username' => $username
    ]);

    $user =
        $stmt->fetch(PDO::FETCH_ASSOC);


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

        redirectError('invalid');
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD VERIFICATION
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | The database password must be created using:
    |
    | password_hash($password, PASSWORD_DEFAULT)
    |
    */

    $databasePassword =
        (string) (
            $user['password'] ?? ''
        );


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

        redirectError('invalid');
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

        $newHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        $update =
            $connection->prepare(
                "
                UPDATE users
                SET password = :password
                WHERE id = :id
                "
            );

        $update->execute([
            ':password' => $newHash,
            ':id' =>
                (int) $user['id']
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR OLD OTP
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
        (string) random_int(
            100000,
            999999
        );


    /*
    |--------------------------------------------------------------------------
    | OTP EXPIRATION
    |--------------------------------------------------------------------------
    */

    $otpExpires =
        time() + 600;


    /*
    |--------------------------------------------------------------------------
    | STORE OTP
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
        (int) $user['id'];

    $_SESSION['otp_username'] =
        (string) $user['username'];

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
    | TIMEOUT
    |--------------------------------------------------------------------------
    */

    $mail->Timeout = 15;

    $mail->SMTPKeepAlive = false;


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
        (string) $user['username']
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
<title>CMO Verification Code</title>
</head>

<body style="
margin:0;
padding:30px;
background:#f4f7fb;
font-family:Arial,Helvetica,sans-serif;
">

<div style="
max-width:600px;
margin:auto;
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

<p style="margin:8px 0 0;">
Philippine Air Force
</p>

</div>

<div style="
padding:35px;
color:#24364c;
">

<h3>
Login Verification
</h3>

<p>
Your CMO Information System verification code is:
</p>

<div style="
margin:30px 0;
padding:22px;
background:#eef7ff;
border:1px solid #cfe8ff;
border-radius:10px;
text-align:center;
">

<div style="
font-size:11px;
color:#6b7c93;
margin-bottom:8px;
text-transform:uppercase;
letter-spacing:1px;
">

Verification Code

</div>

<div style="
font-size:36px;
font-weight:bold;
letter-spacing:8px;
color:#0b5796;
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
please ignore this message.

</p>

</div>

<div style="
padding:18px;
background:#f8fafc;
border-top:1px solid #edf0f3;
text-align:center;
font-size:11px;
color:#8d99a6;
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
    | PLAIN TEXT VERSION
    |--------------------------------------------------------------------------
    */

    $mail->AltBody =
        "CMO Information System\n\n" .
        "Your verification code is: " .
        $otp .
        "\n\n" .
        "This code expires in 10 minutes.\n";


    /*
    |--------------------------------------------------------------------------
    | SEND EMAIL
    |--------------------------------------------------------------------------
    */

    $mail->send();


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    if ($isAjax) {

        jsonResponse(
            true,
            'Verification code sent successfully.',
            [
                'otp_required' => true,
                'email' =>
                    (string) $user['username']
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NON-AJAX
    |--------------------------------------------------------------------------
    */

    header(
        'Location: login.php?otp=1'
    );

    exit;


} catch (PDOException $e) {

    error_log(
        'CMO AUTH DATABASE ERROR: ' .
        $e->getMessage()
    );


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

    redirectError('db');


} catch (Exception $e) {

    error_log(
        'CMO AUTH EMAIL ERROR: ' .
        $e->getMessage()
    );


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
            'We could not send the verification code. Please check the SMTP configuration.'
        );
    }


    redirectError('email');


} catch (Throwable $e) {

    error_log(
        'CMO AUTH GENERAL ERROR: ' .
        $e->getMessage()
    );


    if ($isAjax) {

        jsonResponse(
            false,
            'The authentication server encountered an error. Please try again.'
        );
    }


    redirectError('server');
}