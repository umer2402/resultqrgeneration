<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_login();

$search = trim((string) ($_GET['search'] ?? ''));

$totalResults = (int) pdo()->query('SELECT COUNT(*) FROM students_results')->fetchColumn();
$passResults = (int) pdo()->query("SELECT COUNT(*) FROM students_results WHERE LOWER(result_status) = 'pass'")->fetchColumn();
$pendingResults = (int) pdo()->query("SELECT COUNT(*) FROM students_results WHERE LOWER(result_status) = 'pending'")->fetchColumn();

if ($search !== '') {
    $statement = pdo()->prepare(
        'SELECT * FROM students_results
         WHERE student_name LIKE :search OR roll_no LIKE :search
         ORDER BY created_at DESC
         LIMIT 20'
    );
    $statement->execute(['search' => '%' . $search . '%']);
} else {
    $statement = pdo()->prepare('SELECT * FROM students_results ORDER BY created_at DESC LIMIT 20');
    $statement->execute();
}

$students = $statement->fetchAll();

$pageTitle = 'Dashboard';
$pageSubtitle = 'Review recent result records, search students, and generate or update QR verification codes.';
$layout = 'admin';

include __DIR__ . '/includes/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stats-card border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2">Total Results</p>
                        <h3 class="mb-1"><?= e((string) $totalResults); ?></h3>
                        <p class="text-muted mb-0">All generated student result records.</p>
                    </div>
                    <div class="icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2">Passed Students</p>
                        <h3 class="mb-1"><?= e((string) $passResults); ?></h3>
                        <p class="text-muted mb-0">Results currently marked as pass.</p>
                    </div>
                    <div class="icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2">Pending Results</p>
                        <h3 class="mb-1"><?= e((string) $pendingResults); ?></h3>
                        <p class="text-muted mb-0">Results awaiting completion or publication.</p>
                    </div>
                    <div class="icon">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card surface-card border-0">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="section-title mb-1">Student Results</h2>
                <p class="text-muted mb-0">Search by student name or roll number, then view, edit, delete, or regenerate a QR code.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary" href="<?= e(url('add-result.php')); ?>">
                    <i class="fa-solid fa-plus me-1"></i>
                    Add New Result
                </a>
                <a class="btn btn-outline-danger" href="<?= e(url('logout.php')); ?>">
                    <i class="fa-solid fa-right-from-bracket me-1"></i>
                    Logout
                </a>
            </div>
        </div>

        <form class="row g-3 align-items-end search-box mb-4" method="get" action="">
            <div class="col-md-8">
                <label class="form-label" for="search">Search Student</label>
                <input class="form-control" type="text" id="search" name="search" value="<?= e($search); ?>" placeholder="Enter student name or roll number">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>
                    Search
                </button>
                <a class="btn btn-light flex-grow-1" href="<?= e(url('dashboard.php')); ?>">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Roll No</th>
                        <th>Program</th>
                        <th>Status</th>
                        <th>QR Code</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($students): ?>
                    <?php foreach ($students as $student): ?>
                        <?php
                        $publicUrl = public_result_url($student['qr_token']);
                        $hasQrImage = !empty($student['qr_image']) && file_exists(asset_path($student['qr_image']));
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($student['student_name']); ?></div>
                                <small class="text-muted"><?= e($student['department'] ?: 'Department not specified'); ?></small>
                            </td>
                            <td><?= e($student['roll_no']); ?></td>
                            <td><?= e($student['program'] ?: 'N/A'); ?></td>
                            <td>
                                <span class="badge result-pill text-bg-<?= e(result_status_badge_class($student['result_status'])); ?>">
                                    <?= e($student['result_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($hasQrImage): ?>
                                    <a href="<?= e(url('generate-qr.php?id=' . $student['id'])); ?>" title="View QR details">
                                        <img class="mini-qr" src="<?= e(url($student['qr_image'])); ?>" alt="QR Code for <?= e($student['roll_no']); ?>">
                                    </a>
                                <?php else: ?>
                                    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('generate-qr.php?id=' . $student['id'])); ?>">
                                        Generate
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?= e(format_datetime($student['created_at'])); ?></td>
                            <td class="text-end">
                                <div class="table-actions justify-content-end">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('result.php?token=' . urlencode($student['qr_token']))); ?>" target="_blank">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('edit-result.php?id=' . $student['id'])); ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-success" href="<?= e(url('generate-qr.php?id=' . $student['id'] . '&regenerate=1')); ?>">
                                        <i class="fa-solid fa-rotate"></i>
                                    </a>
                                    <form method="post" action="<?= e(url('delete-result.php')); ?>" data-confirm-delete>
                                        <input type="hidden" name="id" value="<?= e($student['id']); ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fa-solid fa-folder-open fa-2x mb-3"></i>
                                <h5 class="mb-2">No result records found</h5>
                                <p class="mb-0">Add a new result to begin generating QR verification codes.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
