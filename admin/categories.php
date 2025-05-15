<?php
require_once '../config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $category_name = $_GET['delete'];
    
    // First, check if there are products in this category
    $stmt = $pdo->prepare("SELECT COUNT(*) as product_count FROM products WHERE category = ?");
    $stmt->execute([$category_name]);
    $result = $stmt->fetch();
    
    if ($result['product_count'] > 0) {
        header('Location: categories.php?error=1');
        exit;
    }
    
    header('Location: categories.php?deleted=1');
    exit;
}

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    
    if (!empty($category_name)) {
        // In a real application, you might have a separate categories table
        // For this demo, we'll just check if any products use this category
        header('Location: categories.php?added=1');
        exit;
    }
}

// Get all unique categories from products
$stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Bitronics</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reuse the same styles from admin/index.php */
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
            --dark-gray: #e0e0e0;
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
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: var(--white);
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: var(--white);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            font-size: 24px;
            color: var(--primary);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
        }
        
        .user-menu img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .user-menu span {
            font-weight: 500;
            margin-right: 15px;
        }
        
        .user-menu a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            border: 1px solid var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .category-list {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .category-name {
            font-weight: 500;
        }
        
        .category-actions a {
            color: var(--error);
            text-decoration: none;
            margin-left: 10px;
        }
        
        .add-category-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .add-category-form input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success);
            border: 1px solid rgba(42, 157, 143, 0.3);
        }
        
        .alert-error {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--error);
            border: 1px solid rgba(230, 57, 70, 0.3);
        }
        
        .alert-icon {
            margin-right: 10px;
            font-size: 16px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .sidebar.active {
                width: 250px;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>BITRONICS</h2>
            <p>Admin Panel</p>
        </div>
        
        <div class="sidebar-menu">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box-open"></i> Products</a></li>
                <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="../index.php"><i class="fas fa-sign-out-alt"></i> Back</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Manage Categories</h1>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>

            </div>
        </div>
        
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle alert-icon"></i>
                Category has been added successfully.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle alert-icon"></i>
                Category has been deleted successfully.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                Cannot delete category because there are products assigned to it.
            </div>
        <?php endif; ?>
        
        <div class="action-bar">
            <form class="add-category-form" method="post">
                <input type="text" name="category_name" placeholder="New category name" required>
                <button type="submit" name="add_category" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </form>
        </div>
        
        <div class="category-list">
            <?php if (empty($categories)): ?>
                <p>No categories found.</p>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-item">
                        <div class="category-name"><?php echo htmlspecialchars($category); ?></div>
                        <div class="category-actions">
                            <a href="categories.php?delete=<?php echo urlencode($category); ?>" 
                               onclick="return confirm('Are you sure you want to delete this category?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>