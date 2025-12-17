<?php
require 'base.php';
include 'head.php';

$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += (int)$qty;
    }
}

if (is_post() && isset($_POST['update_cart'])) {
    $id = (int)$_POST['product_id'];
    $qty = (int)$_POST['qty'];
    if ($qty < 1) $qty = 1;

    if ($qty == 0) {
        unset($_SESSION['cart'][$id]);
        temp('info', 'Item removed from cart');
    } else {
        $_SESSION['cart'][$id] = $qty;
        temp('info', 'Cart updated');
    }
    header("Location: cart.php");
    exit;
}

if (is_post() && isset($_POST['remove_id'])) {
    $rid = (int)$_POST['remove_id'];
    unset($_SESSION['cart'][$rid]);
    temp('info', 'Item removed');
    header("Location: cart.php");
    exit;
}

$cart_items = [];
$total_price = 0;
$total_unit = 0; 

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $_db->prepare("SELECT * FROM product WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_OBJ);

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p->id];
        $subtotal = $p->price * $qty;
        $total_price += $subtotal;
        $total_unit += $qty;

        $cart_items[] = [
            'product' => $p,
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>

<style>
    .cart-badge {
        display: inline-block;
        min-width: 18px;
        height: 18px;
        background-color: #ff4444;
        color: white;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        line-height: 18px;
        border-radius: 50%;
        margin-left: 8px;
        padding: 0 5px;
        vertical-align: middle;
    }
</style>

<div class="container">

    <!-- LEFT MENU -->
    <div class="sidebar">
        <div class="logo-card">
            <div class="logo-icon">☕</div>
            <h1>HardRock Cafe</h1>
        </div>

        <a href="index.php" class="menu-btn">☕ Drink Menu</a>

        <a href="cart.php" class="menu-btn active">
            🛒 My Cart 
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>

        <a href="order/history.php" class="menu-btn">🧾 My Order</a>
    </div>

    <!-- RIGHT CONTENT -->
    <div class="content">

        <h1 style="color: white; margin-bottom: 10px;">My Cart</h1>
        <hr style="border-color:#555; margin-bottom:30px;">

        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 60px 20px; color: white;">
                <h3>Your cart is empty</h3>
                <p>Let's add some delicious drinks!</p>
                <a href="index.php" class="a-btn" style="margin-top: 20px; display: inline-block;">← Back to Menu</a>
            </div>
        <?php else: ?>
            <?php foreach ($cart_items as $item): 
                $p = $item['product'];
                $qty = $item['qty'];
                $subtotal = $item['subtotal'];
            ?>
                <div class="menu-item">
                    <img src="product_photo/<?= htmlspecialchars($p->photo) ?>" alt="<?= htmlspecialchars($p->name) ?>">

                    <div class="item-info">
                        <h2>
                            <?= htmlspecialchars($p->name) ?>
                            <span class="price-tag">RM <?= number_format($p->price, 2) ?></span>
                        </h2>

                        <!-- Update Quantity Form -->
                        <form method="post" class="qty-form" style="margin: 12px 0;">
                            <input type="hidden" name="product_id" value="<?= $p->id ?>">
                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <button type="button" class="qty-minus qty-btn">-</button>
                                <input type="number" name="qty" value="<?= $qty ?>" min="1" max="100" 
                                       style="width:60px; text-align:center; padding:6px;">
                                <button type="button" class="qty-plus qty-btn">+</button>
                                <button type="submit" name="update_cart" class="a-btn" style="font-size:0.9em;">Update</button>
                            </div>
                        </form>

                        <p style="margin: 10px 0; color: #ddd;">
                            <strong>Subtotal: RM <?= number_format($subtotal, 2) ?></strong>
                        </p>
                    </div>

                    <!-- Remove Button -->
                    <div style="align-self: center; margin-left: auto; margin-right: 15px;">
                        <form method="post" onsubmit="return confirm('Remove this item?');">
                            <input type="hidden" name="remove_id" value="<?= $p->id ?>">
                            <button class="a-btn" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Cart Footer -->
            <div class="cart-footer">
                <div style="font-size: 1.5em; color: white; margin-bottom: 15px;">
                    <strong>TOTAL: RM <?= number_format($total_price, 2) ?></strong>
                </div>

                <a href="order/checkout.php" class="a-btn" style="font-size: 1.2em; padding: 14px 30px;">Checkout</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    document.querySelectorAll('.menu-item').forEach(item => {
        const qtyInput = item.querySelector('input[name="qty"]');
        const btnPlus = item.querySelector('.qty-plus');
        const btnMinus = item.querySelector('.qty-minus');

        if (!qtyInput || !btnPlus || !btnMinus) return;

        btnPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val < 100) qtyInput.value = val + 1;
        });

        btnMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });
    });
</script>

<?php include 'foot.php'; ?>