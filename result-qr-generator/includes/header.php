<?php
require_once __DIR__ . '/../config/db.php';

$pageTitle = $pageTitle ?? APP_NAME;
$pageSubtitle = $pageSubtitle ?? 'Manage student results and QR verification links in one place.';
$layout = $layout ?? 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle); ?> | <?= e(APP_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')); ?>">
</head>
<body class="<?= e($layout); ?>-layout">
<?php if ($layout === 'admin'): ?>
    <div class="admin-shell">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div class="admin-content">
            <header class="topbar">
                <div>
                    <p class="eyebrow mb-2">Admin Panel</p>
                    <h1 class="page-title mb-1"><?= e($pageTitle); ?></h1>
                    <p class="page-subtitle mb-0"><?= e($pageSubtitle); ?></p>
                </div>
                <div class="topbar-actions">
                    <span class="admin-chip">
                        <i class="fa-solid fa-user-shield"></i>
                        <?= e(admin_name()); ?>
                    </span>
                    <a class="btn btn-danger btn-sm" href="<?= e(url('logout.php')); ?>">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>
                        Logout
                    </a>
                </div>
            </header>
            <main class="container-fluid pb-4">
<?php elseif ($layout === 'auth'): ?>
    <div class="auth-page">
        <main class="container py-5">
<?php else: ?>
    <div class="public-page">
        <main class="container py-4">
<?php endif; ?>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show app-alert shadow-sm" role="alert">
        <?= e($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
