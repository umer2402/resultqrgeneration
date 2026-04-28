<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errorMessage = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        $statement = pdo()->prepare('SELECT id, name, email, password FROM admins WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $admin = $statement->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];

            set_flash('Welcome back, ' . $admin['name'] . '!', 'success');
            redirect('dashboard.php');
        }

        $errorMessage = 'Invalid email or password.';
    }
}

$pageTitle = 'Admin Login';
$pageSubtitle = 'Secure access to manage result records and QR verification links.';
$layout = 'auth';

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="auth-panel">
            <section class="auth-hero">
                <p class="eyebrow text-white-50 mb-3">Result Verification System</p>
                <h2 class="mb-3">Manage student results and share verified records with one QR scan.</h2>
                <p class="mb-0 text-white-50">This lightweight Core PHP application lets administrators create result records, generate QR codes, and give students or verifiers a trusted public verification page.</p>

                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        <div>
                            <h6 class="mb-1">Secure Admin Access</h6>
                            <p class="mb-0 text-white-50">Passwords are verified using PHP password hashing and admin sessions protect the dashboard.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-file-circle-check"></i>
                        <div>
                            <h6 class="mb-1">Professional Result Records</h6>
                            <p class="mb-0 text-white-50">Store marks, grades, result status, and academic details in MySQL with PDO prepared statements.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-qrcode"></i>
                        <div>
                            <h6 class="mb-1">Instant QR Verification</h6>
                            <p class="mb-0 text-white-50">Each student gets a secure token that opens a dedicated public verification page.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="auth-card">
                <div class="mb-4">
                    <p class="eyebrow mb-2">Admin Portal</p>
                    <h1 class="page-title mb-2">Sign in</h1>
                    <p class="text-muted mb-0">Use the default admin account after importing the SQL file.</p>
                </div>

                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= e($errorMessage); ?></div>
                <?php endif; ?>

                <form method="post" action="" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input class="form-control" type="email" name="email" id="email" placeholder="admin@example.com" value="<?= e($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input class="form-control" type="password" name="password" id="password" placeholder="Enter password" required>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 btn-lg" type="submit">
                        <i class="fa-solid fa-right-to-bracket me-1"></i>
                        Login
                    </button>
                </form>

                <div class="mt-4 p-3 rounded-4 bg-light border">
                    <p class="mb-1 fw-semibold">Default Admin</p>
                    <p class="mb-0 text-muted">Email: <strong>admin@example.com</strong> | Password: <strong>admin123</strong></p>
                </div>
            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
