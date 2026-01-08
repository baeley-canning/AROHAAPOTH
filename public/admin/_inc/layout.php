<?php
function render_header($title)
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($title); ?></title>
        <?php $cssVersion = file_exists(__DIR__ . '/../admin.css') ? filemtime(__DIR__ . '/../admin.css') : time(); ?>
        <link rel="stylesheet" href="/admin/admin.css?v=<?php echo $cssVersion; ?>">
    </head>
    <body>
        <div class="admin-wrap">
            <div class="admin-nav">
                <a href="/">Home</a>
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/products.php">Products</a>
                <a href="/admin/orders.php">Orders</a>
                <a href="/admin/email.php">Email</a>
                <a href="/admin/logout.php">Logout</a>
            </div>
    <?php
}

function render_footer()
{
    ?>
        </div>
    </body>
    </html>
    <?php
}
