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

$duplicateQuery = 'SELECT id FROM students_results WHERE roll_no = :roll_no';
$duplicateParams = ['roll_no' => $data['roll_no']];

if ($studentId) {
    $duplicateQuery .= ' AND id != :id';
    $duplicateParams['id'] = $studentId;
}

$duplicateStatement = pdo()->prepare($duplicateQuery);
$duplicateStatement->execute($duplicateParams);

if ($duplicateStatement->fetch()) {
    set_old_form_data(array_merge($data, ['id' => $studentId ?: '']));
    set_flash('This roll number already exists. Please use a unique roll number.', 'danger');
    redirect($redirectTarget);
}

if ($studentId) {
    $existingStatement = pdo()->prepare('SELECT id, qr_token FROM students_results WHERE id = :id LIMIT 1');
    $existingStatement->execute(['id' => $studentId]);
    $existingStudent = $existingStatement->fetch();

    if (!$existingStudent) {
        set_flash('Student result not found for updating.', 'danger');
        redirect('dashboard.php');
    }

    $qrToken = $existingStudent['qr_token'] ?: generate_qr_token();

    $updateStatement = pdo()->prepare(
        'UPDATE students_results SET
            student_name = :student_name,
            father_name = :father_name,
            roll_no = :roll_no,
            registration_no = :registration_no,
            program = :program,
            department = :department,
            session = :session,
            semester = :semester,
            exam_title = :exam_title,
            total_marks = :total_marks,
            obtained_marks = :obtained_marks,
            percentage = :percentage,
            grade = :grade,
            result_status = :result_status,
            qr_token = :qr_token
         WHERE id = :id'
    );

    $updateStatement->execute([
        'student_name' => $data['student_name'],
        'father_name' => $data['father_name'],
        'roll_no' => $data['roll_no'],
        'registration_no' => $data['registration_no'],
        'program' => $data['program'],
        'department' => $data['department'],
        'session' => $data['session'],
        'semester' => $data['semester'],
        'exam_title' => $data['exam_title'],
        'total_marks' => $data['total_marks'],
        'obtained_marks' => $data['obtained_marks'],
        'percentage' => $data['percentage'],
        'grade' => $data['grade'],
        'result_status' => $data['result_status'],
        'qr_token' => $qrToken,
        'id' => $studentId,
    ]);

    clear_old_form_data();
    set_flash('Result updated successfully.', 'success');
    redirect('generate-qr.php?id=' . $studentId . '&regenerate=1');
}

$insertStatement = pdo()->prepare(
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
        :student_name,
        :father_name,
        :roll_no,
        :registration_no,
        :program,
        :department,
        :session,
        :semester,
        :exam_title,
        :total_marks,
        :obtained_marks,
        :percentage,
        :grade,
        :result_status,
        :qr_token,
        :qr_image
    )'
);

$qrToken = generate_qr_token();

$insertStatement->execute([
    'student_name' => $data['student_name'],
    'father_name' => $data['father_name'],
    'roll_no' => $data['roll_no'],
    'registration_no' => $data['registration_no'],
    'program' => $data['program'],
    'department' => $data['department'],
    'session' => $data['session'],
    'semester' => $data['semester'],
    'exam_title' => $data['exam_title'],
    'total_marks' => $data['total_marks'],
    'obtained_marks' => $data['obtained_marks'],
    'percentage' => $data['percentage'],
    'grade' => $data['grade'],
    'result_status' => $data['result_status'],
    'qr_token' => $qrToken,
    'qr_image' => null,
]);

$newStudentId = (int) pdo()->lastInsertId();

clear_old_form_data();
set_flash('Result saved successfully. QR code generated below.', 'success');
redirect('generate-qr.php?id=' . $newStudentId);
