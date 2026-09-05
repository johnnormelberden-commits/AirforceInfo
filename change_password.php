<?php

session_start();


/*
 * ==========================================================
 * CHECK LOGIN
 * ==========================================================
 */

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {

    header("Location: login.php");
    exit;

}


/*
 * ==========================================================
 * POSTGRESQL DATABASE CONNECTION
 * ==========================================================
 */

try {

    $host =
        getenv('DB_HOST');

    $port =
        getenv('DB_PORT') ?: '5432';

    $database =
        getenv('DB_NAME');

    $dbUsername =
        getenv('DB_USER');

    $dbPassword =
        getenv('DB_PASSWORD');


    if (
        !$host ||
        !$database ||
        !$dbUsername ||
        !$dbPassword
    ) {

        throw new Exception(
            "Database configuration is missing."
        );

    }


    $conn = new PDO(
        "pgsql:host={$host};port={$port};dbname={$database}",
        $dbUsername,
        $dbPassword
    );


    $conn->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );


} catch (Exception $e) {

    /*
     * Do not expose database details.
     */

    die(
        "Unable to connect to the database."
    );

}


/*
 * ==========================================================
 * GET CURRENT USER
 * ==========================================================
 */

$username =
    $_SESSION['username'] ?? '';


if (empty($username)) {

    session_destroy();

    header("Location: login.php");

    exit;

}


/*
 * ==========================================================
 * FORM VARIABLES
 * ==========================================================
 */

$currentPassword = "";

$newPassword = "";

$confirmPassword = "";

$errorMessage = "";

$successMessage = "";


/*
 * ==========================================================
 * HANDLE FORM SUBMISSION
 * ==========================================================
 */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $currentPassword =
        $_POST['current_password'] ?? '';

    $newPassword =
        $_POST['new_password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';


    /*
     * ======================================================
     * VALIDATION
     * ======================================================
     */

    if (
        empty($currentPassword) ||
        empty($newPassword) ||
        empty($confirmPassword)
    ) {

        $errorMessage =
            "All fields are required.";

    }

    elseif (
        $newPassword !== $confirmPassword
    ) {

        $errorMessage =
            "New password and confirmation do not match.";

    }

    elseif (
        strlen($newPassword) < 6
    ) {

        $errorMessage =
            "New password must be at least 6 characters.";

    }

    elseif (
        $currentPassword === $newPassword
    ) {

        $errorMessage =
            "New password must be different from your current password.";

    }

    else {

        try {

            /*
             * ==================================================
             * GET CURRENT PASSWORD
             * ==================================================
             */

            $stmt = $conn->prepare(
                "
                SELECT password
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
             * ==================================================
             * CHECK USER
             * ==================================================
             */

            if (!$user) {

                $errorMessage =
                    "User not found.";

            }

            else {

                /*
                 * ==================================================
                 * VERIFY CURRENT PASSWORD
                 * ==================================================
                 */

                if (
                    !password_verify(
                        $currentPassword,
                        $user['password']
                    )
                ) {

                    $errorMessage =
                        "Current password is incorrect.";

                }

                else {

                    /*
                     * ==================================================
                     * HASH NEW PASSWORD
                     * ==================================================
                     */

                    $hashedPassword =
                        password_hash(
                            $newPassword,
                            PASSWORD_DEFAULT
                        );


                    /*
                     * ==================================================
                     * UPDATE PASSWORD
                     * ==================================================
                     */

                    $update =
                        $conn->prepare(
                            "
                            UPDATE users
                            SET password = :password
                            WHERE username = :username
                            "
                        );


                    $update->execute([
                        ':password' => $hashedPassword,
                        ':username' => $username
                    ]);


                    /*
                     * ==================================================
                     * SUCCESS
                     * ==================================================
                     */

                    $successMessage =
                        "Password updated successfully.";


                    /*
                     * Clear password fields.
                     */

                    $currentPassword = "";

                    $newPassword = "";

                    $confirmPassword = "";

                }

            }


        } catch (PDOException $e) {

            /*
             * Do not expose database error details.
             */

            $errorMessage =
                "Unable to update the password.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Change Password | CMO Training Squadron
    </title>


    <!-- =====================================================
         SHARED DASHBOARD CSS
         ===================================================== -->

    <link
        rel="stylesheet"
        href="css/dashboard.css"
    >


    <!-- =====================================================
         CHANGE PASSWORD CSS
         ===================================================== -->

    <link
        rel="stylesheet"
        href="css/change-password.css"
    >


    <!-- =====================================================
         FONT AWESOME
         ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


    <!-- =====================================================
         SIDEBAR
         ===================================================== -->

    <?php include 'sidebar.php'; ?>


    <!-- =====================================================
         SIDEBAR OVERLAY
         ===================================================== -->

    <div
        id="sidebarOverlay"
        class="sidebar-overlay"
    ></div>


    <!-- =====================================================
         MAIN WRAPPER
         ===================================================== -->

    <div class="main-wrapper">


        <!-- =================================================
             TOPBAR
             ================================================= -->

        <?php include 'topbar.php'; ?>


        <!-- =================================================
             MAIN CONTENT
             ================================================= -->

        <main class="page-content">


            <div class="change-password-content">


                <!-- =========================================
                     PAGE HEADING
                     ========================================= -->

                <div class="change-password-heading">

                    <div>

                        <h1>
                            Change Password
                        </h1>

                        <p>
                            Update your account password
                            securely.
                        </p>

                    </div>

                </div>


                <!-- =========================================
                     PASSWORD PANEL
                     ========================================= -->

                <div class="change-password-panel">


                    <!-- =====================================
                         PANEL HEADER
                         ===================================== -->

                    <div class="change-password-panel-header">


                        <div class="change-password-icon">

                            <i
                                class="fa-solid fa-lock"
                            ></i>

                        </div>


                        <div>

                            <h2>
                                Account Security
                            </h2>

                            <p>
                                Change the password
                                associated with your account.
                            </p>

                        </div>


                    </div>


                    <!-- =====================================
                         CURRENT USER
                         ===================================== -->

                    <div class="change-password-user">

                        <span>
                            Logged in as:
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $username,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </strong>

                    </div>


                    <!-- =====================================
                         ERROR MESSAGE
                         ===================================== -->

                    <?php if (
                        !empty($errorMessage)
                    ): ?>

                        <div
                            class="
                                change-password-alert
                                change-password-alert-error
                            "
                            role="alert"
                        >

                            <i
                                class="fa-solid fa-circle-exclamation"
                            ></i>

                            <span>

                                <?= htmlspecialchars(
                                    $errorMessage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </span>

                        </div>

                    <?php endif; ?>


                    <!-- =====================================
                         SUCCESS MESSAGE
                         ===================================== -->

                    <?php if (
                        !empty($successMessage)
                    ): ?>

                        <div
                            class="
                                change-password-alert
                                change-password-alert-success
                            "
                            role="alert"
                        >

                            <i
                                class="fa-solid fa-circle-check"
                            ></i>

                            <span>

                                <?= htmlspecialchars(
                                    $successMessage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </span>

                        </div>

                    <?php endif; ?>


                    <!-- =====================================
                         FORM
                         ===================================== -->

                    <form
                        method="post"
                        action=""
                        autocomplete="off"
                    >


                        <!-- =================================
                             CURRENT PASSWORD
                             ================================= -->

                        <div
                            class="
                                change-password-form-group
                            "
                        >

                            <label
                                for="current_password"
                            >
                                Current Password
                            </label>


                            <div
                                class="password-input-wrapper"
                            >

                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="password-input"
                                    autocomplete="current-password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="current_password"
                                    aria-label="Show current password"
                                >

                                    <i
                                        class="fa-solid fa-eye"
                                    ></i>

                                </button>

                            </div>

                        </div>


                        <!-- =================================
                             NEW PASSWORD
                             ================================= -->

                        <div
                            class="
                                change-password-form-group
                            "
                        >

                            <label
                                for="new_password"
                            >
                                New Password
                            </label>


                            <div
                                class="password-input-wrapper"
                            >

                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    class="password-input"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="new_password"
                                    aria-label="Show new password"
                                >

                                    <i
                                        class="fa-solid fa-eye"
                                    ></i>

                                </button>

                            </div>


                            <span
                                class="change-password-help"
                            >
                                Minimum 6 characters.
                            </span>

                        </div>


                        <!-- =================================
                             CONFIRM PASSWORD
                             ================================= -->

                        <div
                            class="
                                change-password-form-group
                            "
                        >

                            <label
                                for="confirm_password"
                            >
                                Confirm New Password
                            </label>


                            <div
                                class="password-input-wrapper"
                            >

                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="password-input"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="confirm_password"
                                    aria-label="Show password confirmation"
                                >

                                    <i
                                        class="fa-solid fa-eye"
                                    ></i>

                                </button>

                            </div>

                        </div>


                        <!-- =================================
                             PASSWORD REQUIREMENTS
                             ================================= -->

                        <div
                            class="password-requirements"
                        >

                            <span
                                class="password-requirements-title"
                            >
                                Password Requirements
                            </span>


                            <ul>

                                <li>
                                    At least 6 characters
                                </li>

                                <li>
                                    Must be different from
                                    your current password
                                </li>

                                <li>
                                    New password must match
                                    the confirmation
                                </li>

                            </ul>

                        </div>


                        <!-- =================================
                             FORM ACTIONS
                             ================================= -->

                        <div
                            class="change-password-actions"
                        >


                            <a
                                href="index.php"
                                class="
                                    change-password-btn
                                    change-password-btn-secondary
                                "
                            >

                                <i
                                    class="fa-solid fa-arrow-left"
                                ></i>

                                Back

                            </a>


                            <button
                                type="submit"
                                class="
                                    change-password-btn
                                    change-password-btn-primary
                                "
                            >

                                <i
                                    class="fa-solid fa-key"
                                ></i>

                                Update Password

                            </button>


                        </div>


                    </form>


                </div>


            </div>


        </main>


    </div>


    <!-- =====================================================
         DASHBOARD JAVASCRIPT
         ===================================================== -->

    <script
        src="js/dashboard.js"
    ></script>


    <!-- =====================================================
         PASSWORD VISIBILITY JAVASCRIPT
         ===================================================== -->

    <script>

        document.addEventListener(
            "DOMContentLoaded",
            function () {

                const toggleButtons =
                    document.querySelectorAll(
                        ".password-toggle"
                    );


                toggleButtons.forEach(
                    function (button) {

                        button.addEventListener(
                            "click",
                            function () {

                                const targetId =
                                    button.getAttribute(
                                        "data-target"
                                    );


                                const input =
                                    document.getElementById(
                                        targetId
                                    );


                                const icon =
                                    button.querySelector(
                                        "i"
                                    );


                                if (!input) {
                                    return;
                                }


                                if (
                                    input.type ===
                                    "password"
                                ) {

                                    input.type =
                                        "text";


                                    if (icon) {

                                        icon.classList.remove(
                                            "fa-eye"
                                        );

                                        icon.classList.add(
                                            "fa-eye-slash"
                                        );

                                    }


                                    button.setAttribute(
                                        "aria-label",
                                        "Hide password"
                                    );

                                }

                                else {

                                    input.type =
                                        "password";


                                    if (icon) {

                                        icon.classList.remove(
                                            "fa-eye-slash"
                                        );

                                        icon.classList.add(
                                            "fa-eye"
                                        );

                                    }


                                    button.setAttribute(
                                        "aria-label",
                                        "Show password"
                                    );

                                }

                            }
                        );

                    }
                );

            }
        );

    </script>


</body>

</html>
