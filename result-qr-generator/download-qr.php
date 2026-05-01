<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_login();

$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$studentId) {
    set_flash('Invalid student record selected for QR download.', 'danger');
    redirect('dashboard.php');
}

$student = db_fetch_one('SELECT id, roll_no, qr_token, qr_image FROM students_results WHERE id = ? LIMIT 1', 'i', [$studentId]);

if (!$student) {
    set_flash('Student result not found.', 'danger');
    redirect('dashboard.php');
}

if (empty($student['qr_token'])) {
    set_flash('QR code is not available for this record yet.', 'warning');
    redirect('generate-qr.php?id=' . $studentId);
}

$downloadFilename = sanitize_filename($student['roll_no']) . '_qrcode.png';

if (!empty($student['qr_image'])) {
    $localQrFile = asset_path($student['qr_image']);

    if (is_file($localQrFile)) {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
        header('Content-Length: ' . (string) filesize($localQrFile));
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        readfile($localQrFile);
        exit;
    }
}

$publicUrl = public_result_url($student['qr_token']);
$remoteImageData = fetch_remote_file(qr_api_url($publicUrl));

if ($remoteImageData === false) {
    set_flash('Unable to download the QR code right now. Please try again.', 'danger');
    redirect('generate-qr.php?id=' . $studentId);
}

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
header('Content-Length: ' . (string) strlen($remoteImageData));
header('Cache-Control: private, no-transform, no-store, must-revalidate');
echo $remoteImageData;
exit;
