<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['logged_in']) &&
    $_SESSION['logged_in'] === true &&
    !empty($_SESSION['username'])
) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| URL STATE
|--------------------------------------------------------------------------
|
| IMPORTANT:
| A normal visit to login.php has NO error.
|
| Example:
| login.php
|
| will show the login page normally.
|
| An error is shown ONLY when:
| login.php?error=...
|
|--------------------------------------------------------------------------
*/

$error = '';

if (isset($_GET['error'])) {
    $error = trim((string) $_GET['error']);
}


$showOtp = (
    isset($_GET['otp']) &&
    $_GET['otp'] === '1'
);


$otpError = '';

if (isset($_GET['otp_error'])) {
    $otpError = trim((string) $_GET['otp_error']);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>CMO Information System - Login</title>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    font-family: 'Inter', Arial, sans-serif;

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


/* =========================================================
   LOGIN CONTAINER
========================================================= */

.login-container {

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 35px 20px;
}


/* =========================================================
   LOGIN CARD
========================================================= */

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

    border: 1px solid rgba(15, 62, 105, 0.08);
}


/* =========================================================
   BRAND PANEL
========================================================= */

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

    border: 35px solid rgba(255,255,255,0.035);

    pointer-events: none;
}


.brand-content {

    position: relative;

    z-index: 2;
}


/* =========================================================
   BRAND HEADER
========================================================= */

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


/* =========================================================
   PORTAL
========================================================= */

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


/* =========================================================
   BRAND TITLE
========================================================= */

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


/* =========================================================
   SECURITY BADGE
========================================================= */

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


/* =========================================================
   LOGIN PANEL
========================================================= */

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


/* =========================================================
   LOGIN TEXT
========================================================= */

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


/* =========================================================
   LABEL
========================================================= */

.form-label {

    display: block;

    margin-bottom: 7px;

    color: #24364c;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 0.8px;

    text-transform: uppercase;
}


/* =========================================================
   INPUT
========================================================= */

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

    border: 1px solid #d6dee7;

    border-radius: 10px;

    background: #ffffff !important;

    color: #26384c !important;

    font-size: 12px;

    box-shadow: none;

    transition: all 0.2s ease;
}


.form-control::placeholder {

    color: #9aa6b4 !important;
}


.form-control:focus {

    border-color: #428dd1;

    box-shadow:
        0 0 0 3px rgba(66,141,209,0.10);

    background: #ffffff !important;
}


.email-input {

    padding-right: 15px;
}


/* =========================================================
   PASSWORD
========================================================= */

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


.password-toggle:hover {

    color: #1764ae;
}


/* =========================================================
   REMEMBER
========================================================= */

.remember-row {

    display: flex;

    align-items: center;

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


/* =========================================================
   LOGIN BUTTON
========================================================= */

.btn-login {

    width: 100%;

    height: 42px;

    margin-top: 2px;

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
        0 7px 15px rgba(15, 91, 157, 0.20);

    transition: all 0.2s ease;
}


.btn-login:hover {

    background:
        linear-gradient(
            90deg,
            #08487f,
            #0d5da0
        );

    transform: translateY(-1px);
}


.btn-login:disabled {

    opacity: 0.7;

    cursor: not-allowed;

    transform: none;
}


/* =========================================================
   SECURITY
========================================================= */

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


/* =========================================================
   FOOTER
========================================================= */

.login-footer {

    margin-top: 23px;

    padding-top: 18px;

    border-top: 1px solid #edf0f3;

    text-align: center;

    color: #8d99a6;

    font-size: 8px;

    line-height: 1.6;
}


/* =========================================================
   ERROR
========================================================= */

.error-message {

    margin-top: 15px;

    padding: 10px 12px;

    border-radius: 8px;

    background: #fff1f1;

    border: 1px solid #ffd6d6;

    color: #d63939;

    font-size: 11px;

    text-align: center;
}


/* =========================================================
   OTP MODAL
========================================================= */

.otp-modal {

    position: fixed;

    inset: 0;

    z-index: 9999;

    display: none;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        rgba(8, 35, 63, 0.68);

    backdrop-filter: blur(5px);
}


.otp-modal.show {

    display: flex;
}


.otp-box {

    width: 100%;

    max-width: 430px;

    background: #ffffff;

    border-radius: 18px;

    padding: 32px;

    box-shadow:
        0 25px 70px rgba(0,0,0,0.25);

    animation: otpAppear 0.25s ease;
}


@keyframes otpAppear {

    from {

        opacity: 0;

        transform: translateY(15px) scale(0.97);

    }

    to {

        opacity: 1;

        transform: translateY(0) scale(1);

    }
}


.otp-icon {

    width: 60px;

    height: 60px;

    margin: 0 auto 18px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eef7ff;

    color: #1764ae;

    font-size: 25px;
}


.otp-title {

    margin: 0;

    text-align: center;

    color: #10233d;

    font-size: 23px;

    font-weight: 800;
}


.otp-description {

    margin: 10px 0 22px;

    text-align: center;

    color: #7d8b9b;

    font-size: 12px;

    line-height: 1.6;
}


/* =========================================================
   OTP STATUS
========================================================= */

.otp-status {

    min-height: 22px;

    margin-bottom: 14px;

    text-align: center;

    color: #1764ae;

    font-size: 11px;
}


.otp-status.success {

    color: #21874b;
}


.otp-status.error {

    color: #d63939;
}


/* =========================================================
   OTP INPUT
========================================================= */

.otp-input {

    width: 100%;

    height: 58px;

    border: 1px solid #d6dee7;

    border-radius: 10px;

    text-align: center;

    letter-spacing: 10px;

    font-size: 25px;

    font-weight: 700;

    color: #10233d;

    outline: none;
}


.otp-input:focus {

    border-color: #428dd1;

    box-shadow:
        0 0 0 3px rgba(66,141,209,0.10);
}


.otp-input:disabled {

    background: #f6f8fa;

    cursor: not-allowed;
}


/* =========================================================
   OTP BUTTON
========================================================= */

.otp-submit {

    width: 100%;

    height: 45px;

    margin-top: 18px;

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
}


.otp-submit:disabled {

    opacity: 0.7;

    cursor: not-allowed;
}


/* =========================================================
   OTP CANCEL
========================================================= */

.otp-cancel {

    width: 100%;

    height: 40px;

    margin-top: 10px;

    border: 1px solid #d6dee7;

    border-radius: 9px;

    background: #ffffff;

    color: #667789;

    font-size: 11px;

    cursor: pointer;
}


/* =========================================================
   OTP MESSAGE
========================================================= */

.otp-message {

    margin-top: 15px;

    padding: 10px;

    border-radius: 8px;

    background: #effbf4;

    border: 1px solid #c8efd6;

    color: #21874b;

    text-align: center;

    font-size: 11px;
}


/* =========================================================
   MOBILE
========================================================= */

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

    .otp-box {
        padding: 25px 20px;
    }
}

</style>

</head>


<body>


<div class="login-container">

<div class="login-card">


<!-- =====================================================
     BRAND
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
     LOGIN
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


<form
    action="auth.php"
    method="POST"
    id="loginForm"
    autocomplete="on"
>


<!-- EMAIL -->

<div>

<label
    for="username"
    class="form-label"
>
Email Address
</label>


<div class="input-wrapper">

<i class="bi bi-envelope input-icon"></i>

<input
    type="email"
    id="username"
    name="username"
    class="form-control email-input"
    placeholder="Enter your email address"
    required
    autocomplete="username"
    inputmode="email"
    maxlength="254"
>

</div>

</div>


<!-- PASSWORD -->

<div>

<label
    for="password"
    class="form-label"
>
Password
</label>


<div class="input-wrapper">

<i class="bi bi-lock input-icon"></i>


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
    id="passwordToggle"
    aria-label="Show password"
>

<i
    class="bi bi-eye"
    id="passwordIcon"
></i>

</button>

</div>

</div>


<!-- REMEMBER -->

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


<!-- LOGIN BUTTON -->

<button
    type="submit"
    class="btn-login"
    id="loginButton"
>

Continue securely

<i class="bi bi-arrow-right"></i>

</button>

</form>


<!-- =====================================================
     SERVER ERROR
====================================================== -->

<?php if ($error !== ''): ?>

<div
    class="error-message"
    id="serverError"
>

<i class="bi bi-exclamation-circle"></i>

<?php

switch ($error) {

    case 'empty':

        echo 'Please enter your email address and password.';

        break;


    case 'invalid_email':

        echo 'Please enter a valid email address.';

        break;


    case 'invalid':

        echo 'Invalid email address or password.';

        break;


    case 'email':

        echo 'We could not send the verification code. Please try again.';

        break;


    case 'db':

        echo 'A database error occurred. Please try again later.';

        break;


    case 'otp_session':

        echo 'Your verification session has expired. Please sign in again.';

        break;


    default:

        echo 'Unable to process the login request. Please try again.';

        break;
}

?>

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
     OTP MODAL
========================================================= -->

<div
    class="otp-modal <?= $showOtp ? 'show' : ''; ?>"
    id="otpModal"
>

<div class="otp-box">


<div class="otp-icon">

<i class="bi bi-shield-lock-fill"></i>

</div>


<h3 class="otp-title">
Verify Your Login
</h3>


<p
    class="otp-description"
    id="otpDescription"
>

We are sending a 6-digit verification
code to your registered email address.

</p>


<div
    class="otp-status"
    id="otpStatus"
>
</div>


<form
    action="verify-otp.php"
    method="POST"
    id="otpForm"
>


<input
    type="text"
    name="otp"
    id="otp"
    class="otp-input"
    inputmode="numeric"
    autocomplete="one-time-code"
    maxlength="6"
    pattern="[0-9]{6}"
    placeholder="••••••"
    disabled
>


<button
    type="submit"
    class="otp-submit"
    id="otpSubmit"
    disabled
>

Verify & Continue

<i class="bi bi-arrow-right"></i>

</button>

</form>


<button
    type="button"
    class="otp-cancel"
    id="otpCancel"
>

Cancel and return to login

</button>


<div class="otp-message">

<i class="bi bi-clock"></i>

The verification code is valid for
<strong>10 minutes</strong>.

</div>


</div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| ELEMENTS
|--------------------------------------------------------------------------
*/

const loginForm =
    document.getElementById("loginForm");

const loginButton =
    document.getElementById("loginButton");

const password =
    document.getElementById("password");

const passwordToggle =
    document.getElementById("passwordToggle");

const passwordIcon =
    document.getElementById("passwordIcon");

const otpModal =
    document.getElementById("otpModal");

const otpStatus =
    document.getElementById("otpStatus");

const otpDescription =
    document.getElementById("otpDescription");

const otpInput =
    document.getElementById("otp");

const otpSubmit =
    document.getElementById("otpSubmit");

const otpForm =
    document.getElementById("otpForm");

const otpCancel =
    document.getElementById("otpCancel");


/*
|--------------------------------------------------------------------------
| PASSWORD TOGGLE
|--------------------------------------------------------------------------
*/

passwordToggle.addEventListener(
    "click",
    function () {

        if (password.type === "password") {

            password.type = "text";

            passwordIcon.classList.remove(
                "bi-eye"
            );

            passwordIcon.classList.add(
                "bi-eye-slash"
            );

        } else {

            password.type = "password";

            passwordIcon.classList.remove(
                "bi-eye-slash"
            );

            passwordIcon.classList.add(
                "bi-eye"
            );
        }

    }
);


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    const div =
        document.createElement("div");

    div.textContent =
        String(value);

    return div.innerHTML;
}


/*
|--------------------------------------------------------------------------
| RESET LOGIN BUTTON
|--------------------------------------------------------------------------
*/

function resetLoginButton() {

    loginButton.disabled = false;

    loginButton.innerHTML =
        'Continue securely ' +
        '<i class="bi bi-arrow-right"></i>';
}


/*
|--------------------------------------------------------------------------
| LOGIN AJAX
|--------------------------------------------------------------------------
|
| OTP POPUP OPENS IMMEDIATELY.
|
|--------------------------------------------------------------------------
*/

loginForm.addEventListener(
    "submit",
    async function (event) {

        event.preventDefault();


        /*
        |--------------------------------------------------------------------------
        | CLIENT VALIDATION
        |--------------------------------------------------------------------------
        */

        if (!loginForm.checkValidity()) {

            loginForm.reportValidity();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | OPEN OTP MODAL IMMEDIATELY
        |--------------------------------------------------------------------------
        */

        otpModal.classList.add("show");


        otpStatus.className =
            "otp-status";


        otpStatus.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>' +
            'Checking your login...';


        otpDescription.innerHTML =
            'Please wait while we verify your account ' +
            'and send the verification code.';


        otpInput.value = "";

        otpInput.disabled = true;

        otpSubmit.disabled = true;


        loginButton.disabled = true;

        loginButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>' +
            'Processing...';


        try {

            const formData =
                new FormData(loginForm);


            const response =
                await fetch(
                    "auth.php",
                    {
                        method: "POST",

                        body: formData,

                        credentials: "same-origin",

                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        }
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | READ AS TEXT FIRST
            |--------------------------------------------------------------------------
            |
            | This prevents response.json() from hiding PHP errors.
            |
            |--------------------------------------------------------------------------
            */

            const responseText =
                await response.text();


            console.log(
                "AUTH HTTP STATUS:",
                response.status
            );

            console.log(
                "AUTH RESPONSE:",
                responseText
            );


            /*
            |--------------------------------------------------------------------------
            | SERVER HTTP ERROR
            |--------------------------------------------------------------------------
            */

            if (!response.ok) {

                throw new Error(
                    "The login server returned HTTP " +
                    response.status +
                    ". Check the PHP error log."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | EMPTY RESPONSE
            |--------------------------------------------------------------------------
            */

            if (!responseText.trim()) {

                throw new Error(
                    "auth.php returned an empty response."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | PARSE JSON
            |--------------------------------------------------------------------------
            */

            let result;

            try {

                result =
                    JSON.parse(responseText);

            } catch (jsonError) {

                console.error(
                    "INVALID JSON FROM AUTH.PHP:",
                    responseText
                );

                throw new Error(
                    "The login server returned an invalid response. Check auth.php for a PHP error."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            if (
                result.success === true
            ) {

                otpStatus.className =
                    "otp-status success";


                otpStatus.innerHTML =
                    '<i class="bi bi-check-circle-fill"></i> ' +
                    'Verification code sent successfully.';


                if (result.email) {

                    otpDescription.innerHTML =
                        'A 6-digit verification code was sent to<br>' +
                        '<strong>' +
                        escapeHtml(result.email) +
                        '</strong>.<br>' +
                        'Please enter the code below.';

                } else {

                    otpDescription.innerHTML =
                        'A 6-digit verification code was sent to ' +
                        'your registered email address.<br>' +
                        'Please enter the code below.';
                }


                otpInput.disabled = false;

                otpSubmit.disabled = true;


                setTimeout(
                    function () {

                        otpInput.focus();

                    },
                    150
                );


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                |
                | Keep login button disabled while OTP is being verified.
                |
                |--------------------------------------------------------------------------
                */

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | AUTHENTICATION FAILURE
            |--------------------------------------------------------------------------
            */

            otpStatus.className =
                "otp-status error";


            otpStatus.innerHTML =
                '<i class="bi bi-exclamation-circle-fill"></i> ' +
                escapeHtml(
                    result.message ||
                    "Unable to process the login request."
                );


            resetLoginButton();


            /*
            |--------------------------------------------------------------------------
            | CLOSE MODAL ONLY AFTER USER SEES ERROR
            |--------------------------------------------------------------------------
            */

            setTimeout(
                function () {

                    otpModal.classList.remove("show");

                },
                3000
            );

        } catch (error) {

            console.error(
                "LOGIN REQUEST ERROR:",
                error
            );


            otpStatus.className =
                "otp-status error";


            otpStatus.innerHTML =
                '<i class="bi bi-exclamation-circle-fill"></i> ' +
                escapeHtml(
                    error.message ||
                    "Unable to process the login request."
                );


            resetLoginButton();


            /*
            |--------------------------------------------------------------------------
            | DO NOT IMMEDIATELY REDIRECT
            |--------------------------------------------------------------------------
            |
            | The user can see the actual problem.
            |
            |--------------------------------------------------------------------------
            */

        }

    }
);


/*
|--------------------------------------------------------------------------
| OTP INPUT
|--------------------------------------------------------------------------
*/

otpInput.addEventListener(
    "input",
    function () {

        this.value =
            this.value
                .replace(/\D/g, "")
                .slice(0, 6);


        otpSubmit.disabled =
            this.value.length !== 6;

    }
);


/*
|--------------------------------------------------------------------------
| OTP SUBMIT
|--------------------------------------------------------------------------
*/

otpForm.addEventListener(
    "submit",
    function (event) {

        if (
            otpInput.value.length !== 6
        ) {

            event.preventDefault();

            return;
        }


        otpSubmit.disabled = true;

        otpSubmit.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>' +
            'Verifying...';

    }
);


/*
|--------------------------------------------------------------------------
| CANCEL OTP
|--------------------------------------------------------------------------
*/

otpCancel.addEventListener(
    "click",
    function () {

        window.location.href =
            "login.php";

    }
);


/*
|--------------------------------------------------------------------------
| EXISTING OTP ERROR
|--------------------------------------------------------------------------
*/

<?php if ($showOtp && $otpError !== ''): ?>

window.addEventListener(
    "load",
    function () {

        otpModal.classList.add("show");

        otpInput.disabled = false;


        <?php if ($otpError === 'invalid'): ?>

        otpStatus.className =
            "otp-status error";

        otpStatus.innerHTML =
            '<i class="bi bi-exclamation-circle-fill"></i> ' +
            'The verification code is incorrect.';


        <?php elseif ($otpError === 'expired'): ?>

        otpStatus.className =
            "otp-status error";

        otpStatus.innerHTML =
            '<i class="bi bi-clock-fill"></i> ' +
            'Your verification code has expired. Please sign in again.';


        <?php elseif ($otpError === 'attempts'): ?>

        otpStatus.className =
            "otp-status error";

        otpStatus.innerHTML =
            '<i class="bi bi-shield-x"></i> ' +
            'Too many incorrect attempts. Please sign in again.';


        <?php else: ?>

        otpStatus.className =
            "otp-status error";

        otpStatus.innerHTML =
            '<i class="bi bi-exclamation-circle-fill"></i> ' +
            'Unable to verify the code. Please try again.';

        <?php endif; ?>


        otpInput.focus();

    }
);

<?php endif; ?>

</script>


</body>

</html>