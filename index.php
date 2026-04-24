<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: /ums-marketplace/dashboard.php");
    exit();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="container py-5 text-center animate-fade-in">
    <div class="row py-lg-5">
        <div class="col-lg-8 col-md-10 mx-auto">
            <h1 class="fw-bold mb-4 display-4">Welcome to <span class="text-cyan">UMS Marketplace</span></h1>
            <p class="lead text-secondary-custom mb-5">The ultimate campus-based e-commerce platform. Buy from your peers, sell your pre-loved items, and discover unique services right here on campus.</p>
            <p>
                <a href="/ums-marketplace/auth/register.php" class="btn btn-primary-custom btn-lg me-3">Get Started Now</a>
                <a href="/ums-marketplace/auth/login.php" class="btn btn-outline-custom btn-lg">Login</a>
            </p>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="container py-5 animate-fade-in" style="animation-delay: 0.2s;">
    <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
        <div class="col d-flex align-items-start">
            <div class="icon-square text-cyan bg-dark flex-shrink-0 me-3 fs-3" style="width: 4rem; height: 4rem; border-radius: 1rem; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <h3 class="fs-4 text-primary">Seamless Role Switching</h3>
                <p class="text-secondary-custom">Switch instantly between Buyer and Seller modes without logging out. Manage your purchases and sales from one unified account.</p>
            </div>
        </div>
        <div class="col d-flex align-items-start">
            <div class="icon-square text-cyan bg-dark flex-shrink-0 me-3 fs-3" style="width: 4rem; height: 4rem; border-radius: 1rem; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-paint-brush"></i>
            </div>
            <div>
                <h3 class="fs-4 text-primary">Modern Dark Theme</h3>
                <p class="text-secondary-custom">Experience a sleek, eye-friendly interface featuring glassmorphism elements, custom glows, and smooth animations.</p>
            </div>
        </div>
        <div class="col d-flex align-items-start">
            <div class="icon-square text-cyan bg-dark flex-shrink-0 me-3 fs-3" style="width: 4rem; height: 4rem; border-radius: 1rem; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h3 class="fs-4 text-primary">Campus Trusted</h3>
                <p class="text-secondary-custom">A closed ecosystem for UMS students. Safe, secure, and designed specifically for our university community.</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
