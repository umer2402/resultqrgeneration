<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_login();

$student = array_merge(default_result_values(), get_old_form_data());

$pageTitle = 'Add Result';
$pageSubtitle = 'Create a new student result record and generate a unique verification QR code.';
$layout = 'admin';
$formHeading = 'Add Student Result';
$submitLabel = 'Save Result & Generate QR';
$formAction = url('save-result.php');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/result-form.php';
include __DIR__ . '/includes/footer.php';
