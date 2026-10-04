```php
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
|
| db.php handles the TiDB / MySQL PDO connection.
|
*/

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vendor/autoload.php';


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

    header("Location: login.php?error=empty");
    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDATE EMAIL
|--------------------------------------------------------------------------
*/

if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {

    header("Location: login.php?error=email");
    exit;

}


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

try {

    /*
     * ======================================================
     * FIND USER BY EMAIL
     * ======================================================
     *
     * The username column contains the user's email address.
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
        !$user ||
        !isset($user['password']) ||
        !password_verify(
            $password,
            $user['password']
        )
    ) {

        header("Location: login.php?error=1");
        exit;

    }


    /*
     * ======================================================
     * PASSWORD IS CORRECT
     * ======================================================
     *
     * We DO NOT log the user in yet.
     *
     * The user must successfully enter the OTP first.
     */


    /*
     * ======================================================
     * GENERATE 6-DIGIT OTP
     * ======================================================
     */

    $otp = (string) random_int(100000, 999999);


    /*
     * ======================================================
     * OTP EXPIRATION
     * ======================================================
     *
     * OTP will remain valid for 10 minutes.
     */

    $otpExpires = time() + (10 * 60);


    /*
     * ======================================================
     * STORE OTP INFORMATION IN SESSION
     * ======================================================
     *
     * We store a HASH of the OTP instead of the actual OTP.
     */

    $_SESSION['otp_hash'] = password_hash(
        $otp,
        PASSWORD_DEFAULT
    );

    $_SESSION['otp_expires'] = $otpExpires;

    $_SESSION['otp_attempts'] = 0;

    $_SESSION['otp_user_id'] = (int) $user['id'];

    $_SESSION['otp_username'] = $user['username'];

    $_SESSION['otp_remember'] = $remember;


    /*
     * ======================================================
     * CREATE MAILER
     * ======================================================
     */

    $mail = new PHPMailer(true);


    /*
     * ======================================================
     * GMAIL SMTP CONFIGURATION
     * ======================================================
     */

    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';

    $mail->SMTPAuth = true;


    /*
     * ======================================================
     * YOUR GMAIL ACCOUNT
     * ======================================================
     *
     * CHANGE THIS TO YOUR GMAIL ADDRESS.
     */

    $mail->Username = 'YOUR_GMAIL@gmail.com';


    /*
     * ======================================================
     * GMAIL APP PASSWORD
     * ======================================================
     *
     * Use a Google App Password.
     *
     * DO NOT use your normal Gmail password.
     */

    $mail->Password = 'YOUR_16_CHARACTER_APP_PASSWORD';


    /*
     * ======================================================
     * SMTP ENCRYPTION
     * ======================================================
     */

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;


    /*
     * ======================================================
     * EMAIL SENDER
     * ======================================================
     */

    $mail->setFrom(
        'YOUR_GMAIL@gmail.com',
        'CMO Information System'
    );


    /*
     * ======================================================
     * EMAIL RECIPIENT
     * ======================================================
     *
     * Send the OTP to the email address used for login.
     */

    $mail->addAddress($username);


    /*
     * ======================================================
     * EMAIL CONTENT
     * ======================================================
     */

    $mail->isHTML(true);

    $mail->Subject = 'CMO Information System - Verification Code';

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
        border-radius:12px;
        overflow:hidden;
        border:1px solid #e2e8f0;
    ">

        <div style="
            background:#0b5796;
            padding:25px;
            text-align:center;
            color:#ffffff;
        ">

            <h2 style="
                margin:0;
                font-size:22px;
            ">
                CMO Information System
            </h2>

            <p style="
                margin:8px 0 0;
                color:#dceeff;
                font-size:13px;
            ">
                Philippine Air Force
            </p>

        </div>


        <div style="
            padding:35px;
            color:#24364c;
        ">

            <h3 style="
                margin-top:0;
                font-size:20px;
            ">
                Verification Code
            </h3>


            <p style="
                font-size:14px;
                line-height:1.6;
            ">
                Someone is attempting to sign in to your
                CMO Information System account.
            </p>


            <p style="
                font-size:14px;
                line-height:1.6;
            ">
                Use the verification code below to
                complete your login:
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
                    Your verification code
                </div>


                <div style="
                    color:#0b5796;
                    font-size:36px;
                    font-weight:bold;
                    letter-spacing:8px;
                ">
                    ' . htmlspecialchars(
                        $otp,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '
                </div>

            </div>


            <p style="
                font-size:13px;
                color:#6b7c93;
                line-height:1.6;
            ">
                This code will expire in
                <strong>10 minutes</strong>.
            </p>


            <p style="
                font-size:13px;
                color:#6b7c93;
                line-height:1.6;
            ">
                If you did not attempt to sign in,
                you can safely ignore this email.
            </p>

        </div>


        <div style="
            padding:18px 35px;
            background:#f8fafc;
            border-top:1px solid #edf0f3;
            text-align:center;
            color:#8d99a6;
            font-size:11px;
        ">

            CMO Information System<br>
            Philippine Air Force

        </div>

    </div>

</body>

</html>

';


    /*
     * ======================================================
     * PLAIN TEXT EMAIL
     * ======================================================
     */

    $mail->AltBody =
        "CMO Information System\n\n" .
        "Your verification code is: " . $otp . "\n\n" .
        "This code will expire in 10 minutes.\n\n" .
        "If you did not attempt to sign in, " .
        "you can safely ignore this email.";


    /*
     * ======================================================
     * SEND EMAIL
     * ======================================================
     */

    $mail->send();


    /*
     * ======================================================
     * OTP EMAIL SENT SUCCESSFULLY
     * ======================================================
     *
     * The user is NOT logged in yet.
     *
     * Send them to the OTP verification page.
     */

    header("Location: verify_otp.php");
    exit;


} catch (Exception $e) {

    /*
     * ======================================================
     * EMAIL ERROR
     * ======================================================
     *
     * Do not expose SMTP credentials or technical details
     * to the user.
     */

    error_log(
        "OTP email error: " . $e->getMessage()
    );


    /*
     * ======================================================
     * CLEAR OTP SESSION INFORMATION
     * ======================================================
     */

    unset($_SESSION['otp_hash']);
    unset($_SESSION['otp_expires']);
    unset($_SESSION['otp_attempts']);
    unset($_SESSION['otp_user_id']);
    unset($_SESSION['otp_username']);
    unset($_SESSION['otp_remember']);


    header("Location: login.php?error=email");
    exit;


} catch (PDOException $e) {

    /*
     * ======================================================
     * DATABASE ERROR
     * ======================================================
     */

    error_log(
        "Login database error: " . $e->getMessage()
    );

    header("Location: login.php?error=db");
    exit;

}
```