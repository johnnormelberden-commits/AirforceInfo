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
*/

$showOtp =
    isset($_GET['otp']) &&
    $_GET['otp'] === '1';

$otpError =
    (string)($_GET['otp_error'] ?? '');

$error =
    (string)($_GET['error'] ?? '');

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
            rgba(0,87,183,.10),
            transparent 30%
        ),
        linear-gradient(
            135deg,
            #edf4fb,
            #f7faff
        );

    color: #172b4d;
}


.login-container {

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 30px 20px;
}


.login-card {

    width: 100%;

    max-width: 1050px;

    min-height: 590px;

    display: flex;

    background: white;

    border-radius: 22px;

    overflow: hidden;

    box-shadow:
        0 25px 70px rgba(19,55,91,.18);

    border: 1px solid rgba(15,62,105,.08);
}


.brand-panel {

    width: 43%;

    padding: 44px 48px;

    color: white;

    background:
        linear-gradient(
            145deg,
            #082d55,
            #06427b 50%,
            #0b5796
        );
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

    background: white;

    overflow: hidden;
}


.logo-wrapper img {

    width: 42px;

    height: 42px;

    object-fit: contain;
}


.brand-name strong {

    display: block;

    font-size: 16px;
}


.brand-name small {

    color: rgba(255,255,255,.65);

    font-size: 10px;
}


.portal-label {

    color: #a9d1f7;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.2px;

    text-transform: uppercase;

    margin-bottom: 15px;
}


.brand-title {

    margin: 0 0 16px;

    font-size: 35px;

    line-height: 1.12;

    font-weight: 800;
}


.brand-description {

    max-width: 370px;

    color: rgba(255,255,255,.68);

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

    background: rgba(2,25,50,.35);

    color: #d9ecff;

    font-size: 10px;
}


.security-dot {

    width: 8px;

    height: 8px;

    border-radius: 50%;

    background: #20d66b;
}


.login-panel {

    width: 57%;

    padding: 50px 65px;

    display: flex;

    align-items: center;
}


.login-content {

    width: 100%;

    max-width: 520px;

    margin: auto;
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
}


.login-subtitle {

    margin: 8px 0 27px;

    color: #7d8b9b;

    font-size: 12px;

    line-height: 1.6;
}


.form-label {

    display: block;

    margin-bottom: 7px;

    color: #24364c;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: .8px;

    text-transform: uppercase;
}


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

    z-index: 2;
}


.form-control {

    width: 100%;

    height: 43px;

    padding: 0 42px;

    border: 1px solid #d6dee7;

    border-radius: 10px;

    background: white !important;

    color: #26384c !important;

    font-size: 12px;
}


.form-control:focus {

    border-color: #428dd1;

    box-shadow:
        0 0 0 3px rgba(66,141,209,.10);
}


.password-toggle {

    position: absolute;

    right: 13px;

    top: 50%;

    transform: translateY(-50%);

    border: 0;

    background: transparent;

    color: #8b99a8;

    cursor: pointer;
}


.remember-row {

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
}


.btn-login {

    width: 100%;

    height: 42px;

    border: 0;

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


.btn-login:disabled {

    opacity: .7;

    cursor: not-allowed;
}


.security-message {

    display: flex;

    justify-content: center;

    gap: 6px;

    margin-top: 13px;

    color: #929daa;

    font-size: 9px;

    text-align: center;
}


.login-footer {

    margin-top: 23px;

    padding-top: 18px;

    border-top: 1px solid #edf0f3;

    text-align: center;

    color: #8d99a6;

    font-size: 8px;

    line-height: 1.6;
}


.error-message {

    margin-top: 15px;

    padding: 10px;

    border-radius: 8px;

    background: #fff1f1;

    border: 1px solid #ffd6d6;

    color: #d63939;

    font-size: 11px;

    text-align: center;
}


/*
|--------------------------------------------------------------------------
| OTP MODAL
|--------------------------------------------------------------------------
*/

.otp-modal {

    position: fixed;

    inset: 0;

    z-index: 9999;

    display: none;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background: rgba(8,35,63,.68);

    backdrop-filter: blur(5px);
}


.otp-modal.show {

    display: flex;
}


.otp-box {

    width: 100%;

    max-width: 430px;

    padding: 32px;

    background: white;

    border-radius: 18px;

    box-shadow:
        0 25px 70px rgba(0,0,0,.25);
}


.otp-icon {

    width: 60px;

    height: 60px;

    margin: 0 auto 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

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

    margin: 10px 0 20px;

    text-align: center;

    color: #7d8b9b;

    font-size: 12px;

    line-height: 1.6;
}


.otp-status {

    min-height: 25px;

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
        0 0 0 3px rgba(66,141,209,.10);
}


.otp-submit {

    width: 100%;

    height: 45px;

    margin-top: 18px;

    border: 0;

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

    opacity: .7;

    cursor: not-allowed;
}


.otp-cancel {

    width: 100%;

    height: 40px;

    margin-top: 10px;

    border: 1px solid #d6dee7;

    border-radius: 9px;

    background: white;

    color: #667789;

    font-size: 11px;
}


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


@media(max-width:800px) {

    .login-card {

        display: block;

        max-width: 500px;
    }

    .brand-panel,
    .login-panel {

        width: 100%;
    }

    .brand-panel {

        min-height: 300px;

        padding: 30px;
    }

    .brand-header {

        margin-bottom: 40px;
    }

    .login-panel {

        padding: 40px 30px;
    }
}


@media(max-width:450px) {

    .login-container {

        padding: 10px;
    }

    .login-panel {

        padding: 35px 22px;
    }

    .brand-panel {

        padding: 25px;
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


<!-- BRAND -->

<div class="brand-panel">

<div class="brand-header">

<div class="logo-wrapper">

<img
    src="cmo1.png"
    alt="CMO Logo"
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


<!-- LOGIN -->

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
    class="form-control"
    placeholder="Enter your email address"
    required
    maxlength="254"
    autocomplete="username"
>

</div>


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
    onclick="togglePassword()"
>

<i
    class="bi bi-eye"
    id="passwordIcon"
></i>

</button>

</div>


<div class="remember-row">

<label class="remember-label">

<input
    type="checkbox"
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


<?php if ($error !== ''): ?>

<div class="error-message">

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
        echo 'The email address or password is incorrect.';
        break;

    case 'email':
        echo 'The verification email could not be sent. Please try again.';
        break;

    case 'db':
        echo 'The authentication system could not access the database.';
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

Your session is protected with two-step verification.

</div>


<div class="login-footer">

Philippine Air Force<br>

CMO Information System

</div>


</div>

</div>

</div>

</div>


<!-- OTP MODAL -->

<div
    class="otp-modal"
    id="otpModal"
>

<div class="otp-box">


<div class="otp-icon">

<i
    class="bi bi-shield-lock-fill"
    id="otpIcon"
></i>

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

<span class="spinner-border spinner-border-sm me-2"></span>

Sending verification code...

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
    required
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
    onclick="cancelOtp()"
>

Cancel and return to login

</button>


<div class="otp-message">

<i class="bi bi-clock"></i>

Code valid for
<strong>10 minutes</strong>.

</div>


</div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| PASSWORD
|--------------------------------------------------------------------------
*/

function togglePassword()
{
    const input =
        document.getElementById('password');

    const icon =
        document.getElementById('passwordIcon');


    if (input.type === 'password') {

        input.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');

    } else {

        input.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');
    }
}


/*
|--------------------------------------------------------------------------
| ELEMENTS
|--------------------------------------------------------------------------
*/

const loginForm =
    document.getElementById('loginForm');

const loginButton =
    document.getElementById('loginButton');

const otpModal =
    document.getElementById('otpModal');

const otpStatus =
    document.getElementById('otpStatus');

const otpInput =
    document.getElementById('otp');

const otpSubmit =
    document.getElementById('otpSubmit');

const otpForm =
    document.getElementById('otpForm');

const otpDescription =
    document.getElementById('otpDescription');


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

loginForm.addEventListener(
    'submit',
    async function(event)
    {
        event.preventDefault();


        /*
        |--------------------------------------------------------------------------
        | SHOW OTP WINDOW IMMEDIATELY
        |--------------------------------------------------------------------------
        */

        otpModal.classList.add('show');


        otpStatus.className =
            'otp-status';


        otpStatus.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>' +
            'Sending verification code...';


        otpInput.value = '';

        otpInput.disabled = true;

        otpSubmit.disabled = true;


        loginButton.disabled = true;


        loginButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>' +
            'Sending verification code...';


        try {

            const formData =
                new FormData(loginForm);


            const response =
                await fetch(
                    'auth.php',
                    {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With':
                                'XMLHttpRequest',
                            'Accept':
                                'application/json'
                        },
                        credentials: 'same-origin'
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | GET RAW RESPONSE FIRST
            |--------------------------------------------------------------------------
            |
            | Do NOT immediately call response.json().
            |
            | If PHP has an error, we can see the actual response.
            |
            |--------------------------------------------------------------------------
            */

            const rawText =
                await response.text();


            let result;


            try {

                result =
                    JSON.parse(rawText);

            } catch (jsonError) {

                console.error(
                    'AUTH.PHP RAW RESPONSE:',
                    rawText
                );

                throw new Error(
                    'The authentication server returned an invalid response.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            if (
                response.ok &&
                result.success === true
            ) {

                otpStatus.className =
                    'otp-status success';


                otpStatus.innerHTML =
                    '<i class="bi bi-check-circle-fill"></i> ' +
                    'Verification code sent successfully.';


                otpInput.disabled = false;

                otpSubmit.disabled = false;


                if (result.email) {

                    otpDescription.innerHTML =
                        'A 6-digit verification code was sent to<br>' +
                        '<strong>' +
                        escapeHtml(result.email) +
                        '</strong>.<br>' +
                        'Please enter the code below.';
                }


                setTimeout(
                    function()
                    {
                        otpInput.focus();
                    },
                    100
                );


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | SERVER REJECTED LOGIN
            |--------------------------------------------------------------------------
            */

            otpStatus.className =
                'otp-status error';


            otpStatus.innerHTML =
                '<i class="bi bi-exclamation-circle-fill"></i> ' +
                escapeHtml(
                    result.message ||
                    'Unable to process the login request.'
                );


            loginButton.disabled = false;


            loginButton.innerHTML =
                'Continue securely ' +
                '<i class="bi bi-arrow-right"></i>';


            setTimeout(
                function()
                {
                    otpModal.classList.remove('show');
                },
                2500
            );


        } catch (error) {

            console.error(
                'LOGIN ERROR:',
                error
            );


            otpStatus.className =
                'otp-status error';


            otpStatus.innerHTML =
                '<i class="bi bi-exclamation-circle-fill"></i> ' +
                escapeHtml(
                    error.message ||
                    'Unable to process the login request.'
                );


            loginButton.disabled = false;


            loginButton.innerHTML =
                'Continue securely ' +
                '<i class="bi bi-arrow-right"></i>';


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Do NOT immediately reload the page.
            |
            | This allows you to see the real error in the browser console.
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
    'input',
    function()
    {

        this.value =
            this.value
                .replace(/\D/g, '')
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
    'submit',
    function()
    {

        if (
            !/^\d{6}$/.test(
                otpInput.value
            )
        ) {

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
| CANCEL
|--------------------------------------------------------------------------
*/

function cancelOtp()
{
    window.location.href = 'login.php';
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value)
{

    const div =
        document.createElement('div');

    div.textContent =
        String(value);

    return div.innerHTML;
}

</script>


</body>

</html>