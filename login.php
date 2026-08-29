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

<title>PAF Personnel System - Login</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
  rel="stylesheet"
>

<style>

body {
  margin: 0;
  background: linear-gradient(135deg, #021631, #05254d);
  font-family: Poppins, Arial, sans-serif;
  color: #fff;
}

.login-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-card {
  background: rgba(0, 0, 0, 0.65);
  border-radius: 20px;
  padding: 40px;
  width: 380px;
  backdrop-filter: blur(10px);
  box-shadow: 0 0 30px rgba(0,0,0,0.8);
  text-align: center;
}

.login-card img {
  width: 90px;
  margin-bottom: 10px;
}

.login-card h3 {
  margin-bottom: 5px;
  text-transform: uppercase;
  font-weight: 700;
}

.login-card span {
  font-size: 0.9rem;
  color: #ffd700;
}

.form-control {
  background: #0c1a2d;
  border: 1px solid #1f3f6f;
  color: #fff;
}

.form-control:focus {
  background: #0c1a2d;
  color: #fff;
  box-shadow: 0 0 10px #ffd700;
  border-color: #ffd700;
}

.btn-login {
  background: linear-gradient(90deg,#0057b7,#003b88);
  border: none;
  color: #fff;
  padding: 10px 20px;
  font-weight: bold;
  border-radius: 25px;
  transition: 0.3s;
}

.btn-login:hover {
  background: #ffd700;
  color: #00204a;
}

input.form-control,
select.form-select,
textarea.form-control {
  color: #ffffff !important;
  background-color: rgba(0, 0, 0, 0.35) !important;
}

input::placeholder,
textarea::placeholder {
  color: rgba(255, 255, 255, 0.6) !important;
}

select.form-select option {
  color: #000 !important;
}

input.form-control:focus,
select.form-select:focus {
  border-color: #ffd700 !important;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.6) !important;
}

select.form-select {
  color: #ffffff !important;
  background-color: rgba(0, 0, 0, 0.35) !important;
  border: 1px solid #ffd700 !important;
}

select.form-select option {
  background-color: #1c2942 !important;
  color: #ffffff !important;
}

select.form-select option:checked,
select.form-select option:hover {
  background-color: #324a78 !important;
  color: #ffffff !important;
}

</style>

</head>

<body>

<div class="login-container">

  <div class="login-card">

    <img src="cmo1.png" alt="Philippine Air Force Logo">

    <h3>CMO Training Squadron</h3>

    <span>Personnel System Login</span>

    <form
      action="auth.php"
      method="POST"
      class="mt-4"
    >

      <div class="mb-3">

        <input
          type="text"
          name="username"
          class="form-control"
          placeholder="Username"
          required
          autocomplete="username"
        >

      </div>

      <div class="mb-4">

        <input
          type="password"
          name="password"
          class="form-control"
          placeholder="Password"
          required
          autocomplete="current-password"
        >

      </div>

      <button
        type="submit"
        class="btn btn-login w-100"
      >
        Login
      </button>

    </form>

    <?php if (isset($_GET['error'])): ?>

      <div class="mt-3 text-danger">
        Invalid username or password
      </div>

    <?php endif; ?>

  </div>

</div>

</body>
</html>