<?php
session_start();

if (isset($_COOKIE['remember_user'])) {
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $_COOKIE['remember_user'];

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<title>CMO Information System - Login</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

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

    border: 1px solid rgba(15, 62, 105, 0.08);
}


/* =========================================
   LEFT BRANDING PANEL
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


/* =========================================
   DECORATIVE DIAGONAL LINES
========================================= */

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


/* =========================================
   DECORATIVE AIR FORCE SYMBOL
========================================= */

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


/* =========================================
   BRAND CONTENT
========================================= */

.brand-content {

    position: relative;

    z-index: 2;
}


/* =========================================
   BRAND HEADER
========================================= */

.brand-header {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 90px;
}


/* =========================================
   LOGO
========================================= */

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


/* =========================================
   BRAND NAME
========================================= */

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


/* =========================================
   PORTAL LABEL
========================================= */

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


/* =========================================
   BRAND TITLE
========================================= */

.brand-title {

    max-width: 350px;

    margin: 0 0 16px;

    font-size: 35px;

    line-height: 1.12;

    font-weight: 800;

    letter-spacing: -1px;
}


/* =========================================
   BRAND DESCRIPTION
========================================= */

.brand-description {

    max-width: 370px;

    margin: 0;

    color: rgba(255,255,255,0.68);

    font-size: 12px;

    line-height: 1.7;
}


/* =========================================
   SECURITY BADGE
========================================= */

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
   RIGHT LOGIN PANEL
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


/* =========================================
   AUTHORIZED LABEL
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
   LOGIN TITLE
========================================= */

.login-title {

    margin: 0;

    color: #10233d;

    font-size: 31px;

    font-weight: 800;

    letter-spacing: -1px;
}


/* =========================================
   LOGIN SUBTITLE
========================================= */

.login-subtitle {

    margin: 8px 0 27px;

    color: #7d8b9b;

    font-size: 12px;

    line-height: 1.6;
}


/* =========================================
   FORM LABEL
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
   INPUT WRAPPER
========================================= */

.input-wrapper {

    position: relative;

    margin-bottom: 18px;
}


/* =========================================
   INPUT ICON
========================================= */

.input-icon {

    position: absolute;

    left: 14px;

    top: 50%;

    transform: translateY(-50%);

    color: #8c9aaa;

    font-size: 14px;

    z-index: 2;
}


/* =========================================
   FORM INPUT
========================================= */

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

    color: #26384c !important;
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


.password-toggle:hover {

    color: #1764ae;
}


/* =========================================
   LOGIN BUTTON
========================================= */

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

    box-shadow:
        0 9px 18px rgba(15, 91, 157, 0.25);
}


.btn-login i {

    margin-left: 7px;
}


/* =========================================
   SECURITY MESSAGE
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


.security-message i {

    color: #8896a5;
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
   ERROR MESSAGE
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


    .brand-description {

        font-size: 11px;
    }


    .security-badge {

        margin-top: 22px;
    }


    .login-panel {

        width: 100%;

        padding: 40px 30px;
    }

}


/* =========================================
   SMALL MOBILE
========================================= */

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

}

</style>

</head>


<body>


<!-- =========================================
     LOGIN CONTAINER
========================================= -->

<div class="login-container">


    <div class="login-card">


        <!-- =====================================
             LEFT CMO BRANDING
        ====================================== -->

        <div class="brand-panel">


            <div class="brand-content">


                <!-- BRAND HEADER -->

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


                <!-- PORTAL LABEL -->

                <div class="portal-label">

                    CMO Information Portal

                </div>


                <!-- MAIN MESSAGE -->

                <h1 class="brand-title">

                    One secure<br>
                    system for<br>
                    CMO information.

                </h1>


                <!-- DESCRIPTION -->

                <p class="brand-description">

                    Access authorized CMO information,
                    personnel records, activities,
                    assignments, and official resources
                    from one secure workspace.

                </p>


                <!-- SECURITY BADGE -->

                <div class="security-badge">

                    <span class="security-dot"></span>

                    Authorized personnel access

                </div>


            </div>

        </div>


        <!-- =====================================
             RIGHT LOGIN PANEL
        ====================================== -->

        <div class="login-panel">


            <div class="login-content">


                <!-- AUTHORIZED -->

                <div class="authorized">

                    Authorized Personnel

                </div>


                <!-- TITLE -->

                <h2 class="login-title">

                    Welcome back

                </h2>


                <!-- SUBTITLE -->

                <p class="login-subtitle">

                    Sign in with your authorized account
                    to access the CMO Information System.

                </p>


                <!-- =================================
                     LOGIN FORM
                ================================== -->

                <form
                    action="auth.php"
                    method="POST"
                >


                    <!-- USERNAME -->

                    <div>


                        <label
                            for="username"
                            class="form-label"
                        >

                            Username

                        </label>


                        <div class="input-wrapper">


                            <i
                                class="bi bi-person input-icon"
                            ></i>


                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                placeholder="Enter your username"
                                required
                                autocomplete="username"
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


                            <!-- SHOW / HIDE PASSWORD -->

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


                    </div>


                    <!-- LOGIN BUTTON -->

                    <button
                        type="submit"
                        class="btn-login"
                    >

                        Sign in securely

                        <i class="bi bi-arrow-right"></i>

                    </button>


                </form>


                <!-- =================================
                     LOGIN ERROR
                ================================== -->

                <?php if (isset($_GET['error'])): ?>

                    <div class="error-message">

                        <i class="bi bi-exclamation-circle"></i>

                        Invalid username or password.

                    </div>

                <?php endif; ?>


                <!-- =================================
                     SECURITY MESSAGE
                ================================== -->

                <div class="security-message">

                    <i class="bi bi-shield-lock-fill"></i>

                    Your session is protected and access
                    is restricted to authorized personnel.

                </div>


                <!-- =================================
                     FOOTER
                ================================== -->

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


<!-- =========================================
     PASSWORD TOGGLE SCRIPT
========================================= -->

<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const icon =
        document.getElementById("passwordIcon");


    if (password.type === "password") {

        password.type = "text";

        icon.classList.remove("bi-eye");

        icon.classList.add("bi-eye-slash");

    } else {

        password.type = "password";

        icon.classList.remove("bi-eye-slash");

        icon.classList.add("bi-eye");

    }

}

</script>


</body>
</html>