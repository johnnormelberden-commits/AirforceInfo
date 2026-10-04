<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| ALREADY AUTHENTICATED
|--------------------------------------------------------------------------
*/

if (!empty($_SESSION['logged_in']) && !empty($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| REMEMBERED USER
|--------------------------------------------------------------------------
|
| IMPORTANT:
| The remember_user cookie is only used to pre-fill the email.
| It is NOT treated as proof of authentication.
|
*/

$rememberedUser = '';

if (
    isset($_COOKIE['remember_user']) &&
    $_COOKIE['remember_user'] !== ''
) {
    $rememberedUser = (string)$_COOKIE['remember_user'];
}


/*
|--------------------------------------------------------------------------
| OTP STATE
|--------------------------------------------------------------------------
*/

$otpPending =
    isset($_SESSION['otp_hash']) &&
    isset($_SESSION['otp_expires']) &&
    isset($_SESSION['otp_user_id']) &&
    isset($_SESSION['otp_username']);


/*
|--------------------------------------------------------------------------
| OTP EMAIL
|--------------------------------------------------------------------------
*/

$otpEmail = '';

if ($otpPending) {
    $otpEmail = (string)$_SESSION['otp_username'];
}


/*
|--------------------------------------------------------------------------
| ERROR MESSAGE
|--------------------------------------------------------------------------
*/

$errorMessage = '';

if (isset($_GET['error'])) {

    $error = (string)$_GET['error'];

    switch ($error) {

        case 'empty':

            $errorMessage =
                'Please enter your email address and password.';

            break;


        case 'invalid_email':

            $errorMessage =
                'Please enter a valid email address.';

            break;


        case 'email':

            $errorMessage =
                'We could not send the verification code. Please check the Gmail SMTP configuration and try again.';

            break;


        case 'db':

            $errorMessage =
                'A database error occurred. Please try again later.';

            break;


        case 'otp_expired':

            $errorMessage =
                'Your verification code has expired. Please sign in again.';

            break;


        case 'otp_attempts':

            $errorMessage =
                'Too many incorrect verification attempts. Please sign in again.';

            break;


        case 'invalid':

            $errorMessage =
                'Invalid verification code.';

            break;


        case '1':

            $errorMessage =
                'Invalid email address or password.';

            break;


        default:

            $errorMessage =
                'Unable to sign in. Please try again.';

            break;
    }
}


/*
|--------------------------------------------------------------------------
| OTP ERROR
|--------------------------------------------------------------------------
*/

$otpError = '';

if (isset($_GET['otp_error'])) {

    $otpError = (string)$_GET['otp_error'];

}


/*
|--------------------------------------------------------------------------
| OTP REMAINING TIME
|--------------------------------------------------------------------------
*/

$otpRemainingSeconds = 0;

if ($otpPending) {

    $otpRemainingSeconds = max(
        0,
        (int)$_SESSION['otp_expires'] - time()
    );

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>
CMO Information System - Login
</title>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>


<!-- =====================================================
     BOOTSTRAP
====================================================== -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- =====================================================
     BOOTSTRAP ICONS
====================================================== -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
>


<!-- =====================================================
     GOOGLE FONT
====================================================== -->

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<style>

/* =========================================
   GLOBAL
========================================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    font-family:
        'Inter',
        Arial,
        sans-serif;

    background:
        radial-gradient(
            circle at 20% 20%,
            rgba(0, 87, 183, 0.10),
            transparent 30%
        ),
        linear-gradient(
            135deg,
            #edf4fb,
            #f7faff
        );

    color: #172b4d;
}


/* =========================================
   LOGIN CONTAINER
========================================= */

.login-container {

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 35px 20px;
}


/* =========================================
   LOGIN CARD
========================================= */

.login-card {

    width: 100%;

    max-width: 1050px;

    min-height: 590px;

    display: flex;

    background: #ffffff;

    border-radius: 22px;

    overflow: hidden;

    box-shadow:
        0 25px 70px rgba(19, 55, 91, 0.18),
        0 5px 20px rgba(19, 55, 91, 0.08);

    border:
        1px solid rgba(15, 62, 105, 0.08);
}


/* =========================================
   LEFT BRANDING
========================================= */

.brand-panel {

    position: relative;

    width: 43%;

    padding: 44px 48px;

    color: white;

    overflow: hidden;

    background:
        linear-gradient(
            145deg,
            #082d55 0%,
            #06427b 50%,
            #0b5796 100%
        );
}


.brand-panel::before {

    content: "";

    position: absolute;

    width: 450px;

    height: 700px;

    top: -160px;

    left: 30px;

    transform: rotate(25deg);

    background:
        repeating-linear-gradient(
            90deg,
            transparent 0px,
            transparent 70px,
            rgba(255,255,255,0.055) 71px,
            rgba(255,255,255,0.055) 73px
        );

    pointer-events: none;
}


.brand-panel::after {

    content: "✦";

    position: absolute;

    right: -60px;

    bottom: -100px;

    width: 280px;

    height: 280px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 170px;

    font-weight: 800;

    color: rgba(255,255,255,0.055);

    border:
        35px solid rgba(255,255,255,0.035);

    pointer-events: none;
}


.brand-content {

    position: relative;

    z-index: 2;
}


.brand-header {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 90px;
}


.logo-wrapper {

    width: 48px;

    height: 48px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #ffffff;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.15);

    overflow: hidden;
}


.logo-wrapper img {

    width: 42px;

    height: 42px;

    object-fit: contain;
}


.brand-name {

    line-height: 1.2;
}


.brand-name strong {

    display: block;

    font-size: 16px;

    font-weight: 700;

    letter-spacing: -0.3px;
}


.brand-name small {

    display: block;

    margin-top: 4px;

    color: rgba(255,255,255,0.65);

    font-size: 10px;
}


.portal-label {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 15px;

    color: #a9d1f7;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.2px;

    text-transform: uppercase;
}


.portal-label::before {

    content: "";

    width: 17px;

    height: 2px;

    background: #63b5ff;

    border-radius: 5px;
}


.brand-title {

    max-width: 350px;

    margin: 0 0 16px;

    font-size: 35px;

    line-height: 1.12;

    font-weight: 800;

    letter-spacing: -1px;
}


.brand-description {

    max-width: 370px;

    margin: 0;

    color: rgba(255,255,255,0.68);

    font-size: 12px;

    line-height: 1.7;
}


.security-badge {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-top: 40px;

    padding: 7px 12px;

    border-radius: 30px;

    background:
        rgba(2, 25, 50, 0.35);

    border:
        1px solid rgba(125, 202, 255, 0.25);

    color: #d9ecff;

    font-size: 10px;

    font-weight: 500;
}


.security-dot {

    width: 8px;

    height: 8px;

    background: #20d66b;

    border-radius: 50%;

    box-shadow:
        0 0 8px rgba(32,214,107,0.8);
}


/* =========================================
   RIGHT LOGIN
========================================= */

.login-panel {

    width: 57%;

    padding: 50px 65px;

    display: flex;

    align-items: center;

    background: #ffffff;
}


.login-content {

    width: 100%;

    max-width: 520px;

    margin: 0 auto;
}


.authorized {

    margin-bottom: 8px;

    color: #1764ae;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.4px;

    text-transform: uppercase;
}


.login-title {

    margin: 0;

    color: #10233d;

    font-size: 31px;

    font-weight: 800;

    letter-spacing: -1px;
}


.login-subtitle {

    margin: 8px 0 27px;

    color: #7d8b9b;

    font-size: 12px;

    line-height: 1.6;
}


/* =========================================
   LABEL
========================================= */

.form-label {

    display: block;

    margin-bottom: 7px;

    color: #24364c;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 0.8px;

    text-transform: uppercase;
}


/* =========================================
   INPUT
========================================= */

.input-wrapper {

    position: relative;

    margin-bottom: 18px;
}


.input-icon {

    position: absolute;

    left: 14px;

    top: 50%;

    transform: translateY(-50%);

    color: #8c9aaa;

    font-size: 14px;

    z-index: 2;
}


.form-control {

    width: 100%;

    height: 43px;

    padding: 0 42px;

    border:
        1px solid #d6dee7;

    border-radius: 10px;

    background: #ffffff !important;

    color: #26384c !important;

    font-size: 12px;

    box-shadow: none;
}


.form-control:focus {

    border-color: #428dd1;

    box-shadow:
        0 0 0 3px
        rgba(66,141,209,0.10);
}


.email-input {

    padding-right: 15px;
}


/* =========================================
   PASSWORD TOGGLE
========================================= */

.password-toggle {

    position: absolute;

    right: 13px;

    top: 50%;

    transform: translateY(-50%);

    border: 0;

    background: transparent;

    color: #8b99a8;

    cursor: pointer;

    font-size: 14px;
}


/* =========================================
   REMEMBER
========================================= */

.remember-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: -5px;

    margin-bottom: 18px;

    font-size: 10px;
}


.remember-label {

    display: flex;

    align-items: center;

    gap: 7px;

    color: #7d8b9b;

    cursor: pointer;
}


.remember-label input {

    width: 14px;

    height: 14px;

    accent-color: #176bb5;

    cursor: pointer;
}


/* =========================================
   LOGIN BUTTON
========================================= */

.btn-login {

    width: 100%;

    height: 42px;

    border: none;

    border-radius: 9px;

    background:
        linear-gradient(
            90deg,
            #0c5595,
            #176bb5
        );

    color: white;

    font-size: 12px;

    font-weight: 600;

    box-shadow:
        0 7px 15px
        rgba(15, 91, 157, 0.20);
}


.btn-login:disabled {

    opacity: 0.65;

    cursor: not-allowed;
}


/* =========================================
   SECURITY
========================================= */

.security-message {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 6px;

    margin-top: 13px;

    color: #929daa;

    font-size: 9px;

    text-align: center;
}


/* =========================================
   FOOTER
========================================= */

.login-footer {

    margin-top: 23px;

    padding-top: 18px;

    border-top:
        1px solid #edf0f3;

    text-align: center;

    color: #8d99a6;

    font-size: 8px;

    line-height: 1.6;
}


/* =========================================
   ERROR
========================================= */

.error-message {

    margin-top: 15px;

    padding: 10px 12px;

    border-radius: 8px;

    background: #fff1f1;

    border:
        1px solid #ffd6d6;

    color: #d63939;

    font-size: 11px;

    text-align: center;
}


/* =========================================
   OTP OVERLAY
========================================= */

.otp-overlay {

    position: fixed;

    inset: 0;

    z-index: 9999;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        rgba(5, 25, 45, 0.72);

    backdrop-filter: blur(7px);

    -webkit-backdrop-filter: blur(7px);
}


/* =========================================
   OTP MODAL
========================================= */

.otp-modal {

    width: 100%;

    max-width: 460px;

    padding: 38px;

    background: #ffffff;

    border-radius: 20px;

    box-shadow:
        0 30px 90px
        rgba(0,0,0,0.28);

    text-align: center;

    animation:
        otpPopup 0.22s ease-out;
}


@keyframes otpPopup {

    from {

        opacity: 0;

        transform:
            translateY(15px)
            scale(0.97);
    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }
}


/* =========================================
   OTP ICON
========================================= */

.otp-icon {

    width: 64px;

    height: 64px;

    margin: 0 auto 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #eef7ff;

    color: #176bb5;

    font-size: 28px;
}


/* =========================================
   OTP TITLE
========================================= */

.otp-modal h2 {

    margin: 0;

    color: #10233d;

    font-size: 25px;

    font-weight: 800;
}


.otp-modal p {

    margin:
        10px auto 18px;

    max-width: 360px;

    color: #7d8b9b;

    font-size: 12px;

    line-height: 1.6;
}


/* =========================================
   OTP EMAIL
========================================= */

.otp-email {

    display: inline-block;

    margin-bottom: 20px;

    padding: 7px 11px;

    border-radius: 8px;

    background: #f3f7fb;

    color: #1764ae;

    font-size: 11px;

    font-weight: 600;

    word-break: break-word;
}


/* =========================================
   OTP INPUT
========================================= */

.otp-input {

    width: 100%;

    height: 60px;

    border:
        1px solid #d6dee7;

    border-radius: 11px;

    outline: none;

    text-align: center;

    letter-spacing: 11px;

    padding-left: 11px;

    color: #172b4d;

    font-size: 26px;

    font-weight: 800;

    background: #ffffff;
}


.otp-input:focus {

    border-color: #428dd1;

    box-shadow:
        0 0 0 3px
        rgba(66,141,209,0.10);
}


/* =========================================
   OTP BUTTON
========================================= */

.btn-otp {

    width: 100%;

    height: 45px;

    margin-top: 17px;

    border: none;

    border-radius: 9px;

    background:
        linear-gradient(
            90deg,
            #0c5595,
            #176bb5
        );

    color: #ffffff;

    font-size: 12px;

    font-weight: 600;
}


.btn-otp:disabled {

    opacity: 0.6;

    cursor: not-allowed;
}


/* =========================================
   OTP TIMER
========================================= */

.otp-timer {

    margin-top: 15px;

    color: #929daa;

    font-size: 10px;
}


.otp-timer strong {

    color: #1764ae;
}


/* =========================================
   OTP ERROR
========================================= */

.otp-error {

    margin-top: 14px;

    padding: 10px;

    border-radius: 8px;

    background: #fff1f1;

    border:
        1px solid #ffd6d6;

    color: #d63939;

    font-size: 11px;
}


/* =========================================
   OTP SECURITY
========================================= */

.otp-security {

    margin-top: 18px;

    color: #929daa;

    font-size: 9px;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 800px) {

    .login-container {

        padding: 20px;
    }


    .login-card {

        max-width: 500px;

        min-height: auto;

        display: block;

        border-radius: 18px;
    }


    .brand-panel {

        width: 100%;

        min-height: 300px;

        padding: 30px;
    }


    .brand-header {

        margin-bottom: 40px;
    }


    .brand-title {

        font-size: 28px;
    }


    .login-panel {

        width: 100%;

        padding: 40px 30px;
    }

}


@media (max-width: 450px) {

    .login-container {

        padding: 10px;
    }


    .brand-panel {

        padding: 25px;
    }


    .login-panel {

        padding: 35px 22px;
    }


    .login-title {

        font-size: 27px;
    }


    .brand-title {

        font-size: 26px;
    }


    .otp-modal {

        padding: 30px 22px;
    }


    .otp-input {

        letter-spacing: 7px;

        font-size: 23px;
    }

}

</style>

</head>


<body>


<div class="login-container">

<div class="login-card">


<!-- =====================================================
     BRAND PANEL
====================================================== -->

<div class="brand-panel">

<div class="brand-content">


<div class="brand-header">

<div class="logo-wrapper">

<img
    src="cmo1.png"
    alt="Philippine Air Force CMO Logo"
>

</div>


<div class="brand-name">

<strong>
CMO Information System
</strong>

<small>
Philippine Air Force
</small>

</div>

</div>


<div class="portal-label">
CMO Information Portal
</div>


<h1 class="brand-title">

One secure<br>
system for<br>
CMO information.

</h1>


<p class="brand-description">

Access authorized CMO information,
personnel records, activities,
assignments, and official resources
from one secure workspace.

</p>


<div class="security-badge">

<span class="security-dot"></span>

Authorized personnel access

</div>


</div>

</div>


<!-- =====================================================
     LOGIN PANEL
====================================================== -->

<div class="login-panel">

<div class="login-content">


<div class="authorized">

Authorized Personnel

</div>


<h2 class="login-title">

Welcome back

</h2>


<p class="login-subtitle">

Sign in with your authorized email
and password. A verification code
will be sent to your email.

</p>


<!-- =================================================
     LOGIN FORM
================================================== -->

<form
    action="auth.php"
    method="POST"
    autocomplete="on"
    id="loginForm"
>


<label
    for="username"
    class="form-label"
>

Email Address

</label>


<div class="input-wrapper">

<i
    class="bi bi-envelope input-icon"
></i>


<input
    type="email"
    id="username"
    name="username"
    class="form-control email-input"
    placeholder="Enter your email address"
    value="<?= htmlspecialchars(
        $rememberedUser,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    ); ?>"
    required
    autocomplete="username"
    inputmode="email"
    maxlength="254"
>

</div>


<label
    for="password"
    class="form-label"
>

Password

</label>


<div class="input-wrapper">

<i
    class="bi bi-lock input-icon"
></i>


<input
    type="password"
    id="password"
    name="password"
    class="form-control"
    placeholder="Enter your password"
    required
    autocomplete="current-password"
>


<button
    type="button"
    class="password-toggle"
    onclick="togglePassword()"
    aria-label="Show password"
>

<i
    class="bi bi-eye"
    id="passwordIcon"
></i>

</button>

</div>


<div class="remember-row">

<label
    class="remember-label"
    for="remember"
>

<input
    type="checkbox"
    id="remember"
    name="remember"
    value="1"
>

Remember me

</label>

</div>


<button
    type="submit"
    class="btn-login"
    id="loginButton"
>

Continue securely

<i class="bi bi-arrow-right"></i>

</button>

</form>


<?php if ($errorMessage !== ''): ?>

<div class="error-message">

<i class="bi bi-exclamation-circle"></i>

<?= htmlspecialchars(
    $errorMessage,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
); ?>

</div>

<?php endif; ?>


<div class="security-message">

<i class="bi bi-shield-lock-fill"></i>

Your session is protected with
two-step verification.

</div>


<div class="login-footer">

<div>
Philippine Air Force
</div>

<div>
CMO Information System
</div>

</div>


</div>

</div>

</div>

</div>


<!-- =========================================================
     OTP POPUP
========================================================= -->

<?php if ($otpPending): ?>

<div
    class="otp-overlay"
    id="otpOverlay"
>

<div
    class="otp-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="otpTitle"
>


<div class="otp-icon">

<i class="bi bi-envelope-check-fill"></i>

</div>


<div class="authorized">

Two-Step Verification

</div>


<h2 id="otpTitle">

Check your email

</h2>


<p>

We sent a 6-digit verification code
to the email address associated with
your account.

</p>


<div class="otp-email">

<i class="bi bi-envelope"></i>

<?= htmlspecialchars(
    $otpEmail,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
); ?>

</div>


<form
    action="verify-otp.php"
    method="POST"
    autocomplete="off"
    id="otpForm"
>


<label
    for="otp"
    class="visually-hidden"
>
Verification Code
</label>


<input
    type="text"
    id="otp"
    name="otp"
    class="otp-input"
    placeholder="••••••"
    maxlength="6"
    pattern="[0-9]{6}"
    inputmode="numeric"
    autocomplete="one-time-code"
    required
    autofocus
>


<button
    type="submit"
    class="btn-otp"
    id="otpButton"
>

Verify and Sign In

<i class="bi bi-arrow-right ms-1"></i>

</button>

</form>


<?php if ($otpError !== ''): ?>

<div class="otp-error">

<i class="bi bi-exclamation-circle"></i>

<?php

if ($otpError === 'invalid') {

    echo 'Incorrect verification code. Please try again.';

} else {

    echo 'Unable to verify the code. Please try again.';

}

?>

</div>

<?php endif; ?>


<div class="otp-timer">

Code expires in

<strong id="otpTimer">
00:00
</strong>

</div>


<div class="otp-security">

<i class="bi bi-shield-lock-fill"></i>

Never share your verification code with anyone.

</div>


</div>

</div>

<?php endif; ?>


<script>

/*
|--------------------------------------------------------------------------
| PASSWORD TOGGLE
|--------------------------------------------------------------------------
*/

function togglePassword() {

    const password =
        document.getElementById('password');

    const icon =
        document.getElementById('passwordIcon');


    if (!password || !icon) {
        return;
    }


    if (password.type === 'password') {

        password.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');

    } else {

        password.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');

    }

}


/*
|--------------------------------------------------------------------------
| LOGIN LOADING
|--------------------------------------------------------------------------
*/

const loginForm =
    document.getElementById('loginForm');

const loginButton =
    document.getElementById('loginButton');


if (loginForm && loginButton) {

    loginForm.addEventListener(
        'submit',
        function () {

            loginButton.disabled = true;

            loginButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                'Sending verification code...';

        }
    );

}


/*
|--------------------------------------------------------------------------
| OTP TIMER
|--------------------------------------------------------------------------
*/

<?php if ($otpPending): ?>

let otpRemainingSeconds =
    <?= (int)$otpRemainingSeconds ?>;


const otpTimer =
    document.getElementById('otpTimer');


const otpInput =
    document.getElementById('otp');


const otpButton =
    document.getElementById('otpButton');


function updateOtpTimer() {

    if (!otpTimer) {
        return;
    }


    if (otpRemainingSeconds <= 0) {

        otpTimer.textContent =
            '00:00';


        if (otpInput) {
            otpInput.disabled = true;
        }


        if (otpButton) {

            otpButton.disabled = true;

            otpButton.innerHTML =
                'Code expired';

        }


        return;
    }


    const minutes =
        Math.floor(
            otpRemainingSeconds / 60
        );


    const seconds =
        otpRemainingSeconds % 60;


    otpTimer.textContent =
        String(minutes).padStart(2, '0')
        +
        ':'
        +
        String(seconds).padStart(2, '0');


    otpRemainingSeconds--;

}


updateOtpTimer();


const otpInterval =
    setInterval(
        function () {

            updateOtpTimer();

            if (otpRemainingSeconds <= 0) {

                clearInterval(
                    otpInterval
                );

            }

        },
        1000
    );


/*
|--------------------------------------------------------------------------
| OTP INPUT
|--------------------------------------------------------------------------
*/

if (otpInput) {

    otpInput.addEventListener(
        'input',
        function () {

            this.value =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 6);

        }
    );

}


/*
|--------------------------------------------------------------------------
| OTP SUBMIT LOADING
|--------------------------------------------------------------------------
*/

const otpForm =
    document.getElementById('otpForm');


if (otpForm && otpButton) {

    otpForm.addEventListener(
        'submit',
        function () {

            if (
                !otpInput ||
                otpInput.value.length !== 6
            ) {

                return;

            }


            otpButton.disabled = true;

            otpButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                'Verifying...';

        }
    );

}


/*
|--------------------------------------------------------------------------
| FOCUS OTP
|--------------------------------------------------------------------------
*/

if (otpInput) {

    setTimeout(
        function () {

            otpInput.focus();

        },
        150
    );

}

<?php endif; ?>


/*
|--------------------------------------------------------------------------
| ESCAPE KEY
|--------------------------------------------------------------------------
|
| Do NOT close the OTP popup with ESC.
| The user must either complete verification
| or start the login process again.
|
*/

document.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key === 'Escape' &&
            document.getElementById('otpOverlay')
        ) {

            event.preventDefault();

        }

    }
);

</script>

</body>

</html>