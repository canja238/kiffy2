<?php
require_once 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission for updating account
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email is being changed to one that already exists
        if ($email !== $user['email']) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = 'Email already exists.';
            }
        }

        if (!$error) {
            // Handle password change if provided
            $password_update = '';
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Current password is required to change password.';
                } elseif (!password_verify($current_password, $user['password']) && $current_password !== $user['password']) {
                    $error = 'Current password is incorrect.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 8) {
                    $error = 'Password must be at least 8 characters long.';
                } else {
                    $password_update = password_hash($new_password, PASSWORD_BCRYPT);
                }
            }

            if (!$error) {
                // Update user in database
                if ($password_update) {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $password_update, $_SESSION['user_id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $_SESSION['user_id']]);
                }

                // Update session
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                $success = 'Account updated successfully!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
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
    <title>My Account - Bitronics</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reuse styles from your existing CSS */
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
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--gray);
            color: var(--text);
            line-height: 1.6;
        }
        
        .account-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .account-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            font-size: 24px;
            color: var(--primary);
        }
        
        .account-content {
            display: flex;
            gap: 30px;
        }
        
        .account-sidebar {
            width: 250px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .account-sidebar h3 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }
        
        .account-sidebar ul {
            list-style: none;
        }
        
        .account-sidebar li {
            margin-bottom: 10px;
        }
        
        .account-sidebar a {
            display: block;
            padding: 8px 10px;
            color: var(--text);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .account-sidebar a:hover, .account-sidebar a.active {
            background-color: rgba(15, 47, 77, 0.1);
            color: var(--primary);
        }
        
        .account-sidebar i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .account-main {
            flex: 1;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .account-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
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
        
        @media (max-width: 768px) {
            .account-content {
                flex-direction: column;
            }
            
            .account-sidebar {
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header - Reuse from index.php -->
    <header>
        <div class="header-container">
            <h1 class="font-orbitron">BITRONICS</h1>

            <form class="search-form" action="products.php" method="get">
                <input class="search-input" 
                       placeholder="Search for Products" 
                       type="text"
                       name="search"
                       aria-label="Search products"/>
                <button class="search-button" aria-label="Search">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <button class="cart-button" aria-label="Cart" onclick="window.location.href='cart.php'">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count">0</span>
                </button>
                
                <div class="account-dropdown">
                    <button class="user-button">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="account.php" class="active"><i class="fas fa-user-circle"></i> My Account</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin/"><i class="fas fa-cog"></i> Admin Panel</a>
                        <?php endif; ?>
                        <a href="orders.php"><i class="fas fa-clipboard-list"></i> My Orders</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation bar -->
        <nav>
            <div class="nav-container">
                <ul class="nav-list">
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="products.php">PRODUCTS</a></li>
                    <li><a href="#">BRANDS</a></li>
                    <li><a href="#">TECHNICAL SUPPORT</a></li>
                    <li><a href="#">ABOUT US</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="account-container">
        <div class="account-header">
            <h1>My Account</h1>
        </div>
        
        <div class="account-content">
            <div class="account-sidebar">
                <h3>My Account</h3>
                <ul>
                    <li><a href="account.php" class="active"><i class="fas fa-user"></i> Account Details</a></li>
                    <li><a href="orders.php"><i class="fas fa-clipboard-list"></i> My Orders</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <div class="account-main">
                <div class="account-section">
                    <h2>Account Details</h2>
                    
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
                    
                    <form action="account.php" method="post">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password (leave blank to keep unchanged)</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password (leave blank to keep unchanged)</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer - Reuse from index.php -->
    <footer>
        <div class="footer-container">
            <div>
                <h2 class="footer-logo">BITRONICS</h2>
                <address class="footer-address">
                    Quezon Avenue, Cotabato City
                </address>
                <p class="footer-contact">
                    <i class="fas fa-phone-alt"></i>+639998893894
                </p>
                <p class="footer-contact">
                    <i class="fas fa-envelope"></i>sales@bitronics-electronics.com
                </p>
            </div>

            <div>
                <h3 class="footer-heading">Company</h3>
                <ul class="footer-links">
                    <li><a href="#">
                        <i class="fas fa-map-marker-alt"></i>Store Locations
                    </a></li>
                    <li><a href="#">
                        <i class="fas fa-star"></i>Reviews
                    </a></li>
                    <li><a href="#">
                        <i class="fas fa-info-circle"></i>About Us
                    </a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="footer-heading">Links</h3>
                <ul class="footer-links">
                    <li><a href="#" target="_blank">
                        <i class="fas fa-external-link-alt"></i>Shopee Official Store
                    </a></li>
                    <li><a href="#" target="_blank">
                        <i class="fas fa-external-link-alt"></i>Lazada Official Store
                    </a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="footer-heading">Follow Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Update cart count
        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCountElements = document.querySelectorAll('.cart-count');
                        cartCountElements.forEach(element => {
                            element.textContent = data.count;
                        });
                    }
                });
        }
        
        // Initialize cart count on page load
        updateCartCount();
    </script>
</body>
</html>