<?php

session_start();

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once 'db.php';


/*
|--------------------------------------------------------------------------
| ONLY ACCEPT POST REQUESTS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: login.php");
    exit;

}


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
| BASIC EMAIL VALIDATION
|--------------------------------------------------------------------------
|
| Since username is the email address, make sure it is a valid
| email format before querying the database.
|
*/

if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {

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
     * ==============================================================
     * FIND USER
     * ==============================================================
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
     * ==============================================================
     * VERIFY PASSWORD
     * ==============================================================
     */

    if (
        !$user ||
        !isset($user['password']) ||
        !password_verify(
            $password,
            $user['password']
        )
    ) {

        /*
         * Do not reveal whether the email exists.
         */

        header("Location: login.php?error=1");
        exit;

    }


    /*
     * ==============================================================
     * GENERATE OTP
     * ==============================================================
     *
     * random_int() is cryptographically secure.
     *
     * Generates a 6-digit code.
     *
     */

    $otp = (string) random_int(100000, 999999);


    /*
     * ==============================================================
     * HASH OTP
     * ==============================================================
     *
     * We do NOT store the actual OTP in the session.
     *
     * Only the hash is stored.
     *
     */

    $otp_hash = password_hash(
        $otp,
        PASSWORD_DEFAULT
    );


    /*
     * ==============================================================
     * OTP EXPIRATION
     * ==============================================================
     *
     * OTP is valid for 5 minutes.
     *
     */

    $otp_expires = time() + (5 * 60);


    /*
     * ==============================================================
     * CLEAR OLD AUTHENTICATION STATE
     * ==============================================================
     *
     * The user is NOT logged in yet.
     *
     */

    unset(
        $_SESSION['logged_in'],
        $_SESSION['username'],
        $_SESSION['user_id']
    );


    /*
     * ==============================================================
     * STORE TEMPORARY LOGIN INFORMATION
     * ==============================================================
     */

    $_SESSION['otp_hash'] = $otp_hash;

    $_SESSION['otp_expires'] = $otp_expires;

    $_SESSION['otp_user_id'] = (int) $user['id'];

    $_SESSION['otp_username'] = $user['username'];

    $_SESSION['otp_remember'] = $remember;

    /*
     * Track failed OTP attempts.
     */

    $_SESSION['otp_attempts'] = 0;


    /*
     * ==============================================================
     * SEND OTP EMAIL
     * ==============================================================
     */

    $recipient = $user['username'];

    $subject = "CMO Information System - Verification Code";


    /*
     * Plain-text email.
     */

    $message =

        "CMO INFORMATION SYSTEM\n" .
        "=======================\n\n" .

        "Your verification code is:\n\n" .

        $otp . "\n\n" .

        "This verification code will expire in 5 minutes.\n\n" .

        "If you did not attempt to sign in to the CMO Information System, " .
        "please ignore this email and contact your system administrator.\n\n" .

        "Philippine Air Force\n" .
        "CMO Information System";


    /*
     * ==============================================================
     * EMAIL HEADERS
     * ==============================================================
     *
     * IMPORTANT:
     *
     * Change this address to an email address belonging to your
     * organization/domain.
     *
     */

    $headers = [];

    $headers[] = "From: CMO Information System <no-reply@yourdomain.com>";

    $headers[] = "Reply-To: no-reply@yourdomain.com";

    $headers[] = "MIME-Version: 1.0";

    $headers[] = "Content-Type: text/plain; charset=UTF-8";


    /*
     * ==============================================================
     * SEND EMAIL
     * ==============================================================
     */

    $mail_sent = mail(
        $recipient,
        $subject,
        $message,
        implode("\r\n", $headers)
    );


    /*
     * ==============================================================
     * EMAIL FAILED
     * ==============================================================
     */

    if (!$mail_sent) {

        /*
         * Remove temporary OTP information.
         */

        unset(
            $_SESSION['otp_hash'],
            $_SESSION['otp_expires'],
            $_SESSION['otp_user_id'],
            $_SESSION['otp_username'],
            $_SESSION['otp_remember'],
            $_SESSION['otp_attempts']
        );


        error_log(
            "OTP email failed for user: " . $recipient
        );


        header("Location: login.php?error=email");
        exit;

    }


    /*
     * ==============================================================
     * OTP SUCCESSFULLY SENT
     * ==============================================================
     */

    header("Location: verify-otp.php");
    exit;


} catch (PDOException $e) {

    /*
     * ==============================================================
     * DATABASE ERROR
     * ==============================================================
     *
     * Do not expose database details to the user.
     *
     */

    error_log(
        "Login database error: " . $e->getMessage()
    );

    header("Location: login.php?error=db");
    exit;

} catch (Throwable $e) {

    /*
     * ==============================================================
     * GENERAL ERROR
     * ==============================================================
     */

    error_log(
        "Login/OTP error: " . $e->getMessage()
    );

    header("Location: login.php?error=1");
    exit;

}
