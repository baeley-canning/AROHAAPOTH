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

function fulfillment_label($status)
{
    $status = strtolower(trim((string)$status));
    $labels = [
        'pending' => 'Pending',
        'packing' => 'Packing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
    ];
    if ($status === '') {
        $status = 'pending';
    }
    return $labels[$status] ?? ucfirst($status);
}

function fulfillment_class($status)
{
    $status = strtolower(trim((string)$status));
    $allowed = ['pending', 'packing', 'shipped', 'delivered'];
    if (!in_array($status, $allowed, true)) {
        $status = 'pending';
    }
    return $status;
}

$orders = [];
$dbReady = (bool)$pdo;
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC');
    $orders = $stmt->fetchAll();
}

render_header('Orders');
?>
<div class="admin-header">
    <h2>Orders</h2>
    <span class="admin-muted">Stripe payments appear here after checkout completes.</span>
</div>

<?php if (!$dbReady): ?>
    <p class="admin-muted">Database not configured. Update config.php and run /admin/install.php.</p>
<?php endif; ?>

<div class="admin-card">
    <?php if (!$orders): ?>
        <p class="admin-muted">No orders yet.</p>
    <?php else: ?>
    <table class="admin-table">
        <thead>
        <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Fulfillment</th>
            <th>Total</th>
            <th>Items</th>
            <th>Date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <?php
            $itemsStmt = $pdo->prepare('SELECT product_name, quantity FROM order_items WHERE order_id = ?');
            $itemsStmt->execute([$order['id']]);
            $items = $itemsStmt->fetchAll();
            $itemText = [];
            foreach ($items as $item) {
                $itemText[] = $item['product_name'] . ' x ' . $item['quantity'];
            }
            ?>
            <tr>
                <td>
                    <a class="admin-link" href="/admin/order.php?ref=<?php echo urlencode($order['order_ref']); ?>">
                        <?php echo htmlspecialchars($order['order_ref']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($order['email'] ?? 'Pending'); ?></td>
                <td>
                    <span class="admin-status <?php echo status_class($order['status'] ?? 'pending'); ?>">
                        <?php echo htmlspecialchars(status_label($order['status'] ?? 'pending')); ?>
                    </span>
                </td>
                <td>
                    <span class="admin-status <?php echo fulfillment_class($order['fulfillment_status'] ?? 'pending'); ?>">
                        <?php echo htmlspecialchars(fulfillment_label($order['fulfillment_status'] ?? 'pending')); ?>
                    </span>
                </td>
                <td>
                    <?php
                    $total = isset($order['amount_total']) ? (int)$order['amount_total'] : 0;
                    echo '$' . number_format($total / 100, 2);
                    ?>
                </td>
                <td><?php echo htmlspecialchars(implode(', ', $itemText)); ?></td>
                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                <td>
                    <a class="admin-button" href="/admin/order.php?ref=<?php echo urlencode($order['order_ref']); ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php
render_footer();
