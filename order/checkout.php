<?php
require '../base.php';
include '../head.php';

$_err = [];

$cart_items   = [];
$total_price  = 0;
$total_unit   = 0;

if (empty($_SESSION['cart'])) {
    flash('info', 'Your cart is empty!');
    redirect('../cart.php');
}

$ids = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$stmt = $_db->prepare("SELECT * FROM product WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_OBJ);

foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p->id];
    $subtotal = $p->price * $qty;
    $total_price += $subtotal;
    $total_unit  += $qty;

    $cart_items[] = (object)[
        'product'  => $p,
        'qty'      => $qty,
        'subtotal' => $subtotal
    ];
}


if (is_post() && isset($_POST['checkout'])) {

    $name       = trim(post('name'));
    $email      = trim(post('email'));
    $order_type = post('order_type');           // dine_in or take_away
    $table_no   = $order_type === 'dine_in'  ? (int)post('table_number') : null;
    $address    = $order_type === 'take_away' ? trim(post('address')) : null;

    // ====================
    if (!$name) {
        $_err['name'] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $_err['name'] = 'Name too long (max 100 characters)';
    }

    if (!$email) {
        $_err['email'] = 'Email is required';
    } elseif (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    } elseif (strlen($email) > 100) {
        $_err['email'] = 'Email too long';
    }

    if ($order_type === 'dine_in') {
        if ($table_no < 1 || $table_no > 10) {
            $_err['table_number'] = 'Please select a table (1–10)';
        }
    } elseif ($order_type === 'take_away') {
        if (empty($address)) {
            $_err['address'] = 'Delivery address is required';
        }
    }

    // ====================
    if (empty($_err)) {
        try {
            $_db->beginTransaction();

            $stmt = $_db->prepare("
                INSERT INTO `order` 
                (date, customer_name, customer_email, order_type, table_number, delivery_address, total_unit, total_price)
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $email,
                $order_type,
                $table_no,
                $address,
                $total_unit,
                $total_price
            ]);

            $order_id = $_db->lastInsertId();

            $order_no = 'ORD' . $order_id;

            $_db->prepare("UPDATE `order` SET order_no = ? WHERE id = ?")
                ->execute([$order_no, $order_id]);

            $stmt = $_db->prepare("
                INSERT INTO order_detail (order_id, product_id, unit, price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($cart_items as $item) {
                $stmt->execute([
                    $order_id,
                    $item->product->id,
                    $item->qty,
                    $item->product->price,
                    $item->subtotal
                ]);
            }

            $_db->commit();

            set_cart();

            temp('info', "Order placed successfully! Your Order No: <strong>$order_no</strong>");
            redirect("detail.php?id=$order_id");

        } catch (Exception $e) {
            $_db->rollBack();
            flash('error', 'Order failed, please try again.');
            error_log("Checkout error: " . $e->getMessage()); 
        }
    } else {
        flash('error', 'Please correct the errors below');
    }
}
?>

<div class="container">

    <?php if ($msg = flash('success')): ?>
        <div class="alert success"><?= $msg ?></div>
    <?php elseif ($msg = flash('error')): ?>
        <div class="alert error"><?= $msg ?></div>
    <?php elseif ($msg = flash('info')): ?>
        <div class="alert info"><?= $msg ?></div>
    <?php endif; ?>

    <!-- LEFT SIDEBAR -->
    <div class="sidebar">
        <div class="logo-card">
            <div class="logo-icon">☕</div>
            <h1>ChaaGee Cafe</h1>
        </div>
        <a href="../index.php" class="menu-btn">☕ Drink Menu</a>
        <a href="../cart.php" class="menu-btn">🛒 My Cart</a>
        <a href="history.php" class="menu-btn">🧾 My Order</a>
    </div>

    <!-- RIGHT CONTENT -->
    <div class="content">
        <h1 style="color:white;">Checkout</h1>
        <hr>

        <!-- Order Summary -->
        <h2 style="color:white;">Order Summary</h2>
        <?php foreach ($cart_items as $item): ?>
            <div class="menu-item">
                <img src="../product_photo/<?= htmlspecialchars($item->product->photo) ?>">
                <div class="item-info">
                    <h2><?= htmlspecialchars($item->product->name) ?>
                        <span class="price-tag">RM <?= number_format($item->product->price, 2) ?></span>
                    </h2>
                    <p>Quantity: <?= $item->qty ?></p>
                    <p>Subtotal: RM <?= number_format($item->subtotal, 2) ?></p>
                </div>
            </div>
        <?php endforeach; ?>

        <h2 style="margin-top:20px; color:white;">
            Total: <strong>RM <?= number_format($total_price, 2) ?></strong>
            <small style="display:block; font-size:0.7em; opacity:0.8;">
                (<?= $total_unit ?> item<?= $total_unit > 1 ? 's' : '' ?>)
            </small>
        </h2>

        <hr style="border-color:#555; margin:30px 0;">

        <!-- Guest Details Form -->
        <h2 style="color:white;">Guest Details</h2>
        <form method="post" class="checkout-form">

            <!-- Order Type -->
            <div class="form-group">
                <label style="color:white; font-size:1.1em;"><strong>Order Type</strong></label><br>
                <label><input type="radio" name="order_type" value="dine_in" required onchange="toggleType()"> Dine In (Eat here)</label><br>
                <label><input type="radio" name="order_type" value="take_away" onchange="toggleType()"> Take Away / Delivery</label>
            </div>

            <!-- Name & Email -->
            <div class="form-group">
                <label>Name <span class="req">*</span></label>
                <?= html_name('name', 'maxlength="100" required') ?>
                <?= err('name') ?>
            </div>

            <div class="form-group">
                <label>Email (Gmail) <span class="req">*</span></label>
                <?= html_email('email', 'maxlength="100" required') ?>
                <?= err('email') ?>
            </div>

            <!-- Dine In: Table Number -->
            <div id="dine_in_box" style="display:none;">
                <div class="form-group">
                    <label>Table Number <span class="req">*</span></label>
                    <select name="table_number" class="form-select">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <option value="<?= $i ?>">Table No. <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <?= err('table_number') ?>
                </div>
            </div>

            <!-- Take Away: Delivery Address -->
            <div id="take_away_box" style="display:none;">
                <div class="form-group">
                    <label>Delivery Address <span class="req">*</span></label>
                    <textarea name="address" rows="3" placeholder="e.g. TAR UMT Hostel Block B, Room 412" class="form-control"></textarea>
                    <?= err('address') ?>
                </div>
            </div>

            <button type="submit" name="checkout" class="s-a-btn" style="padding:15px 30px; font-size:20px; margin-top:20px;">
                Complete Purchase
            </button>
        </form>
    </div>
</div>

<script>
function toggleType() {
    const type = document.querySelector('input[name="order_type"]:checked')?.value;
    document.getElementById('dine_in_box').style.display   = type === 'dine_in'   ? 'block' : 'none';
    document.getElementById('take_away_box').style.display = type === 'take_away' ? 'block' : 'none';
}

document.querySelector('input[value="dine_in"]').checked = true;
toggleType();
</script>

<?php include '../foot.php'; ?>