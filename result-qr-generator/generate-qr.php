<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_login();

$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$studentId) {
    set_flash('Invalid student record selected.', 'danger');
    redirect('dashboard.php');
}

$student = db_fetch_one('SELECT * FROM students_results WHERE id = ? LIMIT 1', 'i', [$studentId]);

if (!$student) {
    set_flash('Student result not found.', 'danger');
    redirect('dashboard.php');
}

if (empty($student['qr_token'])) {
    $student['qr_token'] = generate_qr_token();
    $tokenStatement = db_execute('UPDATE students_results SET qr_token = ? WHERE id = ?', 'si', [$student['qr_token'], $studentId]);
    $tokenStatement->close();
}

$desiredQrPath = 'assets/qr/' . sanitize_filename($student['roll_no']) . '_qrcode.png';
$publicUrl = public_result_url($student['qr_token']);
$remoteQrUrl = qr_api_url($publicUrl);
$shouldRegenerate = isset($_GET['regenerate'])
    || empty($student['qr_image'])
    || $student['qr_image'] !== $desiredQrPath
    || !file_exists(asset_path($student['qr_image'] ?: $desiredQrPath));

$qrMessage = '';
$qrError = '';
$qrImageUrl = $remoteQrUrl;
$qrDownloadUrl = $remoteQrUrl;

if ($shouldRegenerate) {
    $oldQrImage = $student['qr_image'];
    $generated = false;

    try {
        $generated = create_qr_image($publicUrl, $desiredQrPath);
    } catch (Throwable $throwable) {
        error_log('Result QR Generator QR creation failed: ' . $throwable->getMessage());
        $generated = false;
    }

    if ($generated) {
        $updateStatement = db_execute('UPDATE students_results SET qr_image = ? WHERE id = ?', 'si', [$desiredQrPath, $studentId]);
        $updateStatement->close();

        if (!empty($oldQrImage) && $oldQrImage !== $desiredQrPath) {
            $oldQrFile = asset_path($oldQrImage);
            if (file_exists($oldQrFile)) {
                unlink($oldQrFile);
            }
        }

        $student['qr_image'] = $desiredQrPath;
        $qrMessage = isset($_GET['regenerate'])
            ? 'QR code re-generated successfully.'
            : 'QR code generated successfully.';
    } else {
        $qrError = 'The QR image could not be saved on the server, so a live QR preview is shown instead. Please check folder permissions for assets/qr if you want local downloads saved.';
    }
}

if (!empty($student['qr_image']) && file_exists(asset_path($student['qr_image']))) {
    $qrImageUrl = url($student['qr_image']);
    $qrDownloadUrl = url($student['qr_image']);
}

$pageTitle = 'Generate QR Code';
$pageSubtitle = 'Download the QR image, open the public verification page, or regenerate the code after editing.';
$layout = 'admin';

include __DIR__ . '/includes/header.php';
?>
<?php if ($qrMessage !== ''): ?>
    <div class="alert alert-success border-0 shadow-sm"><?= e($qrMessage); ?></div>
<?php endif; ?>

<?php if ($qrError !== ''): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?= e($qrError); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card qr-card border-0 h-100">
            <div class="card-body p-4">
                <p class="eyebrow mb-2">Student Summary</p>
                <h2 class="section-title mb-3"><?= e($student['student_name']); ?></h2>

                <div class="result-meta">
                    <div class="meta-item">
                        <small>Roll No</small>
                        <strong><?= e($student['roll_no']); ?></strong>
                    </div>
                    <div class="meta-item">
                        <small>Program</small>
                        <strong><?= e($student['program'] ?: 'N/A'); ?></strong>
                    </div>
                    <div class="meta-item">
                        <small>Result Status</small>
                        <span class="badge text-bg-<?= e(result_status_badge_class($student['result_status'])); ?> result-pill"><?= e($student['result_status']); ?></span>
                    </div>
                    <div class="meta-item">
                        <small>Created On</small>
                        <strong><?= e(format_datetime($student['created_at'])); ?></strong>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label" for="public_result_url">Public Result URL</label>
                    <input class="form-control" type="text" id="public_result_url" value="<?= e($publicUrl); ?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card qr-card border-0 h-100">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <p class="eyebrow mb-2">QR Preview</p>
                        <h2 class="section-title mb-1">Result Verification QR Code</h2>
                        <p class="text-muted mb-0">Scan this QR code to open the public verification page for this student result.</p>
                    </div>
                    <a class="btn btn-outline-success" href="<?= e(url('generate-qr.php?id=' . $student['id'] . '&regenerate=1')); ?>">
                        <i class="fa-solid fa-rotate me-1"></i>
                        Re-generate QR
                    </a>
                </div>

                <div class="d-flex flex-column align-items-center text-center">
                    <div class="qr-preview mb-4">
                        <img src="<?= e($qrImageUrl); ?>" alt="QR Code for <?= e($student['roll_no']); ?>">
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a class="btn btn-primary" href="<?= e($qrDownloadUrl); ?>" target="_blank" download="<?= e(sanitize_filename($student['roll_no']) . '_qrcode.png'); ?>">
                            <i class="fa-solid fa-download me-1"></i>
                            Download QR Code
                        </a>
                        <a class="btn btn-outline-primary" href="<?= e($publicUrl); ?>" target="_blank">
                            <i class="fa-solid fa-eye me-1"></i>
                            View Result
                        </a>
                        <a class="btn btn-light" href="<?= e(url('dashboard.php')); ?>">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
