<?php
require_once __DIR__ . '/includes/db.php';
$conn->exec("ALTER TABLE orders MODIFY COLUMN status ENUM('Pending', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending'");
echo "DONE";
?>
