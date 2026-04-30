<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$data = normalize_result_payload($_POST);
$errors = validate_result_payload($data);
$percentage = calculate_percentage((float) ($data['obtained_marks'] ?? 0), (float) ($data['total_marks'] ?? 0));
$data['percentage'] = number_format($percentage, 2, '.', '');

$redirectTarget = $studentId ? 'edit-result.php?id=' . $studentId : 'add-result.php';

if ($errors !== []) {
    set_old_form_data(array_merge($data, ['id' => $studentId ?: '']));
    set_flash(implode(' ', $errors), 'danger');
    redirect($redirectTarget);
}

$duplicateQuery = 'SELECT id FROM students_results WHERE roll_no = ?';
$duplicateTypes = 's';
$duplicateParams = [$data['roll_no']];

if ($studentId) {
    $duplicateQuery .= ' AND id != ?';
    $duplicateTypes .= 'i';
    $duplicateParams[] = $studentId;
}

if (db_fetch_one($duplicateQuery, $duplicateTypes, $duplicateParams)) {
    set_old_form_data(array_merge($data, ['id' => $studentId ?: '']));
    set_flash('This roll number already exists. Please use a unique roll number.', 'danger');
    redirect($redirectTarget);
}

if ($studentId) {
    $existingStudent = db_fetch_one('SELECT id, qr_token FROM students_results WHERE id = ? LIMIT 1', 'i', [$studentId]);

    if (!$existingStudent) {
        set_flash('Student result not found for updating.', 'danger');
        redirect('dashboard.php');
    }

    $qrToken = $existingStudent['qr_token'] ?: generate_qr_token();

    $updateStatement = db_execute(
        'UPDATE students_results SET
            student_name = ?,
            father_name = ?,
            roll_no = ?,
            registration_no = ?,
            program = ?,
            department = ?,
            session = ?,
            semester = ?,
            exam_title = ?,
            total_marks = ?,
            obtained_marks = ?,
            percentage = ?,
            grade = ?,
            result_status = ?,
            qr_token = ?
         WHERE id = ?',
        'sssssssssiissssi',
        [
            $data['student_name'],
            $data['father_name'],
            $data['roll_no'],
            $data['registration_no'],
            $data['program'],
            $data['department'],
            $data['session'],
            $data['semester'],
            $data['exam_title'],
            $data['total_marks'],
            $data['obtained_marks'],
            $data['percentage'],
            $data['grade'],
            $data['result_status'],
            $qrToken,
            $studentId,
        ]
    );
    $updateStatement->close();

    clear_old_form_data();
    set_flash('Result updated successfully.', 'success');
    redirect('generate-qr.php?id=' . $studentId . '&regenerate=1');
}

$qrToken = generate_qr_token();
$qrImage = null;

$insertStatement = db_execute(
    'INSERT INTO students_results (
        student_name,
        father_name,
        roll_no,
        registration_no,
        program,
        department,
        session,
        semester,
        exam_title,
        total_marks,
        obtained_marks,
        percentage,
        grade,
        result_status,
        qr_token,
        qr_image
    ) VALUES (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )',
    'sssssssssiisssss',
    [
        $data['student_name'],
        $data['father_name'],
        $data['roll_no'],
        $data['registration_no'],
        $data['program'],
        $data['department'],
        $data['session'],
        $data['semester'],
        $data['exam_title'],
        $data['total_marks'],
        $data['obtained_marks'],
        $data['percentage'],
        $data['grade'],
        $data['result_status'],
        $qrToken,
        $qrImage,
    ]
);
$insertStatement->close();

$newStudentId = db_last_insert_id();

clear_old_form_data();
set_flash('Result saved successfully. QR code generated below.', 'success');
redirect('generate-qr.php?id=' . $newStudentId);
