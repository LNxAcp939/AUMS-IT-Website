<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$active_mode = getActiveMode();

if ($active_mode === 'seller') {
    include __DIR__ . '/seller/seller_dashboard.php';
} else {
    include __DIR__ . '/buyer/buyer_dashboard.php';
}
?>
