<?php $currentPage = $currentPage ?? basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fa-solid fa-qrcode"></i>
        </div>
        <div>
            <h2><?= e(APP_NAME); ?></h2>
            <p><?= e(APP_INSTITUTE); ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="<?= e(url('dashboard.php')); ?>">
            <i class="fa-solid fa-gauge-high"></i>
            Dashboard
        </a>
        <a class="sidebar-link <?= in_array($currentPage, ['add-result.php', 'edit-result.php', 'save-result.php'], true) ? 'active' : ''; ?>" href="<?= e(url('add-result.php')); ?>">
            <i class="fa-solid fa-file-circle-plus"></i>
            Add Result
        </a>
        <a class="sidebar-link" href="<?= e(url('logout.php')); ?>">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Logout
        </a>
    </nav>

    <div class="sidebar-card">
        <span class="badge text-bg-light mb-2">QR Verification</span>
        <p class="mb-0">Create result records, generate QR codes, and verify them publicly with a secure token.</p>
    </div>
</aside>
