<?php
require_once __DIR__ . '/includes/db.php';
$stmt = $conn->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $tables);
?>
