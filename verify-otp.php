<?php

session_start();

/*
|--------------------------------------------------------------------------
| MAKE SURE AN OTP LOGIN IS IN PROGRESS
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['otp_hash']) ||
    !isset($_SESSION['otp_expires']) ||
    !isset($_SESSION['otp_user_id']) ||
    !isset($_SESSION['otp_username'])
) {

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK OTP EXPIRATION
|--------------------------------------------------------------------------
*/

if (time() > $_SESSION['otp_expires']) {

    unset(
        $_SESSION['otp_hash'],
        $_SESSION['otp_expires'],
        $_SESSION['otp_user_id'],
        $_SESSION['otp_username'],
        $_SESSION['otp_remember'],
        $_SESSION['otp_attempts']
    );

    header("Location: login.php?error=otp_expired");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET DISPLAY EMAIL
|--------------------------------------------------------------------------
*/

$email = $_SESSION['otp_username'];


/*
|--------------------------------------------------------------------------
| MASK EMAIL
|--------------------------------------------------------------------------
|
| Example:
|
| john.doe@gmail.com
| becomes
| j*******e@gmail.com
|
*/

function maskEmail($email)
{
    $parts = explode('@', $email, 2);

    if (count($parts) !== 2) {
        return $email;
    }

    $name = $parts[0];
    $domain = $parts[1];

    $length = strlen($name);

    if ($length <= 2) {

        $maskedName =
            substr($name, 0, 1) . '***';

    } else {

        $maskedName =
            substr($name, 0, 1) .
            str_repeat('*', max(1, $length - 2)) .
            substr($name, -1);
    }

    return $maskedName . '@' . $domain;
}


$maskedEmail = maskEmail($email);


/*
|--------------------------------------------------------------------------
| PROCESS OTP
|--------------------------------------------------------------------------
*/

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp = trim($_POST['otp'] ?? '');


    /*
     * ==============================================================
     * BASIC OTP VALIDATION
     * ==============================================================
     */

    if (!preg_match('/^\d{6}$/', $otp)) {

        $error = 'Please enter the 6-digit verification code.';

    } else {


        /*
         * ==========================================================
         * OTP ATTEMPT LIMIT
         * ==========================================================
         *
         * Maximum of 5 incorrect attempts.
         *
         */

        if (!isset($_SESSION['otp_attempts'])) {

            $_SESSION['otp_attempts'] = 0;

        }


        if ($_SESSION['otp_attempts'] >= 5) {

            unset(
                $_SESSION['otp_hash'],
                $_SESSION['otp_expires'],
                $_SESSION['otp_user_id'],
                $_SESSION['otp_username'],
                $_SESSION['otp_remember'],
                $_SESSION['otp_attempts']
            );

            header("Location: login.php?error=otp_attempts");
            exit;
        }


        /*
         * ==========================================================
         * CHECK OTP
         * ==========================================================
         */

        if (
            password_verify(
                $otp,
                $_SESSION['otp_hash']
            )
        ) {

            /*
             * ======================================================
             * OTP CORRECT
             * ======================================================
             *
             * Regenerate session ID to prevent session fixation.
             *
             */

            session_regenerate_id(true);


            /*
             * ======================================================
             * CREATE AUTHENTICATED SESSION
             * ======================================================
             */

            $_SESSION['logged_in'] = true;

            $_SESSION['user_id'] =
                (int) $_SESSION['otp_user_id'];

            $_SESSION['username'] =
                $_SESSION['otp_username'];


            /*
             * ======================================================
             * REMEMBER ME
             * ======================================================
             *
             * For now this preserves the remember_user behavior.
             *
             * IMPORTANT:
             * The production version should use a random token
             * instead of storing the username directly in a cookie.
             *
             */

            if (
                isset($_SESSION['otp_remember']) &&
                $_SESSION['otp_remember'] === true
            ) {

                setcookie(
                    "remember_user",
                    $_SESSION['username'],
                    [
                        'expires'  => time() + (86400 * 30),
                        'path'     => '/',
                        'secure'   => (
                            !empty($_SERVER['HTTPS']) &&
                            $_SERVER['HTTPS'] !== 'off'
                        ),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );

            } else {

                /*
                 * Remove old Remember Me cookie.
                 */

                setcookie(
                    "remember_user",
                    "",
                    [
                        'expires'  => time() - 3600,
                        'path'     => '/',
                        'secure'   => (
                            !empty($_SERVER['HTTPS']) &&
                            $_SERVER['HTTPS'] !== 'off'
                        ),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            }


            /*
             * ======================================================
             * REMOVE TEMPORARY OTP DATA
             * ======================================================
             */

            unset(
                $_SESSION['otp_hash'],
                $_SESSION['otp_expires'],
                $_SESSION['otp_user_id'],
                $_SESSION['otp_username'],
                $_SESSION['otp_remember'],
                $_SESSION['otp_attempts']
            );


            /*
             * ======================================================
             * LOGIN COMPLETE
             * ======================================================
             */

            header("Location: index.php");
            exit;

        } else {

            /*
             * ======================================================
             * WRONG OTP
             * ======================================================
             */

            $_SESSION['otp_attempts']++;

            $remaining =
                5 - $_SESSION['otp_attempts'];


            if ($remaining <= 0) {

                unset(
                    $_SESSION['otp_hash'],
                    $_SESSION['otp_expires'],
                    $_SESSION['otp_user_id'],
                    $_SESSION['otp_username'],
                    $_SESSION['otp_remember'],
                    $_SESSION['otp_attempts']
                );

                header("Location: login.php?error=otp_attempts");
                exit;

            }


            $error =
                "Invalid verification code. " .
                $remaining .
                " attempt(s) remaining.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| REMAINING TIME
|--------------------------------------------------------------------------
*/

$remainingSeconds =
    max(
        0,
        $_SESSION['otp_expires'] - time()
    );

$remainingMinutes =
    floor($remainingSeconds / 60);

$remainingSecondsDisplay =
    $remainingSeconds % 60;

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Verify Email - CMO Information System</title>

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


.verify-container {

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 25px;
}


.verify-card {

    width: 100%;

    max-width: 470px;

    background: #ffffff;

    border-radius: 20px;

    padding: 45px 45px 35px;

    box-shadow:
        0 25px 70px rgba(19, 55, 91, 0.18),
        0 5px 20px rgba(19, 55, 91, 0.08);

    border:
        1px solid rgba(15, 62, 105, 0.08);

    text-align: center;
}


.logo-wrapper {

    width: 58px;

    height: 58px;

    margin: 0 auto 22px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #ffffff;

    border-radius: 13px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.12);

    border:
        1px solid #edf0f3;

    overflow: hidden;
}


.logo-wrapper img {

    width: 50px;

    height: 50px;

    object-fit: contain;
}


.authorized {

    margin-bottom: 7px;

    color: #1764ae;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.4px;

    text-transform: uppercase;
}


.title {

    margin: 0;

    color: #10233d;

    font-size: 29px;

    font-weight: 800;

    letter-spacing: -0.8px;
}


.subtitle {

    margin: 10px auto 25px;

    max-width: 340px;

    color: #7d8b9b;

    font-size: 12px;

    line-height: 1.7;
}


.email {

    display: inline-block;

    padding: 7px 12px;

    margin-bottom: 23px;

    border-radius: 20px;

    background: #eef7ff;

    color: #1764ae;

    font-size: 11px;

    font-weight: 600;
}


.form-label {

    display: block;

    margin-bottom: 8px;

    color: #24364c;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 0.8px;

    text-transform: uppercase;
}


.otp-input {

    width: 100%;

    height: 55px;

    border:
        1px solid #d6dee7;

    border-radius: 11px;

    text-align: center;

    font-size: 25px;

    font-weight: 700;

    letter-spacing: 9px;

    color: #26384c;

    outline: none;

    transition: all 0.2s ease;
}


.otp-input:focus {

    border-color: #428dd1;

    box-shadow:
        0 0 0 3px rgba(66,141,209,0.10);
}


.otp-input::placeholder {

    color: #c2cbd4;

    letter-spacing: 7px;
}


.btn-verify {

    width: 100%;

    height: 44px;

    margin-top: 20px;

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


.btn-verify:hover {

    background:
        linear-gradient(
            90deg,
            #08487f,
            #0d5da0
        );

    transform: translateY(-1px);
}


.btn-verify i {

    margin-left: 7px;
}


.timer {

    margin-top: 17px;

    color: #929daa;

    font-size: 10px;
}


.timer strong {

    color: #1764ae;
}


.error-message {

    margin-bottom: 17px;

    padding: 10px 12px;

    border-radius: 8px;

    background: #fff1f1;

    border:
        1px solid #ffd6d6;

    color: #d63939;

    font-size: 11px;

    line-height: 1.5;
}


.security-message {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 6px;

    margin-top: 25px;

    padding-top: 18px;

    border-top: 1px solid #edf0f3;

    color: #929daa;

    font-size: 9px;
}


.security-message i {

    color: #8896a5;
}


.back-login {

    display: inline-block;

    margin-top: 15px;

    color: #1764ae;

    font-size: 10px;

    font-weight: 600;

    text-decoration: none;
}


.back-login:hover {

    color: #08487f;

}


@media (max-width: 500px) {

    .verify-container {

        padding: 15px;
    }


    .verify-card {

        padding: 35px 25px 28px;
    }


    .title {

        font-size: 26px;
    }


    .otp-input {

        font-size: 22px;

        letter-spacing: 7px;
    }

}

</style>

</head>


<body>


<div class="verify-container">

    <div class="verify-card">


        <!-- LOGO -->

        <div class="logo-wrapper">

            <img
                src="cmo1.png"
                alt="Philippine Air Force CMO Logo"
            >

        </div>


        <!-- LABEL -->

        <div class="authorized">

            Email Verification

        </div>


        <!-- TITLE -->

        <h1 class="title">

            Verify your account

        </h1>


        <!-- DESCRIPTION -->

        <p class="subtitle">

            We sent a 6-digit verification code
            to the email address associated with
            your account.

        </p>


        <!-- MASKED EMAIL -->

        <div class="email">

            <i class="bi bi-envelope"></i>

            <?php echo htmlspecialchars($maskedEmail); ?>

        </div>


        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div class="error-message">

                <i class="bi bi-exclamation-circle"></i>

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- OTP FORM -->

        <form
            method="POST"
            action="verify-otp.php"
            autocomplete="off"
        >

            <label
                for="otp"
                class="form-label"
            >

                Verification Code

            </label>


            <input
                type="text"
                id="otp"
                name="otp"
                class="otp-input"
                placeholder="••••••"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                autocomplete="one-time-code"
                required
                autofocus
            >


            <button
                type="submit"
                class="btn-verify"
            >

                Verify and Sign In

                <i class="bi bi-shield-check"></i>

            </button>

        </form>


        <!-- TIMER -->

        <div
            class="timer"
            id="timer"
            data-seconds="<?php echo $remainingSeconds; ?>"
        >

            Code expires in

            <strong>
                <span id="minutes">
                    <?php echo str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT); ?>
                </span>:<span id="seconds">
                    <?php echo str_pad($remainingSecondsDisplay, 2, '0', STR_PAD_LEFT); ?>
                </span>
            </strong>

        </div>


        <!-- SECURITY -->

        <div class="security-message">

            <i class="bi bi-shield-lock-fill"></i>

            Two-step verification protects your account.

        </div>


        <!-- BACK -->

        <a
            href="login.php"
            class="back-login"
        >

            <i class="bi bi-arrow-left"></i>

            Back to login

        </a>


    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| OTP INPUT
|--------------------------------------------------------------------------
|
| Allow numbers only.
|
*/

const otpInput =
    document.getElementById('otp');


otpInput.addEventListener('input', function () {

    this.value =
        this.value
            .replace(/\D/g, '')
            .substring(0, 6);

});


/*
|--------------------------------------------------------------------------
| COUNTDOWN TIMER
|--------------------------------------------------------------------------
*/

const timer =
    document.getElementById('timer');

let remaining =
    parseInt(
        timer.dataset.seconds,
        10
    );


const minutes =
    document.getElementById('minutes');

const seconds =
    document.getElementById('seconds');


function updateTimer() {

    if (remaining <= 0) {

        minutes.textContent = '00';

        seconds.textContent = '00';

        timer.innerHTML =
            '<strong style="color:#d63939;">' +
            'Verification code expired.' +
            '</strong>';

        otpInput.disabled = true;

        return;
    }


    const mins =
        Math.floor(remaining / 60);

    const secs =
        remaining % 60;


    minutes.textContent =
        String(mins).padStart(2, '0');


    seconds.textContent =
        String(secs).padStart(2, '0');


    remaining--;

    setTimeout(
        updateTimer,
        1000
    );
}


updateTimer();

</script>


</body>

</html>
