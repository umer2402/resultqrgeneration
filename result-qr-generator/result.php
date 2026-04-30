<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$token = trim((string) ($_GET['token'] ?? ''));
$student = null;

if ($token !== '') {
    $student = db_fetch_one('SELECT * FROM students_results WHERE qr_token = ? LIMIT 1', 's', [$token]);
}

$pageTitle = 'Result Verification';
$pageSubtitle = 'Public student result verification';
$layout = 'public';

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card result-card border-0">
            <?php if ($student): ?>
                <div class="result-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <span class="university-badge mb-3">
                                <i class="fa-solid fa-building-columns"></i>
                                <?= e(APP_INSTITUTE); ?>
                            </span>
                            <h1 class="mb-2">Result Verification Page</h1>
                            <p class="mb-0 text-white-50">This result was verified using a secure QR token linked to the student record.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-light" type="button" onclick="window.print();">
                                <i class="fa-solid fa-print me-1"></i>
                                Print Result
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <p class="eyebrow mb-2">Verified Student Record</p>
                            <h2 class="section-title mb-1"><?= e($student['student_name']); ?></h2>
                            <p class="text-muted mb-0">Issued on <?= e(format_datetime($student['created_at'])); ?></p>
                        </div>
                        <span class="badge text-bg-<?= e(result_status_badge_class($student['result_status'])); ?> result-pill">
                            <?= e($student['result_status']); ?>
                        </span>
                    </div>

                    <div class="result-meta">
                        <div class="meta-item">
                            <small>Student Name</small>
                            <strong><?= e($student['student_name']); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Father Name</small>
                            <strong><?= e($student['father_name']); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Roll No</small>
                            <strong><?= e($student['roll_no']); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Registration No</small>
                            <strong><?= e($student['registration_no'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Program</small>
                            <strong><?= e($student['program'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Department</small>
                            <strong><?= e($student['department'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Session</small>
                            <strong><?= e($student['session'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Semester</small>
                            <strong><?= e($student['semester'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Exam Title</small>
                            <strong><?= e($student['exam_title'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Total Marks</small>
                            <strong><?= e($student['total_marks']); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Obtained Marks</small>
                            <strong><?= e($student['obtained_marks']); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Percentage</small>
                            <strong><?= e(number_format((float) $student['percentage'], 2)); ?>%</strong>
                        </div>
                        <div class="meta-item">
                            <small>Grade</small>
                            <strong><?= e($student['grade'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <small>Result Status</small>
                            <strong><?= e($student['result_status']); ?></strong>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="result-header">
                    <span class="university-badge mb-3">
                        <i class="fa-solid fa-building-columns"></i>
                        <?= e(APP_INSTITUTE); ?>
                    </span>
                    <h1 class="mb-2">Result Verification Page</h1>
                    <p class="mb-0 text-white-50">The requested QR verification record could not be located.</p>
                </div>
                <div class="card-body p-4 p-lg-5 text-center">
                    <div class="py-4">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-danger mb-3"></i>
                        <h2 class="section-title mb-2">Invalid or expired QR Code</h2>
                        <p class="text-muted mb-0">The token in the URL does not match any student result. Please scan a valid QR code or contact the institution.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
