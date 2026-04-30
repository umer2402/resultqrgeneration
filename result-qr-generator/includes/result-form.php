<?php
$student = $student ?? default_result_values();
$formHeading = $formHeading ?? 'Add Student Result';
$submitLabel = $submitLabel ?? 'Save Result';
$formAction = $formAction ?? url('save-result.php');
$isEdit = !empty($student['id']);
?>
<div class="card border-0 shadow-sm form-card">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="section-title mb-1"><?= e($formHeading); ?></h2>
                <p class="text-muted mb-0">Enter the result details carefully. Marks and CGPA-style decimal values are both supported, and the percentage will be calculated automatically.</p>
            </div>
            <a class="btn btn-outline-secondary" href="<?= e(url('dashboard.php')); ?>">
                <i class="fa-solid fa-arrow-left me-1"></i>
                Back to Dashboard
            </a>
        </div>

        <form action="<?= e($formAction); ?>" method="post" novalidate>
            <?php if ($isEdit): ?>
                <input type="hidden" name="student_id" value="<?= e($student['id']); ?>">
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label" for="student_name">Student Name <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" id="student_name" name="student_name" value="<?= e($student['student_name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="father_name">Father Name <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" id="father_name" name="father_name" value="<?= e($student['father_name']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="roll_no">Roll No <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" id="roll_no" name="roll_no" value="<?= e($student['roll_no']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="registration_no">Registration No</label>
                    <input class="form-control" type="text" id="registration_no" name="registration_no" value="<?= e($student['registration_no']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="session">Session</label>
                    <input class="form-control" type="text" id="session" name="session" value="<?= e($student['session']); ?>" placeholder="2023 - 2027">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="program">Program</label>
                    <input class="form-control" type="text" id="program" name="program" value="<?= e($student['program']); ?>" placeholder="BS Computer Science">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="department">Department</label>
                    <input class="form-control" type="text" id="department" name="department" value="<?= e($student['department']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="semester">Semester</label>
                    <input class="form-control" type="text" id="semester" name="semester" value="<?= e($student['semester']); ?>" placeholder="Semester 4">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="exam_title">Exam Title</label>
                    <input class="form-control" type="text" id="exam_title" name="exam_title" value="<?= e($student['exam_title']); ?>" placeholder="Final Term Examination 2026">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="total_marks">Total Marks / CGPA <span class="text-danger">*</span></label>
                    <input class="form-control percentage-source" type="number" min="0.01" step="0.01" id="total_marks" name="total_marks" value="<?= e($student['total_marks']); ?>" placeholder="100 or 4.00" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="obtained_marks">Obtained Marks / CGPA <span class="text-danger">*</span></label>
                    <input class="form-control percentage-source" type="number" min="0" step="0.01" id="obtained_marks" name="obtained_marks" value="<?= e($student['obtained_marks']); ?>" placeholder="85 or 3.25" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="percentage">Percentage</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="percentage" name="percentage" value="<?= e($student['percentage']); ?>" readonly>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="grade">Grade</label>
                    <input class="form-control" type="text" id="grade" name="grade" value="<?= e($student['grade']); ?>" placeholder="A, B+, C">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="result_status">Result Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="result_status" name="result_status" required>
                        <?php
                        $statusOptions = ['Pass', 'Fail', 'Pending', 'On Hold'];
                        foreach ($statusOptions as $statusOption):
                        ?>
                            <option value="<?= e($statusOption); ?>" <?= strcasecmp((string) $student['result_status'], $statusOption) === 0 ? 'selected' : ''; ?>>
                                <?= e($statusOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 mt-4 pt-2">
                <button class="btn btn-primary btn-lg" type="submit">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    <?= e($submitLabel); ?>
                </button>
                <a class="btn btn-light btn-lg" href="<?= e(url('dashboard.php')); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>
