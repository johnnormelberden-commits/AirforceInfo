<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "airforce_info");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];
$currentPassword = "";
$newPassword = "";
$confirmPassword = "";
$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic checks
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = "All fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "New password and confirmation do not match.";
    } elseif (strlen($newPassword) < 6) {
        $errorMessage = "New password must be at least 6 characters.";
    } else {
        // Get user from DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                $errorMessage = "Current password is incorrect.";
            } else {
                // Update with new hashed password
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update->bind_param("ss", $hashed, $username);
                $update->execute();

                if ($update->affected_rows > 0) {
                    $successMessage = "Password updated successfully.";
                    $currentPassword = $newPassword = $confirmPassword = "";
                } else {
                    $errorMessage = "No changes made. Try again.";
                }
            }
        } else {
            $errorMessage = "User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <style>
    body {
      background: radial-gradient(circle at top left, #021631, #05254d);
      color: #e6edf3;
      font-family: "Poppins", sans-serif;
      min-height: 100vh;
      margin: 0;
    }
    .container-main {
      margin-top: 60px;
      max-width: 500px;
      background: rgba(0,0,0,0.7);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 0 25px rgba(0,0,0,0.7);
    }
    .form-control {
      background-color: #050b16;
      border: 1px solid #264b7c;
      color: #e6edf3;
    }
    .form-control:focus {
      background-color: #071021;
      border-color: #ffd700;
      box-shadow: 0 0 8px rgba(255,215,0,0.7);
    }
    .btn-primary {
      background-color: #0057b7;
      border: none;
    }
    .btn-primary:hover {
      background-color: #003b88;
      box-shadow: 0 0 10px rgba(255,215,0,0.8);
    }

    
    
  </style>
</head>
<body>

<div class="container container-main">
  <h3 class="mb-3 text-center">Change Password</h3>

  <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger"><?= $errorMessage ?></div>
  <?php endif; ?>

  <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success"><?= $successMessage ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Current Password</label>
      <input type="password" class="form-control" name="current_password" required>
    </div>

    <div class="mb-3">
      <label class="form-label">New Password</label>
      <input type="password" class="form-control" name="new_password" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm New Password</label>
      <input type="password" class="form-control" name="confirm_password" required>
    </div>

    <div class="d-flex justify-content-between">
      <a href="index.php" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Update Password</button>
    </div>
  </form>
</div>

</body>
</html>
