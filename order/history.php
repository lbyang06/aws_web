<?php
require '../base.php';
include '../head.php';

$stmt = $_db->prepare("
    SELECT 
        id,
        date,
        customer_name,
        order_type,
        table_number,
        delivery_address,
        total_unit,
        total_price
    FROM `order`
    ORDER BY date DESC
");
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_OBJ);
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
        <h1 style="color:white; text-align:center; margin-bottom:10px;">Order History</h1>
        <p style="color:#ccc; text-align:center; margin-bottom:30px;">View all your past orders</p>
        <hr style="border-color:#555; margin-bottom:30px;">

        <?php if (empty($history)): ?>
            <div style="text-align:center; padding:80px 20px; color:white;">
                <h3>No orders yet</h3>
                <p>Looks like you haven't ordered anything yet.</p>
                <a href="../index.php" class="a-btn" style="margin-top:20px; display:inline-block; padding:12px 25px;">
                    ← Browse Menu & Order Now
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($history as $h): 
                $order_no = $h->order_id ?? 'ORD' . str_pad($h->id, 6, '0', STR_PAD_LEFT);
                $formatted_date = date('d M Y', strtotime($h->date));
                $formatted_time = date('h:i A', strtotime($h->date));
            ?>
                <a href="detail.php?id=<?= $h->id ?>" class="history-link">
                    <div class="history-card">

                        <!-- Order Number (Highlight) -->
                        <div class="history-order-no">
                            <div style="font-size:0.9em; color:#aaa;">Order No.</div>
                            <strong style="font-size:1.4em; color:#ffd700;">
                                <?= htmlspecialchars($order_id) ?>
                            </strong>
                        </div>

                        <!-- Date & Time -->
                        <div class="history-date">
                            <div style="font-size:0.9em; color:#aaa;">Date & Time</div>
                            <strong><?= $formatted_date ?><br><small><?= $formatted_time ?></small></strong>
                        </div>

                        <!-- Order Type + Info -->
                        <div class="history-type">
                            <div style="font-size:0.9em; color:#aaa;">Type</div>
                            <?php if ($h->order_type === 'dine_in'): ?>
                                <strong style="color:#4ade80;">Dine In</strong>
                                <?php if ($h->table_number): ?>
                                    <br><small>Table No. <strong style="color:#ffd700;"><?= $h->table_number ?></strong></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <strong style="color:#74c0fc;">Take Away</strong>
                                <?php if ($h->delivery_address): ?>
                                    <br><small style="word-break:break-word; max-width:180px; display:block;">
                                        <?= htmlspecialchars(truncate_text($h->delivery_address, 40)) ?>
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Quantity -->
                        <div class="history-qty">
                            <div style="font-size:0.9em; color:#aaa;">Items</div>
                            <strong>x <?= $h->total_unit ?></strong>
                        </div>

                        <!-- Total Price -->
                        <div class="history-price">
                            <div style="font-size:0.9em; color:#aaa;">Total</div>
                            <strong style="font-size:1.3em; color:#ff6b6b;">
                                RM <?= number_format($h->total_price, 2) ?>
                            </strong>
                        </div>

                        <!-- Arrow -->
                        <div class="history-arrow">
                            <span style="font-size:2em; color:#888;">›</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<?php
function truncate_text($text, $length = 40) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}
?>

<style>
.history-link {
    text-decoration: none;
    color: inherit;
    display: block;
    margin-bottom: 16px;
}
.history-card {
    background: rgba(255,255,255,0.08);
    border-radius: 15px;
    padding: 18px;
    display: grid;
    grid-template-columns: 1.2fr 1fr 1.3fr 0.8fr 1fr auto;
    gap: 15px;
    align-items: center;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.1);
}
.history-card:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
.history-arrow {
    text-align: right;
    color: #aaa;
}
@media (max-width: 900px) {
    .history-card {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 15px;
    }
    .history-arrow {
        grid-column: span 2;
        text-align: right;
    }
}
@media (max-width: 600px) {
    .history-card {
        grid-template-columns: 1fr;
        text-align: center;
    }
    .history-arrow {
        grid-column: 1;
    }
}
</style>

<?php include '../foot.php'; ?>

