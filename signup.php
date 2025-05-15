<?php
require_once 'config.php';

$error = '';
$success = '';
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already exists.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password])) {
                // If this is the admin account, update the role
                if ($email === 'admin@gmail.com') {
                    $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?")
                        ->execute([$email]);
                }
                
                $success = 'Registration successful! You can now login.';
                $username = $email = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Bitronics</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #0f2f4d;
            --primary-light: #1a3d5f;
            --secondary: #d9e6eb;
            --accent: #3a86ff;
            --text: #333;
            --light-text: #777;
            --border: #e0e0e0;
            --error: #e63946;
            --success: #2a9d8f;
            --white: #fff;
            --gray: #f5f5f5;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--gray);
            color: var(--text);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(135deg, var(--secondary) 0%, var(--white) 100%);
        }
        
        .auth-container {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            position: relative;
        }
        
        .auth-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .auth-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .auth-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .auth-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
            font-size: 14px;
        }
        
        .input-field {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: var(--gray);
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 47, 77, 0.1);
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--light-text);
        }
        
        .password-strength {
            height: 4px;
            background-color: var(--gray);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .password-strength::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background-color: var(--error);
            transition: width 0.3s, background-color 0.3s;
        }
        
        .password-strength[data-strength="1"]::before {
            width: 25%;
            background-color: var(--error);
        }
        
        .password-strength[data-strength="2"]::before {
            width: 50%;
            background-color: #ffbe0b;
        }
        
        .password-strength[data-strength="3"]::before {
            width: 75%;
            background-color: #3a86ff;
        }
        
        .password-strength[data-strength="4"]::before {
            width: 100%;
            background-color: var(--success);
        }
        
        .password-hint {
            font-size: 12px;
            color: var(--light-text);
            margin-top: 5px;
            display: none;
        }
        
        .auth-btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .auth-btn:hover {
            background-color: var(--primary-light);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .alert-error {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--error);
            border: 1px solid rgba(230, 57, 70, 0.3);
        }
        
        .alert-success {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success);
            border: 1px solid rgba(42, 157, 143, 0.3);
        }
        
        .alert-icon {
            margin-right: 10px;
            font-size: 16px;
        }
        
        @media (max-width: 480px) {
            .auth-container {
                border-radius: 0;
            }
            
            .auth-header, .auth-form {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>BITRONICS</h1>
            <p>Create your account</p>
        </div>
        
        <div class="auth-form">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form action="signup.php" method="post" id="signupForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="input-field" 
                           value="<?php echo htmlspecialchars($username); ?>" required
                           placeholder="Choose a username">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="input-field" 
                           value="<?php echo htmlspecialchars($email); ?>" required
                           placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password (min 8 characters)</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="input-field" 
                               required placeholder="Create a password" minlength="8"
                               oninput="checkPasswordStrength(this.value)">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                    <div class="password-strength" data-strength="0"></div>
                    <div class="password-hint" id="passwordHint"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" class="input-field" 
                               required placeholder="Confirm your password" minlength="8"
                               oninput="checkPasswordMatch()">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                    </div>
                    <div class="password-hint" id="confirmHint"></div>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus"></i> Sign Up
                </button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const icon = passwordField.nextElementSibling;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function checkPasswordStrength(password) {
            const strengthBar = document.querySelector('.password-strength');
            const hint = document.getElementById('passwordHint');
            
            // Reset
            let strength = 0;
            hint.style.display = 'none';
            hint.textContent = '';
            
            if (password.length === 0) {
                strengthBar.setAttribute('data-strength', '0');
                return;
            }
            
            // Check length
            if (password.length >= 8) strength += 1;
            
            // Check for numbers
            if (/\d/.test(password)) strength += 1;
            
            // Check for special chars
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            
            // Check for uppercase and lowercase
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Cap at 4
            strength = Math.min(strength, 4);
            
            strengthBar.setAttribute('data-strength', strength.toString());
            
            // Show hints if needed
            if (strength < 3 && password.length > 0) {
                hint.style.display = 'block';
                if (password.length < 8) {
                    hint.textContent = 'Password should be at least 8 characters';
                } else if (!/\d/.test(password)) {
                    hint.textContent = 'Add numbers to strengthen your password';
                } else if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                    hint.textContent = 'Add special characters to strengthen your password';
                } else {
                    hint.textContent = 'Consider mixing uppercase and lowercase letters';
                }
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const hint = document.getElementById('confirmHint');
            
            if (confirmPassword.length === 0) {
                hint.style.display = 'none';
                return;
            }
            
            hint.style.display = 'block';
            
            if (password !== confirmPassword) {
                hint.textContent = 'Passwords do not match';
                hint.style.color = 'var(--error)';
            } else {
                hint.textContent = 'Passwords match';
                hint.style.color = 'var(--success)';
            }
        }
        
        // Form validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please check and try again.');
            }
        });
    </script>
</body>
</html>