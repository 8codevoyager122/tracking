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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            width: 100%;
            box-sizing: border-box;
            position: relative;
        }
        .form-container .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-container .password-container input[type="password"] {
            padding-right: 40px; /* Ensure space for the icon */
        }
        .form-container .password-toggle-icon {
            position: absolute;
            right: 10px; /* Position the icon */
            top: 50%;
            transform: translateY(-70%); /* Center the icon vertically */
            font-size: 18px; /* Size of the icon */
            color: #333;
            cursor: pointer;
            z-index: 10; /* Ensure the icon is above the input field */
        }
        .form-container .password-toggle-icon:hover {
            color: #4CAF50;
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
            <input type="text" name="loginUsername" placeholder="Username" value="<?php echo $savedUsername; ?>" required>
            <div class="password-container">
                <input type="password" name="loginPassword" placeholder="Password" id="loginPassword" required>
                <i class="password-toggle-icon fas fa-eye" id="toggleLoginPassword"></i>
            </div>
            <div>
                <input type="checkbox" name="rememberMe" id="rememberMe">
                <label for="rememberMe">Remember Me</label>
            </div>
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
            <div class="password-container">
                <input type="password" name="registerPassword" placeholder="Password" id="registerPassword" required>
                <i class="password-toggle-icon fas fa-eye" id="toggleRegisterPassword"></i>
            </div>
            <button type="submit" name="register">Register</button>
        </form>
        <div class="form-options">
            <a href="login.php" id="showLogin">Back to Login</a>
        </div>
    </div>

    <div class="form-container hidden" id="forgotPasswordForm">
        <h2>Forgot Password</h2>
        <form method="POST" action="">
            <input type="email" name="forgotPasswordEmail" placeholder="Email" required>
            <button type="submit" name="forgotPassword">Reset Password</button>
        </form>
        <div class="form-options">
            <a href="login.php" id="showLogin">Back to Login</a>
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

            const toggleLoginPassword = document.getElementById('toggleLoginPassword');
            const loginPassword = document.getElementById('loginPassword');

            const toggleRegisterPassword = document.getElementById('toggleRegisterPassword');
            const registerPassword = document.getElementById('registerPassword');

            toggleLoginPassword.addEventListener('click', function() {
                if (loginPassword.type === 'password') {
                    loginPassword.type = 'text';
                    toggleLoginPassword.classList.remove('fa-eye');
                    toggleLoginPassword.classList.add('fa-eye-slash');
                } else {
                    loginPassword.type = 'password';
                    toggleLoginPassword.classList.remove('fa-eye-slash');
                    toggleLoginPassword.classList.add('fa-eye');
                }
            });

            toggleRegisterPassword.addEventListener('click', function() {
                if (registerPassword.type === 'password') {
                    registerPassword.type = 'text';
                    toggleRegisterPassword.classList.remove('fa-eye');
                    toggleRegisterPassword.classList.add('fa-eye-slash');
                } else {
                    registerPassword.type = 'password';
                    toggleRegisterPassword.classList.remove('fa-eye-slash');
                    toggleRegisterPassword.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html>
