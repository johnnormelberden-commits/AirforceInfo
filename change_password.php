<?php

session_start();


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {

    header("Location: login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
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

    die(
        "Unable to connect to the database."
    );

}


/*
|--------------------------------------------------------------------------
| GET CURRENT USER
|--------------------------------------------------------------------------
*/

$username =
    $_SESSION['username'] ?? '';


if (empty($username)) {

    session_destroy();

    header("Location: login.php");

    exit;

}


/*
|--------------------------------------------------------------------------
| FORM VARIABLES
|--------------------------------------------------------------------------
*/

$currentPassword = "";
$newPassword = "";
$confirmPassword = "";

$errorMessage = "";
$successMessage = "";


/*
|--------------------------------------------------------------------------
| HANDLE PASSWORD CHANGE
|--------------------------------------------------------------------------
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


            if (!$user) {

                $errorMessage =
                    "User not found.";

            }

            elseif (
                !password_verify(
                    $currentPassword,
                    $user['password']
                )
            ) {

                $errorMessage =
                    "Current password is incorrect.";

            }

            else {

                $hashedPassword =
                    password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );


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


                $successMessage =
                    "Password updated successfully.";


                $currentPassword = "";
                $newPassword = "";
                $confirmPassword = "";

            }


        } catch (PDOException $e) {

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


    <!-- SAME SHARED DASHBOARD CSS -->

    <link
        rel="stylesheet"
        href="css/dashboard.css"
    >


    <!-- CHANGE PASSWORD CSS -->

    <link
        rel="stylesheet"
        href="css/change_password.css"
    >


    <!-- BOOTSTRAP ICONS -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

</head>


<body>


    <!-- =====================================================
         SAME SIDEBAR
    ====================================================== -->

    <?php include 'sidebar.php'; ?>


    <!-- =====================================================
         SAME MAIN WRAPPER
    ====================================================== -->

    <div class="main-wrapper">


        <!-- =================================================
             SAME TOPBAR
        ================================================== -->

        <?php include 'topbar.php'; ?>


        <!-- =================================================
             CHANGE PASSWORD CONTENT
        ================================================== -->

        <main class="page-content">

            <div class="change-password-content">

                <div class="change-password-heading">

                    <div>

                        <h1>
                            Change Password
                        </h1>

                        <p>
                            Update your account password securely.
                        </p>

                    </div>

                </div>


                <div class="change-password-panel">


                    <div class="change-password-panel-header">

                        <div class="change-password-icon">

                            <i class="bi bi-lock-fill"></i>

                        </div>


                        <div>

                            <h2>
                                Account Security
                            </h2>

                            <p>
                                Change the password associated
                                with your account.
                            </p>

                        </div>

                    </div>


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


                    <?php if (!empty($errorMessage)): ?>

                        <div
                            class="change-password-alert change-password-alert-error"
                            role="alert"
                        >

                            <i class="bi bi-exclamation-circle-fill"></i>

                            <span>

                                <?= htmlspecialchars(
                                    $errorMessage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </span>

                        </div>

                    <?php endif; ?>


                    <?php if (!empty($successMessage)): ?>

                        <div
                            class="change-password-alert change-password-alert-success"
                            role="alert"
                        >

                            <i class="bi bi-check-circle-fill"></i>

                            <span>

                                <?= htmlspecialchars(
                                    $successMessage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </span>

                        </div>

                    <?php endif; ?>


                    <form
                        method="post"
                        action=""
                        autocomplete="off"
                    >


                        <!-- CURRENT PASSWORD -->

                        <div class="change-password-form-group">

                            <label for="current_password">
                                Current Password
                            </label>


                            <div class="password-input-wrapper">

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

                                    <i class="bi bi-eye-fill"></i>

                                </button>

                            </div>

                        </div>


                        <!-- NEW PASSWORD -->

                        <div class="change-password-form-group">

                            <label for="new_password">
                                New Password
                            </label>


                            <div class="password-input-wrapper">

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

                                    <i class="bi bi-eye-fill"></i>

                                </button>

                            </div>


                            <span class="change-password-help">
                                Minimum 6 characters.
                            </span>

                        </div>


                        <!-- CONFIRM PASSWORD -->

                        <div class="change-password-form-group">

                            <label for="confirm_password">
                                Confirm New Password
                            </label>


                            <div class="password-input-wrapper">

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

                                    <i class="bi bi-eye-fill"></i>

                                </button>

                            </div>

                        </div>


                        <!-- PASSWORD REQUIREMENTS -->

                        <div class="password-requirements">

                            <span class="password-requirements-title">
                                Password Requirements
                            </span>


                            <ul>

                                <li>
                                    At least 6 characters
                                </li>

                                <li>
                                    Must be different from your current password
                                </li>

                                <li>
                                    New password must match the confirmation
                                </li>

                            </ul>

                        </div>


                        <!-- ACTIONS -->

                        <div class="change-password-actions">


                            <a
                                href="index.php"
                                class="change-password-btn change-password-btn-secondary"
                            >

                                <i class="bi bi-arrow-left"></i>

                                Back

                            </a>


                            <button
                                type="submit"
                                class="change-password-btn change-password-btn-primary"
                            >

                                <i class="bi bi-key-fill"></i>

                                Update Password

                            </button>


                        </div>


                    </form>


                </div>

            </div>

        </main>


    </div>


    <!-- =====================================================
         SAME DASHBOARD JAVASCRIPT
    ====================================================== -->

    <script src="js/dashboard.js"></script>


    <!-- =====================================================
         PASSWORD VISIBILITY
    ====================================================== -->

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
                                    input.type === "password"
                                ) {

                                    input.type = "text";


                                    if (icon) {

                                        icon.classList.remove(
                                            "bi-eye-fill"
                                        );

                                        icon.classList.add(
                                            "bi-eye-slash-fill"
                                        );

                                    }


                                    button.setAttribute(
                                        "aria-label",
                                        "Hide password"
                                    );

                                }

                                else {

                                    input.type = "password";


                                    if (icon) {

                                        icon.classList.remove(
                                            "bi-eye-slash-fill"
                                        );

                                        icon.classList.add(
                                            "bi-eye-fill"
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
