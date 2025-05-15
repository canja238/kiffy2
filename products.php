<?php
require_once 'config.php';
session_start();

// Get search query if any
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build query for products
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (product_name LIKE ? OR product_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY is_featured DESC, product_name ASC";

// Get products
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for filter
$stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Bitronics</title>
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
        
        .products-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .products-header {
            margin-bottom: 30px;
        }
        
        .products-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .products-filter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-label {
            font-weight: 500;
            color: var(--primary);
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background-color: var(--white);
            min-width: 150px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-details {
            padding: 15px;
        }
        
        .product-category {
            font-size: 12px;
            color: var(--light-text);
            margin-bottom: 5px;
        }
        
        .product-name {
            font-weight: 500;
            margin-bottom: 10px;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-price {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .product-stock {
            font-size: 12px;
            margin-bottom: 15px;
        }
        
        .product-stock.in-stock {
            color: var(--success);
        }
        
        .product-stock.out-of-stock {
            color: var(--error);
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
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
            margin-right: 5px;
        }
        
        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--success);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .product-card-wrapper {
            position: relative;
        }
        
        .no-products {
            text-align: center;
            padding: 40px 0;
            grid-column: 1 / -1;
        }
        
        .no-products i {
            font-size: 50px;
            color: var(--light-text);
            margin-bottom: 20px;
        }
        
        .no-products p {
            color: var(--light-text);
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .products-filter {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .filter-select {
                width: 100%;
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
                       value="<?php echo htmlspecialchars($search); ?>"
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
                    <li><a href="products.php" class="active">PRODUCTS</a></li>
                    <li><a href="#">BRANDS</a></li>
                    <li><a href="#">TECHNICAL SUPPORT</a></li>
                    <li><a href="#">ABOUT US</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="products-container">
        <div class="products-header">
            <h1>Our Products</h1>
            
            <div class="products-filter">
                <div class="filter-group">
                    <span class="filter-label">Filter by:</span>
                    <select class="filter-select" onchange="window.location.href='products.php?category='+this.value">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <span class="filter-label">Sort by:</span>
                    <select class="filter-select" onchange="sortProducts(this.value)">
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="name_desc">Name (Z-A)</option>
                        <option value="price_asc">Price (Low to High)</option>
                        <option value="price_desc">Price (High to Low)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <p>No products found matching your criteria.</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-undo"></i> Reset Filters
                </a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-card-wrapper">
                            <?php if ($product['is_featured']): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                            
                            <?php if ($product['product_image']): ?>
                                <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div style="width: 100%; height: 200px; background-color: #eee; 
                                            display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-box-open" style="font-size: 40px; color: #999;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-details">
                                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-stock <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </div>
                                <div class="product-actions">
                                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button class="btn btn-primary add-to-cart" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-shopping-cart"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
        
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount();
                        alert('Product added to cart!');
                    } else {
                        alert('Failed to add product to cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to cart.');
                });
            });
        });
        
        // Sort products (client-side for demo, would be server-side in production)
        function sortProducts(value) {
            // In a real application, this would reload the page with the new sort parameter
            alert('Sorting by ' + value + ' would be implemented here');
        }
    </script>
</body>
</html>