<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

require_login();

function status_label($status)
{
    $status = strtolower(trim((string)$status));
    $labels = [
        'paid' => 'Paid',
        'pending' => 'Pending',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'refunding' => 'Refunding',
        'expired' => 'Expired',
    ];
    if ($status === '') {
        $status = 'pending';
    }
    return $labels[$status] ?? ucfirst($status);
}

function status_class($status)
{
    $status = strtolower(trim((string)$status));
    $allowed = ['paid', 'pending', 'failed', 'refunded', 'refunding', 'expired'];
    if (!in_array($status, $allowed, true)) {
        $status = 'pending';
    }
    return $status;
}

function format_money($amount, $currency)
{
    $amount = (int)$amount;
    $currency = strtoupper((string)$currency);
    $symbol = $currency === 'NZD' ? 'NZ$' : '$';
    return $symbol . number_format($amount / 100, 2);
}

$ref = trim($_GET['ref'] ?? '');
$order = null;
$items = [];
$dbReady = (bool)$pdo;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && $ref !== '') {
    $status = strtolower(trim($_POST['fulfillment_status'] ?? ''));
    $allowed = ['pending', 'packing', 'shipped', 'delivered'];
    if (!in_array($status, $allowed, true)) {
        $status = 'pending';
    }
    $trackingUrl = trim($_POST['tracking_url'] ?? '');
    $trackingUrl = $trackingUrl !== '' ? $trackingUrl : null;

    $stmt = $pdo->prepare('UPDATE orders SET fulfillment_status = ?, tracking_url = ? WHERE order_ref = ?');
    $stmt->execute([$status, $trackingUrl, $ref]);
    $message = 'Fulfillment status updated.';
}

if ($pdo && $ref !== '') {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_ref = ?');
    $stmt->execute([$ref]);
    $order = $stmt->fetch();

    if ($order) {
        $itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$order['id']]);
        $items = $itemsStmt->fetchAll();
    }
}

render_header($order ? ('Order ' . $order['order_ref']) : 'Order details');
?>
<div class="admin-header">
    <div>
        <h2><?php echo $order ? ('Order ' . htmlspecialchars($order['order_ref'])) : 'Order details'; ?></h2>
        <span class="admin-muted">Order sheets show payment status, customer info, and items.</span>
    </div>
    <a class="admin-button" href="/admin/orders.php">Back to orders</a>
</div>

<?php if ($message): ?>
    <p class="admin-muted"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if (!$dbReady): ?>
    <p class="admin-muted">Database not configured. Update config.php and run /admin/install.php.</p>
<?php elseif (!$order): ?>
    <div class="admin-card">
        <p class="admin-muted">Order not found. Check the link and try again.</p>
    </div>
<?php else: ?>
    <div class="admin-card admin-detail-grid">
        <div class="admin-detail">
            <h4>Status</h4>
            <span class="admin-status <?php echo status_class($order['status'] ?? 'pending'); ?>">
                <?php echo htmlspecialchars(status_label($order['status'] ?? 'pending')); ?>
            </span>
            <?php if (!empty($order['payment_status'])): ?>
                <div class="admin-muted" style="margin-top: 6px;">Payment status: <?php echo htmlspecialchars($order['payment_status']); ?></div>
            <?php endif; ?>
            <?php if (!empty($order['refund_status'])): ?>
                <div class="admin-muted">Refund status: <?php echo htmlspecialchars($order['refund_status']); ?></div>
            <?php endif; ?>
        </div>
        <div class="admin-detail">
            <h4>Total</h4>
            <div><?php echo format_money($order['amount_total'] ?? 0, $order['currency'] ?? 'NZD'); ?></div>
            <?php if (!empty($order['amount_refunded'])): ?>
                <div class="admin-muted">Refunded: <?php echo format_money($order['amount_refunded'], $order['currency'] ?? 'NZD'); ?></div>
            <?php endif; ?>
        </div>
        <div class="admin-detail">
            <h4>Customer</h4>
            <div><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></div>
            <div class="admin-muted"><?php echo htmlspecialchars($order['email'] ?? ''); ?></div>
            <?php if (!empty($order['customer_phone'])): ?>
                <div class="admin-muted"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
            <?php endif; ?>
        </div>
        <div class="admin-detail">
            <h4>Placed</h4>
            <div><?php echo htmlspecialchars($order['created_at']); ?></div>
            <?php if (!empty($order['updated_at'])): ?>
                <div class="admin-muted">Updated: <?php echo htmlspecialchars($order['updated_at']); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card" style="margin-top: 16px;">
        <h3 style="margin-top: 0;">Fulfillment</h3>
        <form method="post" class="admin-form" style="margin-top: 12px;">
            <label>Order stage</label>
            <div class="admin-pill-group">
                <?php
                $currentStatus = strtolower($order['fulfillment_status'] ?? 'pending');
                $options = ['pending' => 'Pending', 'packing' => 'Packing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
                foreach ($options as $value => $label):
                    $inputId = 'fulfillment-' . $value;
                ?>
                    <input
                        class="admin-pill-input"
                        type="radio"
                        id="<?php echo $inputId; ?>"
                        name="fulfillment_status"
                        value="<?php echo $value; ?>"
                        <?php echo $currentStatus === $value ? 'checked' : ''; ?>
                    >
                    <label class="admin-pill-label" for="<?php echo $inputId; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <label>Tracking link (optional)</label>
            <input type="url" name="tracking_url" value="<?php echo htmlspecialchars($order['tracking_url'] ?? ''); ?>" placeholder="https://...">
            <span class="admin-muted">If you have a courier tracking link, paste it here.</span>

            <div style="margin-top: 16px;">
                <button class="admin-button primary" type="submit">Save fulfillment status</button>
            </div>
        </form>
    </div>

    <div class="admin-card" style="margin-top: 16px;">
        <h3 style="margin-top: 0;">Items</h3>
        <?php if (!$items): ?>
            <p class="admin-muted">No items recorded for this order.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Line Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name'] ?? $item['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo format_money($item['unit_amount'] ?? 0, $order['currency'] ?? 'NZD'); ?></td>
                        <td><?php echo format_money($item['line_total'] ?? 0, $order['currency'] ?? 'NZD'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="admin-card" style="margin-top: 16px;">
        <h3 style="margin-top: 0;">Shipping</h3>
        <?php if (!empty($order['shipping_address'])): ?>
            <div class="admin-muted"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
        <?php else: ?>
            <p class="admin-muted">No shipping address captured yet.</p>
        <?php endif; ?>
        <?php if (!empty($order['tracking_url'])): ?>
            <p class="admin-muted" style="margin-top: 12px;">
                Tracking: <a class="admin-link" href="<?php echo htmlspecialchars($order['tracking_url']); ?>" target="_blank" rel="noopener">Open tracking</a>
            </p>
        <?php endif; ?>
    </div>

    <div class="admin-card" style="margin-top: 16px;">
        <h3 style="margin-top: 0;">Stripe</h3>
        <div class="admin-muted">Session: <?php echo htmlspecialchars($order['stripe_session_id'] ?? ''); ?></div>
        <div class="admin-muted">Payment intent: <?php echo htmlspecialchars($order['stripe_payment_intent_id'] ?? ''); ?></div>
    </div>
<?php endif; ?>
<?php
render_footer();
