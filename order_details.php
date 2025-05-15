<?php
require_once 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = $_GET['id'];

// Get order details
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email 
                       FROM orders o JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.product_image 
                       FROM order_items oi JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Bitronics</title>
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
        
        .order-details {
            margin-bottom: 30px;
        }
        
        .order-details p {
            margin-bottom: 8px;
        }
        
        .order-details strong {
            font-weight: 500;
            color: var(--primary);
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .order-items th, .order-items td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .order-items th {
            font-weight: 500;
            font-size: 13px;
            color: var(--light-text);
            text-transform: uppercase;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .order-summary {
            margin-top: 30px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
        
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-total {
            font-weight: 600;
            font-size: 18px;
            color: var(--primary);
        }
        
        .btn {
            padding: 8px 16px;
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
        
        @media (max-width: 768px) {
            .account-content {
                flex-direction: column;
            }
            
            .account-sidebar {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .order-items {
                display: block;
                overflow-x: auto;
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
                        <a href="account.php"><i class="fas fa-user-circle"></i> My Account</a>
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
            <h1>Order Details</h1>
            <a href="orders.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
        
        <div class="account-content">
            <div class="account-sidebar">
                <h3>My Account</h3>
                <ul>
                    <li><a href="account.php"><i class="fas fa-user"></i> Account Details</a></li>
                    <li><a href="orders.php" class="active"><i class="fas fa-clipboard-list"></i> My Orders</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <div class="account-main">
                <div class="account-section">
                    <h2>Order #<?php echo $order['id']; ?></h2>
                    
                    <div class="order-details">
                        <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </p>
                        <p><strong>Payment Method:</strong> Cash on Delivery</p>
                    </div>
                    
                    <table class="order-items">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="order-item">
                                        <?php if ($item['product_image']): ?>
                                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 class="order-item-image">
                                        <?php else: ?>
                                            <div style="width: 60px; height: 60px; background-color: #eee; 
                                                        display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-box-open" style="color: #999;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="order-item-details">
                                            <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="order-summary">
                        <div class="order-summary-row">
                            <span>Subtotal:</span>
                            <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="order-summary-row">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="order-summary-row order-total">
                            <span>Total:</span>
                            <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
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