_<?php
$conn = new mysqli("localhost", "root", "", "airforce_info");

$username = "admin";
$password = password_hash("12345", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

echo "Encrypted admin user created.";

