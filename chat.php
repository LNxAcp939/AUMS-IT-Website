<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'];
$mode = getActiveMode();

// ==========================================
// 1. AJAX FETCH MESSAGES (Background Polling)
// ==========================================
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['product_id']) && isset($_GET['other_user_id'])) {
    $product_id = $_GET['product_id'];
    $other_user_id = $_GET['other_user_id'];

    $stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE product_id = ? 
        AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        ORDER BY created_at ASC
    ");
    $stmt->execute([$product_id, $user_id, $other_user_id, $other_user_id, $user_id]);
    $messages = $stmt->fetchAll();

    if (empty($messages)) {
        echo '<div class="text-center text-secondary-custom my-auto"><i class="fas fa-hand-sparkles fa-2x mb-2 text-cyan opacity-50"></i><p>Say hello to start the conversation!</p></div>';
        exit;
    }

    foreach ($messages as $msg) {
        $is_mine = ($msg['sender_id'] == $user_id);
        ?>
        <div class="d-flex w-100 <?= $is_mine ? 'justify-content-end' : 'justify-content-start' ?>">
            <div class="p-3" style="
                max-width: 75%; 
                border-radius: 16px; 
                <?= $is_mine ? 'background: var(--electric-blue); color: #fff; border-bottom-right-radius: 4px;' : 'background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--border-color); border-bottom-left-radius: 4px;' ?>
            ">
                <p class="mb-1" style="word-wrap: break-word;"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                <div class="text-end" style="font-size: 0.7rem; opacity: 0.7;">
                    <?= date('h:i A', strtotime($msg['created_at'])) ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Mark messages as read when fetched via AJAX
    $stmt_read = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND product_id = ? AND is_read = 0");
    $stmt_read->execute([$user_id, $other_user_id, $product_id]);
    
    exit; // Stop execution here for AJAX request
}

// ==========================================
// 2. HANDLE NEW MESSAGE SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['product_id']) && isset($_POST['other_user_id'])) {
    $message = trim($_POST['message']);
    $product_id = $_POST['product_id'];
    $other_user_id = $_POST['other_user_id'];
    
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $other_user_id, $product_id, $message]);
    }
    
    // Redirect to avoid form resubmission
    $url = "/ums-marketplace/chat.php?product_id=" . $product_id;
    if ($mode === 'seller' && isset($_POST['buyer_id'])) {
        $url .= "&buyer_id=" . $_POST['buyer_id'];
    }
    header("Location: " . $url);
    exit();
}

// ==========================================
// 3. RENDER THE APPROPRIATE UI
// ==========================================
include __DIR__ . '/includes/header.php';

// If a specific chat thread is requested
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    // Determine participants
    if ($mode === 'buyer') {
        $stmt = $conn->prepare("SELECT seller_id, name as product_name FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $prod = $stmt->fetch();
        
        if (!$prod || $prod['seller_id'] == $user_id) {
            header("Location: /ums-marketplace/chat.php");
            exit();
        }
        
        $other_user_id = $prod['seller_id'];
        $buyer_id_for_form = $user_id;
    } else {
        if (!isset($_GET['buyer_id'])) {
            header("Location: /ums-marketplace/chat.php");
            exit();
        }
        
        $other_user_id = $_GET['buyer_id'];
        $buyer_id_for_form = $other_user_id;
        
        $stmt = $conn->prepare("SELECT name as product_name FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $user_id]);
        $prod = $stmt->fetch();
        
        if (!$prod) {
            header("Location: /ums-marketplace/chat.php");
            exit();
        }
    }
    
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$other_user_id]);
    $other_user = $stmt->fetch();
    ?>
    
    <div class="container py-4 animate-fade-in" style="height: calc(100vh - 150px); display: flex; flex-direction: column;">
        <!-- Chat Header -->
        <div class="glass-card p-3 mb-3 d-flex align-items-center gap-3" style="border-radius: 16px 16px 0 0;">
            <a href="/ums-marketplace/chat.php" class="btn btn-sm btn-outline-custom rounded-circle" style="width: 35px; height: 35px; padding: 0; line-height: 31px;"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h5 class="mb-0 text-primary"><?= htmlspecialchars($other_user['name']) ?></h5>
                <small class="text-cyan"><i class="fas fa-box me-1"></i> <?= htmlspecialchars($prod['product_name']) ?></small>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div class="glass-card flex-grow-1 p-3 p-md-4 overflow-auto d-flex flex-column gap-3 mb-3" id="chat-box" style="border-radius: 0; border-top: none; border-bottom: none;">
            <div class="text-center text-secondary-custom">
                <div class="spinner-border spinner-border-sm text-cyan" role="status"></div> Loading messages...
            </div>
        </div>

        <!-- Chat Input Area -->
        <div class="glass-card p-3" style="border-radius: 0 0 16px 16px;">
            <form method="POST" action="" class="d-flex gap-2">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                <input type="hidden" name="other_user_id" value="<?= htmlspecialchars($other_user_id) ?>">
                <?php if ($mode === 'seller'): ?>
                    <input type="hidden" name="buyer_id" value="<?= htmlspecialchars($buyer_id_for_form) ?>">
                <?php endif; ?>
                <input type="text" name="message" class="form-control form-control-custom rounded-pill px-4" placeholder="Type your message..." required autocomplete="off">
                <button type="submit" class="btn btn-primary-custom rounded-circle" style="width: 45px; height: 45px; padding: 0;"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBox = document.getElementById('chat-box');
        const productId = <?= json_encode($product_id) ?>;
        const otherUserId = <?= json_encode($other_user_id) ?>;
        
        function fetchMessages() {
            fetch(`/ums-marketplace/chat.php?ajax=1&product_id=${productId}&other_user_id=${otherUserId}`)
                .then(response => response.text())
                .then(html => {
                    const wasAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 50;
                    chatBox.innerHTML = html;
                    if (wasAtBottom) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                });
        }

        fetchMessages();
        setInterval(fetchMessages, 3000);
    });
    </script>
    
    <?php
} 
// Else, render the Inbox list
else {
    ?>
    <div class="container py-4 animate-fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary mb-0"><i class="fas fa-inbox text-cyan me-2"></i><?= ucfirst($mode) ?> Inbox</h2>
        </div>

        <div class="glass-card p-4">
            <?php
            if ($mode === 'buyer') {
                $stmt = $conn->prepare("
                    SELECT m.product_id, MAX(m.created_at) as last_msg_time, p.name as product_name, p.image_url, p.seller_id, u.name as other_name
                    FROM messages m
                    JOIN products p ON m.product_id = p.id
                    JOIN users u ON p.seller_id = u.id
                    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND p.seller_id != ?
                    GROUP BY m.product_id, p.name, p.image_url, p.seller_id, u.name
                    ORDER BY last_msg_time DESC
                ");
                $stmt->execute([$user_id, $user_id, $user_id]);
            } else {
                $stmt = $conn->prepare("
                    SELECT 
                        m.product_id, 
                        MAX(m.created_at) as last_msg_time, 
                        p.name as product_name, 
                        p.image_url,
                        CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as buyer_id,
                        u.name as other_name
                    FROM messages m
                    JOIN products p ON m.product_id = p.id
                    JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
                    WHERE p.seller_id = ?
                    GROUP BY m.product_id, p.name, p.image_url, buyer_id, u.name
                    ORDER BY last_msg_time DESC
                ");
                $stmt->execute([$user_id, $user_id, $user_id]);
            }
            
            $conversations = $stmt->fetchAll();
            
            if(empty($conversations)): ?>
                <div class="text-center py-5 text-secondary-custom">
                    <i class="fas fa-comments fa-3x mb-3 text-cyan opacity-50"></i>
                    <p class="fs-5">You have no active conversations.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush" style="background: transparent;">
                    <?php foreach($conversations as $conv): ?>
                        <?php 
                        $link = "/ums-marketplace/chat.php?product_id=" . $conv['product_id'];
                        if ($mode === 'seller') {
                            $link .= "&buyer_id=" . $conv['buyer_id'];
                        }
                        ?>
                        <a href="<?= $link ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-secondary" style="background: rgba(255,255,255,0.02); margin-bottom: 10px; border-radius: 12px !important;">
                            <?php if($conv['image_url']): ?>
                                <img src="<?= htmlspecialchars($conv['image_url']) ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">
                            <?php else: ?>
                                <div class="bg-dark text-secondary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 10px; border: 1px solid var(--border-color);">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 text-primary fw-bold text-truncate"><?= htmlspecialchars($conv['product_name']) ?></h6>
                                    <small class="text-secondary-custom whitespace-nowrap"><?= date('d M, h:i A', strtotime($conv['last_msg_time'])) ?></small>
                                </div>
                                <p class="mb-0 text-cyan small"><i class="fas <?= $mode === 'buyer' ? 'fa-store' : 'fa-user' ?> me-1"></i> <?= htmlspecialchars($conv['other_name']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

include __DIR__ . '/includes/footer.php';
?>
