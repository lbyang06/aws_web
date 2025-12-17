<?php
require 'base.php';
//-----------------------------------------------------------------------------
include 'head.php';

if (is_post() && isset($_POST['add_to_cart'])) {
    $id = $_POST['product_id'];
    $id  = (int)$_POST['product_id'];
    $qty = (int)($_POST['menu_qty_'.$id] ?? 1);

    if ($qty < 1) { $qty = 1; }
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
    temp('info', 'Item added to cart');
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$cat = $_GET['cat'] ?? 'c001';
$coffee = $_db->query("SELECT * FROM product WHERE category_id = '$cat'");
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
    <button class="menu-btn active">☕ Drink Menu</button>

    <?php
    $cart_count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $cart_count += (int)$qty;
        }
    }
    ?>
    <a href="cart.php" class="menu-btn">
        🛒 My Cart 
        <?php if ($cart_count > 0): ?>
            <span class="cart-badge"><?= $cart_count ?></span>
        <?php endif; ?>
    </a>

    <a href="/order/history.php" class="menu-btn">🧾 My Order</a>
  </div>

  <!-- RIGHT SECTION -->
  <div class="content">
    <!-- TABS -->
    <div class="tabs">
        <a href="index.php?cat=c001" class="tab <?= ($cat=='c001' ? 'active' : '') ?>">Iced Coffee</a>
        <a href="index.php?cat=c002" class="tab <?= ($cat=='c002' ? 'active' : '') ?>">Hot Coffee</a>
    </div>

    <!-- ITEM LIST -->
    <?php foreach ($coffee as $c): ?>
        <div class="menu-item">
            <img src="product_photo/<?= $c->photo?>" alt="">
            <div class="item-info">
                <h2>
                    <?= $c->name ?>
                    <span class="price-tag">RM <?= $c->price ?></span>
                </h2>
                <p><?= $c->description ?></p>
            </div>
            <div class="a-t-cart">
                <form method="post">
                    <?= html_hidden('product_id', $c->id)?>
                    <section>
                        <button type="button" class="qty-minus qty-btn">-</button>
                        <input type="number"
                            name="menu_qty<?= $c->id ?>"
                            value="1"
                            min="1"
                            max="20">
                        <button type="button" class="qty-plus qty-btn">+</button>
                        <button class="a-btn" name="add_to_cart">Add to cart</button>
                    </section>
                </form>
            </div>
        </div>
    <?php endforeach ?>
  </div>
</div>

<script>
document.querySelectorAll('.menu-item').forEach(item => {
    let qtyInput = item.querySelector('input[name="menu_qty"]');
    let btnPlus = item.querySelector('.qty-plus');
    let btnMinus = item.querySelector('.qty-minus');
    // Ensure default value is 1
    if (!qtyInput.value) qtyInput.value = 1;
    btnPlus.addEventListener('click', () => {
        let current = parseInt(qtyInput.value, 10) || 1;
        if (current < 100) qtyInput.value = current + 1;
    });
    btnMinus.addEventListener('click', () => {
        let current = parseInt(qtyInput.value, 10) || 1;
        if (current > 1) qtyInput.value = current - 1;
    });
});
</script>

<?php
include 'foot.php';
?>
