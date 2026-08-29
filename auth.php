<?php
session_start();
$conn = new mysqli("localhost", "root", "", "airforce_info");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    if (password_verify($password, $user['password'])) {


        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;

        // ✅ Remember Me feature
        if ($remember) {
            setcookie("remember_user", $username, time() + (86400 * 30), "/");
        }

        // ✅ Log activity
       // $log = $conn->prepare("INSERT INTO login_logs (username, action) VALUES (?, 'LOGIN')");
       // $log->bind_param("s", $username);
     //   $log->execute();

        header("Location: index.php");
        exit;
    }
}

header("Location: login.php?error=1");
exit;
