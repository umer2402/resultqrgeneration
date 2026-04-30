<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$studentId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$studentId) {
    set_flash('Invalid student record selected for deletion.', 'danger');
    redirect('dashboard.php');
}

$student = db_fetch_one('SELECT qr_image FROM students_results WHERE id = ? LIMIT 1', 'i', [$studentId]);

if (!$student) {
    set_flash('Student result not found.', 'danger');
    redirect('dashboard.php');
}

if (!empty($student['qr_image'])) {
    $qrFile = asset_path($student['qr_image']);
    if (file_exists($qrFile)) {
        unlink($qrFile);
    }
}

$deleteStatement = db_execute('DELETE FROM students_results WHERE id = ?', 'i', [$studentId]);
$deleteStatement->close();

set_flash('Student result deleted successfully.', 'success');
redirect('dashboard.php');
