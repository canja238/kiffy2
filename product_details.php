<?php
require_once 'config.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=product_details.php?id='.$product_id);
        exit;
    }
    
    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) $quantity = 1;
    
    // Check if already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cart_item = $stmt->fetch();
    
    if ($cart_item) {
        $new_quantity = $cart_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $cart_item['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
    }
    
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Bitronics</title>
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
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--gray);
            color: var(--text);
            line-height: 1.6;
        }
        
        .product-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .product-header {
            margin-bottom: 20px;
        }
        
        .product-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            font-size: 24px;
            color: var(--primary);
        }
        
        .breadcrumb {
            font-size: 14px;
            color: var(--light-text);
            margin-bottom: 20px;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .product-detail {
            display: flex;
            gap: 30px;
            background-color: var(--white);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .product-images {
            flex: 1;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            background-color: var(--gray);
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .thumbnail-container {
            display: flex;
            gap: 10px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid var(--border);
        }
        
        .thumbnail:hover {
            border-color: var(--primary);
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-category {
            font-size: 14px;
            color: var(--light-text);
            margin-bottom: 10px;
        }
        
        .product-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .product-price {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .product-stock {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .in-stock {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success);
        }
        
        .out-of-stock {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--error);
        }
        
        .product-description {
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-input {
            width: 60px;
            padding: 10px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 4px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            flex: 1;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .btn-secondary {
            background-color: var(--white);
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-secondary:hover {
            background-color: rgba(15, 47, 77, 0.1);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .product-meta {
            margin-top: 30px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
        
        .meta-item {
            margin-bottom: 10px;
        }
        
        .meta-label {
            font-weight: 500;
            color: var(--primary);
            display: inline-block;
            width: 120px;
        }
        
        @media (max-width: 768px) {
            .product-detail {
                flex-direction: column;
            }
            
            .main-image {
                height: 300px;
            }
            
            .product-actions {
                flex-direction: column;
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
                
                <?php if (isset($_SESSION['user_id'])): ?>
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
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none; color: var(--primary); font-weight: 600;">LOGIN</a>
                    <span style="color: var(--primary);">|</span>
                    <a href="signup.php" style="text-decoration: none; color: var(--primary); font-weight: 600;">SIGN UP</a>
                <?php endif; ?>
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
    <div class="product-container">
        <div class="product-header">
            <div class="breadcrumb">
                <a href="products.php">Products</a> &raquo; <?php echo htmlspecialchars($product['product_name']); ?>
            </div>
            <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
        </div>
        
        <div class="product-detail">
            <div class="product-images">
                <?php if ($product['product_image']): ?>
                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                         class="main-image" id="mainImage">
                <?php else: ?>
                    <div class="main-image" style="display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-box-open" style="font-size: 50px; color: var(--light-text);"></i>
                    </div>
                <?php endif; ?>
                
                <div class="thumbnail-container">
                    <?php if ($product['product_image']): ?>
                        <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="thumbnail" onclick="document.getElementById('mainImage').src = this.src">
                    <?php endif; ?>
                    <!-- Additional thumbnails would go here -->
                </div>
            </div>
            
            <div class="product-info">
                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                <h2 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                
                <div class="product-stock <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                    <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                </div>
                
                <?php if ($product['product_description']): ?>
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
                    </div>
                <?php endif; ?>
                
                <form action="product_details.php?id=<?php echo $product_id; ?>" method="post">
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <label for="quantity">Qty:</label>
                            <input type="number" id="quantity" name="quantity" class="quantity-input" 
                                   value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn btn-primary" 
                                <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        
                        <a href="cart.php" class="btn btn-secondary">
                            <i class="fas fa-shopping-bag"></i> View Cart
                        </a>
                    </div>
                </form>
                
                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">SKU:</span>
                        <?php echo $product['sku'] ? htmlspecialchars($product['sku']) : 'N/A'; ?>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Category:</span>
                        <?php echo $product['category'] ? htmlspecialchars($product['category']) : 'N/A'; ?>
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