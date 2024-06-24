<?php
session_start(); // Start session for storing login status

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    header("Location: /dashboard.php"); // Redirect to dashboard if logged in
    exit;
}

// Database connection
require_once 'connection.php';

// Registration logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = htmlspecialchars(strip_tags($_POST['registerUsername']));
    $email = htmlspecialchars(strip_tags($_POST['registerEmail']));
    $password = password_hash($_POST['registerPassword'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        // Registration successful
        echo '<script>alert("Registration successful");</script>';
    } catch (PDOException $e) {
        // Registration failed
        echo '<script>alert("Registration failed: ' . $e->getMessage() . '");</script>';
    }
}

// Login logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = htmlspecialchars(strip_tags($_POST['loginUsername']));
    $password = $_POST['loginPassword'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username; // Store username in session
            $_SESSION['user_id'] = $user['id'];
            header("Location: /dashboard.php"); // Redirect to dashboard on successful login
            exit;
        } else {
            // Invalid username or password
            echo '<script>alert("Invalid username or password");</script>';
        }
    } catch (PDOException $e) {
        // Login failed
        echo '<script>alert("Login failed: ' . $e->getMessage() . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login, Register, Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="password"] {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-container button {
            margin-top: 10px;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
        .form-container .form-options {
            margin-top: 20px;
            text-align: center;
        }
        .form-container .form-options a {
            text-decoration: none;
            color: #4CAF50;
            margin: 0 10px;
        }
        .form-container .form-options a:hover {
            text-decoration: underline;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="form-container" id="loginForm">
        <h2>Login</h2>
        <form method="POST" action="">
            <input type="text" name="loginUsername" placeholder="Username" required>
            <input type="password" name="loginPassword" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <div class="form-options">
            <a href="#" id="showRegister">Create an Account</a> | 
            <a href="#" id="showForgotPassword">Forgot Password?</a>
        </div>
    </div>

    <div class="form-container hidden" id="registerForm">
        <h2>Register</h2>
        <form method="POST" action="">
            <input type="text" name="registerUsername" placeholder="Username" required>
            <input type="email" name="registerEmail" placeholder="Email" required>
            <input type="password" name="registerPassword" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <div class="form-options">
            <a href="#" id="showLogin">Back to Login</a>
        </div>
    </div>

    <div class="form-container hidden" id="forgotPasswordForm">
        <h2>Forgot Password</h2>
        <form method="POST" action="">
            <input type="email" name="forgotPasswordEmail" placeholder="Email" required>
            <button type="submit" name="forgotPassword">Reset Password</button>
        </form>
        <div class="form-options">
            <a href="#" id="showLogin">Back to Login</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            
            const showLogin = document.getElementById('showLogin');
            const showRegister = document.getElementById('showRegister');
            const showForgotPassword = document.getElementById('showForgotPassword');

            showLogin.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                forgotPasswordForm.classList.add('hidden');
            });

            showRegister.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                forgotPasswordForm.classList.add('hidden');
            });

            showForgotPassword.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.classList.add('hidden');
                registerForm.classList.add('hidden');
                forgotPasswordForm.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
