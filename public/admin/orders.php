<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

require_login();

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
            <th>Total</th>
            <th>Items</th>
            <th>Date</th>
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
                <td><?php echo htmlspecialchars($order['order_ref']); ?></td>
                <td><?php echo htmlspecialchars($order['email'] ?? 'Pending'); ?></td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <?php
                    $total = isset($order['amount_total']) ? (int)$order['amount_total'] : 0;
                    echo '$' . number_format($total / 100, 2);
                    ?>
                </td>
                <td><?php echo htmlspecialchars(implode(', ', $itemText)); ?></td>
                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php
render_footer();
