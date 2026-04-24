<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Check if product_id column exists
    $stmt = $conn->query("SHOW COLUMNS FROM `messages` LIKE 'product_id'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE `messages` ADD COLUMN `product_id` INT NULL AFTER `receiver_id`");
        $conn->exec("ALTER TABLE `messages` ADD FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE");
        echo "Database updated successfully for the Chat System.\n";
    } else {
        echo "Database is already up to date.\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
