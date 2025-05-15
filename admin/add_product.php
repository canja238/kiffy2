<?php
require_once '../config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// Form data
$product_name = $sku = $product_description = $price = $category = $stock_quantity = '';
$is_featured = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $sku = trim($_POST['sku']);
    $product_description = trim($_POST['product_description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);
    $stock_quantity = trim($_POST['stock_quantity']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle image upload
    $product_image = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['product_image']['name']);
        $target_path = $upload_dir . $file_name;
        
        // Check if file is an image
        $image_info = getimagesize($_FILES['product_image']['tmp_name']);
        if ($image_info !== false) {
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                $product_image = 'uploads/products/' . $file_name;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'File is not an image.';
        }
    }
    
    // Validate inputs
    if (empty($product_name) || empty($sku) || empty($price) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Price must be a positive number.';
    } elseif (!is_numeric($stock_quantity) || $stock_quantity < 0) {
        $error = 'Stock quantity must be a non-negative number.';
    } else {
        // Check if SKU already exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'SKU already exists. Please use a different SKU.';
        } else {
            // Insert new product
            $stmt = $pdo->prepare("INSERT INTO products 
                                  (product_name, sku, product_description, price, category, 
                                   product_image, stock_quantity, is_featured) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([
                $product_name, $sku, $product_description, $price, $category,
                $product_image, $stock_quantity, $is_featured
            ])) {
                $success = 'Product added successfully!';
                // Clear form
                $product_name = $sku = $product_description = $price = $category = $stock_quantity = '';
                $is_featured = 0;
            } else {
                $error = 'Failed to add product. Please try again.';
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
    <title>Add Product - Bitronics</title>
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
        
        .form-container {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .form-group .checkbox-label input {
            margin-right: 10px;
        }
        
        .image-preview {
            width: 150px;
            height: 150px;
            border: 1px dashed var(--border);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            overflow: hidden;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
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
                <li><a href="products.php" class="active"><i class="fas fa-box-open"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="../index.php"><i class="fas fa-sign-out-alt"></i> Back</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Add Product</h1>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>

            </div>
        </div>
        
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
        
        <div class="form-container">
            <form action="add_product.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Product Name *</label>
                    <input type="text" id="product_name" name="product_name" 
                           value="<?php echo htmlspecialchars($product_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="sku">SKU (Stock Keeping Unit) *</label>
                    <input type="text" id="sku" name="sku" 
                           value="<?php echo htmlspecialchars($sku); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Description</label>
                    <textarea id="product_description" name="product_description"><?php echo htmlspecialchars($product_description); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" 
                           value="<?php echo htmlspecialchars($price); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <input type="text" id="category" name="category" 
                           value="<?php echo htmlspecialchars($category); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" 
                           value="<?php echo htmlspecialchars($stock_quantity); ?>">
                </div>
                
                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <div class="image-preview" id="imagePreview">
                        <i class="fas fa-image" style="font-size: 24px; color: #ccc;"></i>
                    </div>
                    <input type="file" id="product_image" name="product_image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" <?php echo $is_featured ? 'checked' : ''; ?>>
                        Featured Product
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Product
                </button>
                <a href="products.php" class="btn" style="background-color: var(--border); margin-left: 10px;">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
            </form>
        </div>
    </div>

    <script>
        // Image preview functionality
        const productImage = document.getElementById('product_image');
        const imagePreview = document.getElementById('imagePreview');
        
        productImage.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = this.result;
                    imagePreview.appendChild(img);
                });
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>