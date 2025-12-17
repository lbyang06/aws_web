<?php
require '../base.php';
include '../head.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'Invalid order ID');
    redirect('../index.php');
}

$stmt = $_db->prepare("SELECT * FROM `order` WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_OBJ);

if (!$order) {
    flash('error', 'Order not found');
    redirect('../index.php');
}

$stmt = $_db->prepare("
    SELECT 
        od.unit,
        od.price,
        od.subtotal,
        p.name,
        p.photo
    FROM order_detail od
    JOIN product p ON od.product_id = p.id
    WHERE od.order_id = ?
    ORDER BY p.name ASC
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<div class="container">

    <!-- LEFT SIDEBAR -->
    <div class="sidebar">
        <div class="logo-card">
            <div class="logo-icon">☕</div>
            <h1>HardRock Cafe</h1>
        </div>

        <a href="../index.php" class="menu-btn">☕ Drink Menu</a>
        <a href="../cart.php" class="menu-btn">🛒 My Cart</a>
        <a href="history.php" class="menu-btn active">🧾 My Order</a>
    </div>

    <!-- RIGHT CONTENT -->
    <div class="content">
        <h1 style="color:white; text-align:center; margin-bottom:20px;">Order Confirmed!</h1>
        <hr style="border-color:#666;">

        <!-- Big Order Number -->
        <div style="text-align:center; margin:30px 0;">
            <h2 style="color:#ffd700; font-size:1.8em;">Your Order Number</h2>
            <h1 style="font-size:4em; color:#ff6b6b; letter-spacing:5px; margin:10px 0;">
                <strong><?= htmlspecialchars($order->order_no ?? 'ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT)) ?></strong>
            </h1>
            <p style="color:#ccc; font-size:1.1em;">Please remember or screenshot this number</p>
        </div>

        <div class="order-card" style="background:rgba(255,255,255,0.1); padding:20px; border-radius:12px; margin:20px 0;">
            <p><strong>Date & Time:</strong> <?= date('d M Y, h:i A', strtotime($order->date)) ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($order->customer_name) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order->customer_email) ?></p>

            <!-- Order Type Info -->
            <p><strong>Order Type:</strong> 
                <?= $order->order_type === 'dine_in' ? 'Dine In' : 'Take Away / Delivery' ?>
            </p>

            <?php if ($order->order_type === 'dine_in' && $order->table_number): ?>
                <p><strong>Table Number:</strong> <span style="font-size:1.5em; color:#ffd700;">No. <?= $order->table_number ?></span></p>
            <?php elseif ($order->order_type === 'take_away' && $order->delivery_address): ?>
                <p><strong>Delivery Address:</strong><br>
                    <?= nl2br(htmlspecialchars($order->delivery_address)) ?>
                </p>
            <?php endif; ?>
        </div>

        <h2 style="color:white; margin-top:30px;">Ordered Items</h2>

        <?php foreach ($items as $item): 
            $subtotal = $item->price * $item->unit;
        ?>
            <div class="menu-item">
                <img src="../product_photo/<?= htmlspecialchars($item->photo) ?>" alt="<?= htmlspecialchars($item->name) ?>">

                <div class="item-info">
                    <h2><?= htmlspecialchars($item->name) ?>
                        <span class="price-tag">RM <?= number_format($item->price, 2) ?></span>
                    </h2>
                    <p>Quantity: <strong><?= $item->unit ?></strong></p>
                    <p>Subtotal: <strong>RM <?= number_format($subtotal, 2) ?></strong></p>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Total Summary -->
        <div class="checkout-summary" style="margin-top:40px; padding:25px; background:rgba(255,255,255,0.15); border-radius:15px; text-align:center;">
            <h2 style="color:#ffd700; margin-bottom:15px;">
                Total Amount: <strong style="font-size:1.8em;">RM <?= number_format($order->total_price, 2) ?></strong>
            </h2>
            <p style="color:#ccc; margin:15px 0;">
                <?= $order->total_unit ?> item<?= $order->total_unit > 1 ? 's' : '' ?> in total
            </p>

            <div style="margin-top:25px;">
                <a href="../index.php" class="a-btn" style="padding:14px 30px; font-size:1.1em;">Back to Menu</a>
            </div>
        </div>

        <div style="text-align:center; margin-top:40px; color:#888; font-size:0.9em;">
            <p>Thank you for your order! ☕</p>
            <p>We will prepare your drink shortly.</p>
        </div>
    </div>
</div>

<?php include '../foot.php'; ?>