<?php
require_once '../config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized access');
}

if (!isset($_GET['order_id'])) {
    die('Order ID not provided');
}

$order_id = $_GET['order_id'];

// Get order details
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email 
                       FROM orders o JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die('Order not found');
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.product_image 
                       FROM order_items oi JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<div class="order-details">
    <h3>Order #<?php echo $order['id']; ?></h3>
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
    <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
    <p><strong>Status:</strong> 
        <span class="status status-<?php echo strtolower($order['status']); ?>">
            <?php echo ucfirst($order['status']); ?>
        </span>
    </p>
    <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
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
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php if ($item['product_image']): ?>
                        <img src="../<?php echo htmlspecialchars($item['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; background-color: #eee; 
                                    display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-box-open" style="color: #999;"></i>
                        </div>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($item['product_name']); ?>
                </div>
            </td>
            <td>₱<?php echo number_format($item['price'], 2); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>