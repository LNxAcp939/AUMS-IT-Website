<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'buyer';
    
    if (in_array($mode, ['buyer', 'seller'])) {
        $_SESSION['active_mode'] = $mode;
        echo json_encode(['success' => true, 'mode' => $mode]);
        exit();
    }
}

echo json_encode(['success' => false]);
?>
