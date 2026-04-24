<?php
require_once __DIR__ . '/includes/db.php';
$stmt = $conn->query("DESCRIBE orders");
$cols = $stmt->fetchAll();
print_r($cols);
?>
