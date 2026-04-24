<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Check if column exists before adding
    $stmt = $conn->query("SHOW COLUMNS FROM `users` LIKE 'is_blocked'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE `users` ADD COLUMN `is_blocked` BOOLEAN DEFAULT FALSE AFTER `is_admin`");
        echo "Column 'is_blocked' added successfully.\n";
    } else {
        echo "Column 'is_blocked' already exists.\n";
    }

    // Make the first user an admin if there are no admins
    $stmt = $conn->query("SELECT id FROM users WHERE is_admin = 1");
    if ($stmt->rowCount() == 0) {
        $conn->exec("UPDATE users SET is_admin = 1 ORDER BY id ASC LIMIT 1");
        echo "First user has been made an admin.\n";
    } else {
        echo "An admin user already exists.\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
