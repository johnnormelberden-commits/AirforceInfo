<?php
/*
|--------------------------------------------------------------------------
| CMO TRAINING SQUADRON
| CHANGE PASSWORD
|--------------------------------------------------------------------------
| File:
| change_password.php
|
| CSS:
| css/change-password.css
|
| Shared files:
| includes/sidebar.php
| includes/topbar.php
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
|
| Change this session check if your login system uses a different
| session variable.
|
*/

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
|
| Expected:
| config/database.php
|
| The database file should create a PDO connection named $pdo.
|
*/

require_once __DIR__ . '/config/database.php';


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$errorMessage = '';
$successMessage = '';

$username = $_SESSION['username'];


/*
|--------------------------------------------------------------------------
| GET CURRENT USER
|--------------------------------------------------------------------------
*/

try {

    $userQuery = $pdo->prepare("
        SELECT id, username, password
        FROM users
        WHERE username = :username
        LIMIT 1
    ");

    $userQuery->execute([
        ':username' => $username
    ]);

    $user = $userQuery->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        $errorMessage = 'Unable to locate your user account.';

    }

} catch (PDOException $e) {

    $errorMessage = 'A database error occurred. Please try again later.';

}


/*
|--------------------------------------------------------------------------
| HANDLE CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {

    /*
    |--------------------------------------------------------------------------
    | GET FORM VALUES
    |--------------------------------------------------------------------------
    */

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $currentPassword === '' ||
        $newPassword === '' ||
        $confirmPassword === ''
    ) {

        $errorMessage = 'Please complete all password fields.';

    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY CURRENT PASSWORD
    |--------------------------------------------------------------------------
    */

    elseif (!password_verify($currentPassword, $user['password'])) {

        $errorMessage = 'The current password is incorrect.';

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK NEW PASSWORD
    |--------------------------------------------------------------------------
    */

    elseif (strlen($newPassword) < 8) {

        $errorMessage = 'The new password must contain at least 8 characters.';

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK PASSWORD CONFIRMATION
    |--------------------------------------------------------------------------
    */

    elseif ($newPassword !== $confirmPassword) {

        $errorMessage = 'The new passwords do not match.';

    }


    /*
    |--------------------------------------------------------------------------
    | PREVENT SAME PASSWORD
    |--------------------------------------------------------------------------
    */

    elseif (password_verify($newPassword, $user['password'])) {

        $errorMessage = 'The new password must be different from your current password.';

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */

    else {

        try {

            /*
            |--------------------------------------------------------------------------
            | HASH NEW PASSWORD
            |--------------------------------------------------------------------------
            */

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            $updateQuery = $pdo->prepare("
                UPDATE users
                SET password = :password
                WHERE id = :id
            ");

            $updateQuery->execute([
                ':password' => $hashedPassword,
                ':id' => $user['id']
            ]);


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            $successMessage = 'Your password has been changed successfully.';


            /*
            |--------------------------------------------------------------------------
            | CLEAR FORM
            |--------------------------------------------------------------------------
            */

            $currentPassword = '';
            $newPassword = '';
            $confirmPassword = '';


        } catch (PDOException $e) {

            $errorMessage = 'Unable to change your password. Please try again later.';

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
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- =====================================================
         GLOBAL CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- =====================================================
         CHANGE PASSWORD CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/change-password.css"
    >

</head>


<body>


<!-- =========================================================
     DASHBOARD LAYOUT
========================================================= -->

<div class="dashboard-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <?php

    require_once __DIR__ . '/includes/sidebar.php';

    ?>


    <!-- =====================================================
         MAIN AREA
    ====================================================== -->

    <div class="main-area">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <?php

        require_once __DIR__ . '/includes/topbar.php';

        ?>


        <!-- =================================================
             PAGE CONTENT
        ================================================== -->

        <main class="main-content">


            <section class="change-password-content">


                <!-- =============================================
                     PAGE HEADING
                ============================================== -->

                <div class="change-password-heading">

                    <div>

                        <h1>
                            Change Password
                        </h1>

                        <p>
                            Update your account password to keep your
                            Student Database Information System secure.
                        </p>

                    </div>

                </div>


                <!-- =============================================
                     PASSWORD PANEL
                ============================================== -->

                <div class="change-password-panel">


                    <!-- =========================================
                         PANEL HEADER
                    ========================================== -->

                    <div class="change-password-panel-header">


                        <div class="change-password-icon">

                            <i class="bi bi-shield-lock-fill"></i>

                        </div>


                        <div>

                            <h2>
                                Update Your Password
                            </h2>

                            <p>
                                Enter your current password and choose
                                a new secure password.
                            </p>

                        </div>


                    </div>


                    <!-- =========================================
                         CURRENT USER
                    ========================================== -->

                    <div class="change-password-user">

                        <span>
                            <i class="bi bi-person-circle"></i>
                        </span>

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


                    <!-- =========================================
                         ERROR ALERT
                    ========================================== -->

                    <?php if ($errorMessage !== ''): ?>

                        <div
                            class="change-password-alert change-password-alert-error"
                            role="alert"
                        >

                            <i class="bi bi-exclamation-triangle-fill"></i>

                            <span>
                                <?= htmlspecialchars(
                                    $errorMessage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </span>

                        </div>

                    <?php endif; ?>


                    <!-- =========================================
                         SUCCESS ALERT
                    ========================================== -->

                    <?php if ($successMessage !== ''): ?>

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


                    <!-- =========================================
                         PASSWORD FORM
                    ========================================== -->

                    <form
                        method="POST"
                        action=""
                        id="changePasswordForm"
                        autocomplete="off"
                    >


                        <!-- =====================================
                             CURRENT PASSWORD
                        ====================================== -->

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
                                    placeholder="Enter your current password"
                                    autocomplete="current-password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="current_password"
                                    aria-label="Show current password"
                                    title="Show password"
                                >

                                    <i class="bi bi-eye-fill"></i>

                                </button>

                            </div>

                        </div>


                        <!-- =====================================
                             NEW PASSWORD
                        ====================================== -->

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
                                    placeholder="Enter your new password"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="new_password"
                                    aria-label="Show new password"
                                    title="Show password"
                                >

                                    <i class="bi bi-eye-fill"></i>

                                </button>

                            </div>


                            <small class="change-password-help">

                                Use at least 8 characters for your new password.

                            </small>

                        </div>


                        <!-- =====================================
                             CONFIRM PASSWORD
                        ====================================== -->

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
                                    placeholder="Re-enter your new password"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="confirm_password"
                                    aria-label="Show confirm password"
                                    title="Show password"
                                >

                                    <i class="bi bi-eye-fill"></i>

                                </button>

                            </div>

                        </div>


                        <!-- =====================================
                             PASSWORD REQUIREMENTS
                        ====================================== -->

                        <div class="password-requirements">

                            <span class="password-requirements-title">

                                Password Requirements

                            </span>


                            <ul>

                                <li>
                                    At least 8 characters
                                </li>

                                <li>
                                    Must be different from your current password
                                </li>

                                <li>
                                    New password and confirmation must match
                                </li>

                            </ul>

                        </div>


                        <!-- =====================================
                             FORM ACTIONS
                        ====================================== -->

                        <div class="change-password-actions">


                            <!-- BACK -->

                            <a
                                href="index.php"
                                class="change-password-btn change-password-btn-secondary"
                            >

                                <i class="bi bi-arrow-left"></i>

                                <span>
                                    Back to Dashboard
                                </span>

                            </a>


                            <!-- UPDATE -->

                            <button
                                type="submit"
                                class="change-password-btn change-password-btn-primary"
                                id="updatePasswordBtn"
                            >

                                <i class="bi bi-key-fill"></i>

                                <span>
                                    Update Password
                                </span>

                            </button>


                        </div>


                    </form>


                </div>


            </section>


        </main>


    </div>


</div>


<!-- =========================================================
     PASSWORD VISIBILITY JAVASCRIPT
========================================================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | PASSWORD TOGGLE
    |--------------------------------------------------------------------------
    */

    const toggleButtons =
        document.querySelectorAll('.password-toggle');


    toggleButtons.forEach(function (button) {


        button.addEventListener('click', function () {


            const targetId =
                button.getAttribute('data-target');


            const input =
                document.getElementById(targetId);


            if (!input) {
                return;
            }


            const icon =
                button.querySelector('i');


            if (input.type === 'password') {

                input.type = 'text';


                if (icon) {

                    icon.classList.remove(
                        'bi-eye-fill'
                    );

                    icon.classList.add(
                        'bi-eye-slash-fill'
                    );

                }


                button.setAttribute(
                    'aria-label',
                    'Hide password'
                );

                button.setAttribute(
                    'title',
                    'Hide password'
                );


            } else {

                input.type = 'password';


                if (icon) {

                    icon.classList.remove(
                        'bi-eye-slash-fill'
                    );

                    icon.classList.add(
                        'bi-eye-fill'
                    );

                }


                button.setAttribute(
                    'aria-label',
                    'Show password'
                );

                button.setAttribute(
                    'title',
                    'Show password'
                );

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | PASSWORD MATCH VALIDATION
    |--------------------------------------------------------------------------
    */

    const newPassword =
        document.getElementById('new_password');

    const confirmPassword =
        document.getElementById('confirm_password');


    function validatePasswordMatch() {


        if (
            !newPassword ||
            !confirmPassword
        ) {

            return;

        }


        if (confirmPassword.value === '') {

            confirmPassword.classList.remove(
                'is-valid',
                'is-invalid'
            );

            return;

        }


        if (
            newPassword.value !== '' &&
            newPassword.value === confirmPassword.value
        ) {

            confirmPassword.classList.remove(
                'is-invalid'
            );

            confirmPassword.classList.add(
                'is-valid'
            );

        } else {

            confirmPassword.classList.remove(
                'is-valid'
            );

            confirmPassword.classList.add(
                'is-invalid'
            );

        }

    }


    if (newPassword) {

        newPassword.addEventListener(
            'input',
            validatePasswordMatch
        );

    }


    if (confirmPassword) {

        confirmPassword.addEventListener(
            'input',
            validatePasswordMatch
        );

    }


    /*
    |--------------------------------------------------------------------------
    | NEW PASSWORD LENGTH VALIDATION
    |--------------------------------------------------------------------------
    */

    if (newPassword) {

        newPassword.addEventListener(
            'input',
            function () {


                if (newPassword.value === '') {

                    newPassword.classList.remove(
                        'is-valid',
                        'is-invalid'
                    );

                    return;

                }


                if (newPassword.value.length >= 8) {

                    newPassword.classList.remove(
                        'is-invalid'
                    );

                    newPassword.classList.add(
                        'is-valid'
                    );

                } else {

                    newPassword.classList.remove(
                        'is-valid'
                    );

                    newPassword.classList.add(
                        'is-invalid'
                    );

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FORM SUBMIT VALIDATION
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById('changePasswordForm');


    if (form) {

        form.addEventListener(
            'submit',
            function (event) {


                const current =
                    document.getElementById(
                        'current_password'
                    );


                const newPass =
                    document.getElementById(
                        'new_password'
                    );


                const confirmPass =
                    document.getElementById(
                        'confirm_password'
                    );


                if (
                    !current.value ||
                    !newPass.value ||
                    !confirmPass.value
                ) {

                    return;

                }


                if (newPass.value.length < 8) {

                    event.preventDefault();

                    alert(
                        'The new password must contain at least 8 characters.'
                    );

                    newPass.focus();

                    return;

                }


                if (
                    newPass.value !==
                    confirmPass.value
                ) {

                    event.preventDefault();

                    alert(
                        'The new passwords do not match.'
                    );

                    confirmPass.focus();

                    return;

                }

            }
        );

    }


});

</script>


</body>

</html>
