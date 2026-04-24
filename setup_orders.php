<?php
require_once __DIR__ . '/includes/db.php';

try {
    // 1. Add seller_id to orders
    $stmt = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'seller_id'");
    if ($stmt->rowCount() == 0) {
        // Delete all previous orders because they are multi-vendor and will break the new constraint
        $conn->exec("DELETE FROM orders"); 
        
        $conn->exec("ALTER TABLE `orders` ADD COLUMN `seller_id` INT NOT NULL AFTER `user_id`");
        $conn->exec("ALTER TABLE `orders` ADD FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
        echo "orders table updated successfully.<br>";
    } else {
        echo "orders table already updated.<br>";
    }

    // 2. Ensure categories exist
    $stmt = $conn->query("SELECT COUNT(*) FROM categories");
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $conn->exec("INSERT INTO categories (name, icon) VALUES 
            ('Electronics', 'fas fa-laptop'),
            ('Books', 'fas fa-book'),
            ('Clothing', 'fas fa-tshirt'),
            ('Services', 'fas fa-hands-helping'),
            ('Others', 'fas fa-box')
        ");
        echo "Categories populated.<br>";
    } else {
        echo "Categories already exist.<br>";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
