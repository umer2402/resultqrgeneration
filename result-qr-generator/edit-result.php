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

$oldFormData = get_old_form_data();
if ($oldFormData !== []) {
    $student = array_merge($student, $oldFormData);
}

$pageTitle = 'Edit Result';
$pageSubtitle = 'Update student result details, then regenerate the QR code if needed.';
$layout = 'admin';
$formHeading = 'Edit Student Result';
$submitLabel = 'Update Result & Refresh QR';
$formAction = url('save-result.php');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/result-form.php';
include __DIR__ . '/includes/footer.php';
