
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| OTP SESSION VALIDATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['otp_hash']) ||
    !isset($_SESSION['otp_expires']) ||
    !isset($_SESSION['otp_user_id']) ||
    !isset($_SESSION['otp_username'])
) {
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| OTP CLEANUP FUNCTION
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
| CHECK OTP EXPIRATION
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($_SESSION['otp_expires']) ||
    time() >= (int) $_SESSION['otp_expires']
) {
    clearOtpSession();

    header('Location: login.php?error=otp_expired');
    exit;
}


/*
|--------------------------------------------------------------------------
| INITIALIZE ATTEMPTS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}


/*
|--------------------------------------------------------------------------
| HANDLE OTP SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CHECK EXPIRATION AGAIN
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_SESSION['otp_expires']) ||
        !is_numeric($_SESSION['otp_expires']) ||
        time() >= (int) $_SESSION['otp_expires']
    ) {
        clearOtpSession();

        header('Location: login.php?error=otp_expired');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK MAXIMUM ATTEMPTS
    |--------------------------------------------------------------------------
    */

    if ((int) $_SESSION['otp_attempts'] >= 5) {
        clearOtpSession();

        header('Location: login.php?error=otp_attempts');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | GET OTP
    |--------------------------------------------------------------------------
    */

    $otp = trim(
        (string) ($_POST['otp'] ?? '')
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATE OTP FORMAT
    |--------------------------------------------------------------------------
    */

    if (!preg_match('/^\d{6}$/', $otp)) {
        header('Location: verify-otp.php?error=invalid');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY OTP
    |--------------------------------------------------------------------------
    */

    $otpValid = password_verify(
        $otp,
        (string) $_SESSION['otp_hash']
    );


    /*
    |--------------------------------------------------------------------------
    | CORRECT OTP
    |--------------------------------------------------------------------------
    */

    if ($otpValid) {

        /*
        |--------------------------------------------------------------------------
        | SAVE DATA BEFORE CLEARING OTP SESSION
        |--------------------------------------------------------------------------
        */

        $userId = (int) $_SESSION['otp_user_id'];

        $username = (string) $_SESSION['otp_username'];

        $remember = !empty(
            $_SESSION['otp_remember']
        );


        /*
        |--------------------------------------------------------------------------
        | REGENERATE SESSION ID
        |--------------------------------------------------------------------------
        */

        session_regenerate_id(true);


        /*
        |--------------------------------------------------------------------------
        | CREATE AUTHENTICATED SESSION
        |--------------------------------------------------------------------------
        */

        $_SESSION['logged_in'] = true;

        $_SESSION['user_id'] = $userId;

        $_SESSION['username'] = $username;


        /*
        |--------------------------------------------------------------------------
        | CLEAR OTP
        |--------------------------------------------------------------------------
        */

        clearOtpSession();


        /*
        |--------------------------------------------------------------------------
        | REMEMBER ME
        |--------------------------------------------------------------------------
        */

        $secureCookie =
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off';


        if ($remember) {

            setcookie(
                'remember_user',
                $username,
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'secure' => $secureCookie,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );

        } else {

            setcookie(
                'remember_user',
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => $secureCookie,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN COMPLETE
        |--------------------------------------------------------------------------
        */

        header('Location: index.php');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | INCORRECT OTP
    |--------------------------------------------------------------------------
    */

    $_SESSION['otp_attempts'] =
        (int) ($_SESSION['otp_attempts'] ?? 0) + 1;


    /*
    |--------------------------------------------------------------------------
    | MAXIMUM ATTEMPTS REACHED
    |--------------------------------------------------------------------------
    */

    if ((int) $_SESSION['otp_attempts'] >= 5) {

        clearOtpSession();

        header('Location: login.php?error=otp_attempts');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | INVALID OTP
    |--------------------------------------------------------------------------
    */

    header('Location: verify-otp.php?error=invalid');
    exit;
}


/*
|--------------------------------------------------------------------------
| EMAIL
|--------------------------------------------------------------------------
*/

$email = (string) $_SESSION['otp_username'];


/*
|--------------------------------------------------------------------------
| REMAINING TIME
|--------------------------------------------------------------------------
*/

$remainingSeconds = max(
    0,
    (int) $_SESSION['otp_expires'] - time()
);

$remainingMinutes = intdiv(
    $remainingSeconds,
    60
);

$remainingSecondsOnly =
    $remainingSeconds % 60;

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Verify OTP - CMO Information System</title>

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

        .verify-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .verify-card {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border-radius: 20px;
            padding: 45px;
            box-shadow:
                0 25px 70px rgba(19, 55, 91, 0.16),
                0 5px 20px rgba(19, 55, 91, 0.07);
            border:
                1px solid rgba(15, 62, 105, 0.08);
            text-align: center;
        }

        .verify-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef7ff;
            color: #176bb5;
            font-size: 27px;
        }

        .authorized {
            margin-bottom: 8px;
            color: #1764ae;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .verify-title {
            margin: 0;
            color: #10233d;
            font-size: 29px;
            font-weight: 800;
            letter-spacing: -0.8px;
        }

        .verify-subtitle {
            margin: 10px 0 25px;
            color: #7d8b9b;
            font-size: 12px;
            line-height: 1.7;
        }

        .email-display {
            display: inline-block;
            margin-bottom: 25px;
            padding: 8px 13px;
            border-radius: 8px;
            background: #f3f7fb;
            color: #1764ae;
            font-size: 12px;
            font-weight: 600;
            word-break: break-word;
        }

        .otp-input {
            width: 100%;
            height: 58px;
            border: 1px solid #d6dee7;
            border-radius: 10px;
            outline: none;
            text-align: center;
            letter-spacing: 10px;
            padding-left: 10px;
            color: #172b4d;
            font-size: 25px;
            font-weight: 700;
            background: #ffffff;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .otp-input:focus {
            border-color: #428dd1;
            box-shadow:
                0 0 0 3px
                rgba(66,141,209,0.10);
        }

        .otp-input::placeholder {
            color: #b4bec9;
            letter-spacing: 8px;
        }

        .btn-verify {
            width: 100%;
            height: 44px;
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
            box-shadow:
                0 7px 15px
                rgba(15, 91, 157, 0.20);
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

        .error-message {
            margin-top: 17px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #fff1f1;
            border: 1px solid #ffd6d6;
            color: #d63939;
            font-size: 11px;
            text-align: center;
        }

        .otp-timer {
            margin-top: 17px;
            color: #929daa;
            font-size: 10px;
        }

        .otp-timer strong {
            color: #1764ae;
            font-weight: 700;
        }

        .security-message {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 25px;
            color: #929daa;
            font-size: 9px;
            line-height: 1.5;
        }

        .security-message i {
            color: #8896a5;
        }

        .verify-footer {
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px solid #edf0f3;
            color: #8d99a6;
            font-size: 8px;
            line-height: 1.6;
        }

        @media (max-width: 500px) {

            .verify-container {
                padding: 15px;
            }

            .verify-card {
                padding: 35px 25px;
                border-radius: 17px;
            }

            .verify-title {
                font-size: 26px;
            }

            .otp-input {
                height: 55px;
                font-size: 22px;
                letter-spacing: 7px;
            }
        }

    </style>

</head>

<body>

<div class="verify-container">

    <div class="verify-card">

        <div class="verify-icon">
            <i class="bi bi-envelope-check-fill"></i>
        </div>

        <div class="authorized">
            Two-Step Verification
        </div>

        <h1 class="verify-title">
            Check your email
        </h1>

        <p class="verify-subtitle">
            We sent a 6-digit verification code
            to the email address associated with
            your account.
        </p>

        <div class="email-display">

            <i class="bi bi-envelope"></i>

            <?php
            echo htmlspecialchars(
                $email,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
            ?>

        </div>


        <!-- IMPORTANT: hyphen, not underscore -->

        <form
            action="verify-otp.php"
            method="POST"
            autocomplete="off"
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
                class="btn-verify"
            >
                Verify and Sign In
                <i class="bi bi-arrow-right ms-1"></i>
            </button>

        </form>


        <?php if (isset($_GET['error'])): ?>

            <div class="error-message">

                <?php

                switch ($_GET['error']) {

                    case 'invalid':

                        echo '
                            <i class="bi bi-exclamation-circle"></i>
                            Incorrect verification code.
                            Please try again.
                        ';

                        break;

                    default:

                        echo '
                            <i class="bi bi-exclamation-circle"></i>
                            Unable to verify the code.
                            Please try again.
                        ';

                        break;
                }

                ?>

            </div>

        <?php endif; ?>


        <div class="otp-timer">

            Code expires in

            <strong id="timer">

                <?php

                printf(
                    '%02d:%02d',
                    $remainingMinutes,
                    $remainingSecondsOnly
                );

                ?>

            </strong>

        </div>


        <div class="security-message">

            <i class="bi bi-shield-lock-fill"></i>

            Never share your verification code with anyone.

        </div>


        <div class="verify-footer">

            <div>
                Philippine Air Force
            </div>

            <div>
                CMO Information System
            </div>

        </div>

    </div>

</div>


<script>

let remainingSeconds =
    <?php echo (int) $remainingSeconds; ?>;

const timer =
    document.getElementById("timer");


function updateTimer() {

    if (remainingSeconds <= 0) {

        timer.textContent = "00:00";

        const otpInput =
            document.getElementById("otp");

        const verifyButton =
            document.querySelector(".btn-verify");

        if (otpInput) {
            otpInput.disabled = true;
        }

        if (verifyButton) {
            verifyButton.disabled = true;
            verifyButton.style.opacity = "0.6";
            verifyButton.style.cursor = "not-allowed";
        }

        return;
    }


    const minutes =
        Math.floor(remainingSeconds / 60);

    const seconds =
        remainingSeconds % 60;


    timer.textContent =
        String(minutes).padStart(2, "0")
        + ":"
        + String(seconds).padStart(2, "0");


    remainingSeconds--;
}


updateTimer();


const timerInterval = setInterval(
    function () {

        updateTimer();

        if (remainingSeconds <= 0) {
            clearInterval(timerInterval);
        }

    },
    1000
);


/*
|--------------------------------------------------------------------------
| ALLOW ONLY NUMBERS
|--------------------------------------------------------------------------
*/

const otpInput =
    document.getElementById("otp");


if (otpInput) {

    otpInput.addEventListener(
        "input",
        function () {

            this.value =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 6);

        }
    );

}

</script>

</body>

</html>