```php
<?php

session_start();

/*
|--------------------------------------------------------------------------
| CHECK OTP SESSION
|--------------------------------------------------------------------------
|
| The user can only reach this page after auth.php successfully:
|
| 1. Verified the email
| 2. Verified the password
| 3. Generated an OTP
| 4. Sent the OTP by email
|
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

if (
    !is_numeric($_SESSION['otp_expires']) ||
    time() >= (int) $_SESSION['otp_expires']
) {

    /*
     * Remove OTP information.
     */

    unset($_SESSION['otp_hash']);
    unset($_SESSION['otp_expires']);
    unset($_SESSION['otp_attempts']);
    unset($_SESSION['otp_user_id']);
    unset($_SESSION['otp_username']);
    unset($_SESSION['otp_remember']);

    header("Location: login.php?error=otp_expired");
    exit;
}


/*
|--------------------------------------------------------------------------
| INITIALIZE OTP ATTEMPTS
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
     * Check expiration again when the form is submitted.
     */

    if (
        !isset($_SESSION['otp_expires']) ||
        !is_numeric($_SESSION['otp_expires']) ||
        time() >= (int) $_SESSION['otp_expires']
    ) {

        unset($_SESSION['otp_hash']);
        unset($_SESSION['otp_expires']);
        unset($_SESSION['otp_attempts']);
        unset($_SESSION['otp_user_id']);
        unset($_SESSION['otp_username']);
        unset($_SESSION['otp_remember']);

        header("Location: login.php?error=otp_expired");
        exit;
    }


    $otp = trim($_POST['otp'] ?? '');


    /*
     * ======================================================
     * BASIC OTP VALIDATION
     * ======================================================
     */

    if (
        $otp === '' ||
        !preg_match('/^\d{6}$/', $otp)
    ) {
        header("Location: verify_otp.php?error=invalid");
        exit;
    }


    /*
     * ======================================================
     * CHECK MAXIMUM ATTEMPTS
     * ======================================================
     */

    if ($_SESSION['otp_attempts'] >= 5) {

        /*
         * Too many attempts.
         */

        unset($_SESSION['otp_hash']);
        unset($_SESSION['otp_expires']);
        unset($_SESSION['otp_attempts']);
        unset($_SESSION['otp_user_id']);
        unset($_SESSION['otp_username']);
        unset($_SESSION['otp_remember']);

        header("Location: login.php?error=otp_attempts");
        exit;
    }


    /*
     * ======================================================
     * VERIFY OTP
     * ======================================================
     */

    if (
        isset($_SESSION['otp_hash']) &&
        password_verify(
            $otp,
            $_SESSION['otp_hash']
        )
    ) {

        /*
         * ==================================================
         * OTP CORRECT
         * ==================================================
         *
         * Now we can finally log the user in.
         */


        /*
         * ==================================================
         * SAVE REQUIRED INFORMATION BEFORE
         * CLEARING OTP SESSION
         * ==================================================
         */

        $userId = (int) $_SESSION['otp_user_id'];

        $username = $_SESSION['otp_username'];

        $remember = !empty($_SESSION['otp_remember']);


        /*
         * ==================================================
         * REGENERATE SESSION ID
         * ==================================================
         *
         * Helps prevent session fixation.
         */

        session_regenerate_id(true);


        /*
         * ==================================================
         * SET AUTHENTICATED SESSION
         * ==================================================
         */

        $_SESSION['logged_in'] = true;

        $_SESSION['username'] = $username;

        $_SESSION['user_id'] = $userId;


        /*
         * ==================================================
         * CLEAR OTP DATA
         * ==================================================
         *
         * The OTP must never be reusable.
         */

        unset($_SESSION['otp_hash']);
        unset($_SESSION['otp_expires']);
        unset($_SESSION['otp_attempts']);
        unset($_SESSION['otp_user_id']);
        unset($_SESSION['otp_username']);
        unset($_SESSION['otp_remember']);


        /*
         * ==================================================
         * REMEMBER ME
         * ==================================================
         *
         * The cookie is created ONLY after successful
         * password AND OTP verification.
         */

        if ($remember) {

            setcookie(
                "remember_user",
                $username,
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
             * Remove any old Remember Me cookie.
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
         * ==================================================
         * LOGIN COMPLETE
         * ==================================================
         */

        header("Location: index.php");
        exit;
    }


    /*
     * ======================================================
     * OTP INCORRECT
     * ======================================================
     */

    $_SESSION['otp_attempts'] =
        ($_SESSION['otp_attempts'] ?? 0) + 1;


    /*
     * ======================================================
     * CHECK IF THIS ATTEMPT REACHED THE MAXIMUM
     * ======================================================
     */

    if ($_SESSION['otp_attempts'] >= 5) {

        unset($_SESSION['otp_hash']);
        unset($_SESSION['otp_expires']);
        unset($_SESSION['otp_attempts']);
        unset($_SESSION['otp_user_id']);
        unset($_SESSION['otp_username']);
        unset($_SESSION['otp_remember']);

        header("Location: login.php?error=otp_attempts");
        exit;
    }


    /*
     * ======================================================
     * REDIRECT BACK WITH AN ERROR
     * ======================================================
     */

    header("Location: verify_otp.php?error=invalid");
    exit;
}


/*
|--------------------------------------------------------------------------
| DISPLAY EMAIL
|--------------------------------------------------------------------------
*/

$email = $_SESSION['otp_username'];


/*
|--------------------------------------------------------------------------
| CALCULATE REMAINING TIME
|--------------------------------------------------------------------------
*/

$remainingSeconds = max(
    0,
    (int) $_SESSION['otp_expires'] - time()
);

$remainingMinutes = floor(
    $remainingSeconds / 60
);

$remainingSecondsOnly = $remainingSeconds % 60;

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

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <!-- Google Font -->

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
           CONTAINER
        ========================================= */

        .verify-container {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 25px;
        }


        /* =========================================
           CARD
        ========================================= */

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


        /* =========================================
           ICON
        ========================================= */

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


        /* =========================================
           LABEL
        ========================================= */

        .authorized {

            margin-bottom: 8px;

            color: #1764ae;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 1.4px;

            text-transform: uppercase;
        }


        /* =========================================
           TITLE
        ========================================= */

        .verify-title {

            margin: 0;

            color: #10233d;

            font-size: 29px;

            font-weight: 800;

            letter-spacing: -0.8px;
        }


        /* =========================================
           DESCRIPTION
        ========================================= */

        .verify-subtitle {

            margin:
                10px
                0
                25px;

            color: #7d8b9b;

            font-size: 12px;

            line-height: 1.7;
        }


        /* =========================================
           EMAIL
        ========================================= */

        .email-display {

            display: inline-block;

            margin-bottom: 25px;

            padding:
                8px
                13px;

            border-radius: 8px;

            background: #f3f7fb;

            color: #1764ae;

            font-size: 12px;

            font-weight: 600;

            word-break: break-word;
        }


        /* =========================================
           OTP INPUT
        ========================================= */

        .otp-input {

            width: 100%;

            height: 58px;

            border:
                1px solid #d6dee7;

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


        /* =========================================
           VERIFY BUTTON
        ========================================= */

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

            transform:
                translateY(-1px);
        }


        /* =========================================
           ERROR
        ========================================= */

        .error-message {

            margin-top: 17px;

            padding:
                10px
                12px;

            border-radius: 8px;

            background: #fff1f1;

            border:
                1px solid #ffd6d6;

            color: #d63939;

            font-size: 11px;

            text-align: center;
        }


        /* =========================================
           TIMER
        ========================================= */

        .otp-timer {

            margin-top: 17px;

            color: #929daa;

            font-size: 10px;
        }


        .otp-timer strong {

            color: #1764ae;

            font-weight: 700;
        }


        /* =========================================
           SECURITY MESSAGE
        ========================================= */

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


        /* =========================================
           FOOTER
        ========================================= */

        .verify-footer {

            margin-top: 25px;

            padding-top: 18px;

            border-top:
                1px solid #edf0f3;

            color: #8d99a6;

            font-size: 8px;

            line-height: 1.6;
        }


        /* =========================================
           MOBILE
        ========================================= */

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


        <!-- ICON -->

        <div class="verify-icon">

            <i class="bi bi-envelope-check-fill"></i>

        </div>


        <!-- LABEL -->

        <div class="authorized">

            Two-Step Verification

        </div>


        <!-- TITLE -->

        <h1 class="verify-title">

            Check your email

        </h1>


        <!-- DESCRIPTION -->

        <p class="verify-subtitle">

            We sent a 6-digit verification code
            to the email address associated with
            your account.

        </p>


        <!-- EMAIL -->

        <div class="email-display">

            <i class="bi bi-envelope"></i>

            <?php echo htmlspecialchars(
                $email,
                ENT_QUOTES,
                'UTF-8'
            ); ?>

        </div>


        <!-- OTP FORM -->

        <form
            action="verify_otp.php"
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


        <!-- ERROR -->

        <?php if (isset($_GET['error'])): ?>

            <div class="error-message">

                <?php

                if ($_GET['error'] === 'invalid') {

                    echo '
                        <i class="bi bi-exclamation-circle"></i>
                        Incorrect verification code.
                        Please try again.
                    ';

                } else {

                    echo '
                        <i class="bi bi-exclamation-circle"></i>
                        Unable to verify the code.
                        Please try again.
                    ';
                }

                ?>

            </div>

        <?php endif; ?>


        <!-- TIMER -->

        <div class="otp-timer">

            Code expires in

            <strong id="timer">

                <?php

                printf(
                    "%02d:%02d",
                    $remainingMinutes,
                    $remainingSecondsOnly
                );

                ?>

            </strong>

        </div>


        <!-- SECURITY -->

        <div class="security-message">

            <i class="bi bi-shield-lock-fill"></i>

            Never share your verification code with anyone.

        </div>


        <!-- FOOTER -->

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

/*
|--------------------------------------------------------------------------
| OTP TIMER
|--------------------------------------------------------------------------
*/

let remainingSeconds =
    <?php echo (int) $remainingSeconds; ?>;


const timer =
    document.getElementById("timer");


function updateTimer() {

    if (remainingSeconds <= 0) {

        timer.textContent = "00:00";

        /*
         * Disable the OTP form once the timer expires.
         */

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
        Math.floor(
            remainingSeconds / 60
        );


    const seconds =
        remainingSeconds % 60;


    timer.textContent =
        String(minutes).padStart(2, "0")
        + ":"
        +
        String(seconds).padStart(2, "0");


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
```