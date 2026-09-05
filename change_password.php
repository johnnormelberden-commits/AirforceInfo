<?php

session_start();

/*
 * ==========================================================
 * CHECK LOGIN
 * ==========================================================
 */

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


/*
 * ==========================================================
 * POSTGRESQL DATABASE CONNECTION
 * ==========================================================
 *
 * Render provides these through Environment Variables:
 *
 * DB_HOST
 * DB_PORT
 * DB_NAME
 * DB_USER
 * DB_PASSWORD
 *
 * Do NOT put the actual database password in this file.
 * ==========================================================
 */

try {

    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '5432';
    $database = getenv('DB_NAME');
    $dbUsername = getenv('DB_USER');
    $dbPassword = getenv('DB_PASSWORD');

    if (
        !$host ||
        !$database ||
        !$dbUsername ||
        !$dbPassword
    ) {
        throw new Exception("Database configuration is missing.");
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
     * Do not expose database credentials or
     * connection details to users.
     */

    die("Unable to connect to the database.");

}


/*
 * ==========================================================
 * GET CURRENT USER
 * ==========================================================
 */

$username = $_SESSION['username'] ?? '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


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

        $errorMessage = "All fields are required.";

    } elseif ($newPassword !== $confirmPassword) {

        $errorMessage = "New password and confirmation do not match.";

    } elseif (strlen($newPassword) < 6) {

        $errorMessage = "New password must be at least 6 characters.";

    } elseif ($currentPassword === $newPassword) {

        $errorMessage = "New password must be different from your current password.";

    } else {

        try {

            /*
             * ==================================================
             * GET CURRENT PASSWORD HASH
             * ==================================================
             */

            $stmt = $conn->prepare(
                "SELECT password
                 FROM users
                 WHERE username = :username
                 LIMIT 1"
            );

            $stmt->execute([
                ':username' => $username
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);


            /*
             * ==================================================
             * CHECK USER
             * ==================================================
             */

            if (!$user) {

                $errorMessage = "User not found.";

            } else {

                /*
                 * ==================================================
                 * VERIFY CURRENT PASSWORD
                 * ==================================================
                 */

                if (!password_verify(
                    $currentPassword,
                    $user['password']
                )) {

                    $errorMessage = "Current password is incorrect.";

                } else {

                    /*
                     * ==================================================
                     * HASH NEW PASSWORD
                     * ==================================================
                     */

                    $hashedPassword = password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );


                    /*
                     * ==================================================
                     * UPDATE PASSWORD
                     * ==================================================
                     */

                    $update = $conn->prepare(
                        "UPDATE users
                         SET password = :password
                         WHERE username = :username"
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
                     * Clear form fields
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

  <title>Change Password</title>


  <!-- Bootstrap -->

  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
  >


  <style>

    body {

      background:
        radial-gradient(
          circle at top left,
          #021631,
          #05254d
        );

      color: #e6edf3;

      font-family: "Poppins", sans-serif;

      min-height: 100vh;

      margin: 0;

      display: flex;

      align-items: center;

      justify-content: center;

    }


    .container-main {

      width: 100%;

      max-width: 500px;

      background: rgba(0, 0, 0, 0.7);

      border-radius: 15px;

      padding: 30px;

      box-shadow:
        0 0 25px rgba(0, 0, 0, 0.7);

      border: 1px solid #1f3b63;

    }


    h3 {

      color: #58a6ff;

      font-weight: 600;

    }


    .form-label {

      color: #d0e2ff;

      font-weight: 500;

    }


    .form-control {

      background-color: #050b16 !important;

      border: 1px solid #264b7c;

      color: #ffffff !important;

    }


    .form-control:focus {

      background-color: #071021 !important;

      border-color: #ffd700;

      box-shadow:
        0 0 8px rgba(255, 215, 0, 0.7);

      color: #ffffff !important;

    }


    .form-control::placeholder {

      color: rgba(255, 255, 255, 0.5);

    }


    .btn-primary {

      background-color: #0057b7;

      border: none;

    }


    .btn-primary:hover {

      background-color: #003b88;

      box-shadow:
        0 0 10px rgba(255, 215, 0, 0.8);

    }


    .btn-secondary {

      border: none;

    }


    .alert {

      border-radius: 10px;

    }

  </style>

</head>


<body>


<div class="container-main">

  <h3 class="mb-4 text-center">
    Change Password
  </h3>


  <!-- CURRENT USER -->

  <div class="text-center mb-4">

    <span class="text-light">
      Logged in as:
    </span>

    <strong style="color:#ffd700;">
      <?= htmlspecialchars($username); ?>
    </strong>

  </div>


  <!-- ERROR MESSAGE -->

  <?php if (!empty($errorMessage)): ?>

    <div
      class="alert alert-danger"
      role="alert"
    >

      <?= htmlspecialchars($errorMessage); ?>

    </div>

  <?php endif; ?>


  <!-- SUCCESS MESSAGE -->

  <?php if (!empty($successMessage)): ?>

    <div
      class="alert alert-success"
      role="alert"
    >

      <?= htmlspecialchars($successMessage); ?>

    </div>

  <?php endif; ?>


  <!-- CHANGE PASSWORD FORM -->

  <form method="post">


    <!-- CURRENT PASSWORD -->

    <div class="mb-3">

      <label class="form-label">
        Current Password
      </label>

      <input
        type="password"
        class="form-control"
        name="current_password"
        autocomplete="current-password"
        required
      >

    </div>


    <!-- NEW PASSWORD -->

    <div class="mb-3">

      <label class="form-label">
        New Password
      </label>

      <input
        type="password"
        class="form-control"
        name="new_password"
        minlength="6"
        autocomplete="new-password"
        required
      >

      <div class="form-text text-secondary">
        Minimum 6 characters.
      </div>

    </div>


    <!-- CONFIRM PASSWORD -->

    <div class="mb-4">

      <label class="form-label">
        Confirm New Password
      </label>

      <input
        type="password"
        class="form-control"
        name="confirm_password"
        minlength="6"
        autocomplete="new-password"
        required
      >

    </div>


    <!-- BUTTONS -->

    <div class="d-flex justify-content-between">

      <a
        href="index.php"
        class="btn btn-secondary"
      >
        Back
      </a>


      <button
        type="submit"
        class="btn btn-primary"
      >
        Update Password
      </button>

    </div>


  </form>

</div>


</body>

</html>