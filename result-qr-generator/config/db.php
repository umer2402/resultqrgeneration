<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Karachi');

define('APP_NAME', 'Result QR Generator');
define('APP_INSTITUTE', 'Thal University');

/*
|--------------------------------------------------------------------------
| Database Credentials
|--------------------------------------------------------------------------
| Update these values to match your local XAMPP or hosting database.
*/
define('DB_HOST', 'localhost');
define('DB_NAME', 'thalahos_resultsDB');
define('DB_USER', 'thalahos_resultsDB');
define('DB_PASS', 'IvL,4H=wmDpXRYB9');
define('DB_PORT', 3306);

/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
| Set this when deploying to cPanel, for example:
| https://yourdomain.com/result-qr-generator
| Leave it empty on localhost to auto-detect from the current request.
*/
define('APP_BASE_URL', 'https://cricstars11.com/resultsqr/result-qr-generator/');
define('APP_DEBUG', false);

function db(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_OFF);

    $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($connection->connect_errno) {
        error_log('Result QR Generator DB connection failed: ' . $connection->connect_error);

        if (APP_DEBUG) {
            exit('Database connection failed: ' . e($connection->connect_error));
        }

        exit('Database connection failed. Please update config/db.php with the correct credentials.');
    }

    if (!$connection->set_charset('utf8mb4')) {
        error_log('Result QR Generator charset setup failed: ' . $connection->error);
    }

    return $connection;
}

function db_prepare(string $sql): mysqli_stmt
{
    $statement = db()->prepare($sql);

    if (!$statement instanceof mysqli_stmt) {
        error_log('Result QR Generator statement prepare failed: ' . db()->error . ' | SQL: ' . $sql);

        if (APP_DEBUG) {
            exit('Database query preparation failed: ' . e(db()->error));
        }

        exit('Database query preparation failed.');
    }

    return $statement;
}

function db_bind_params(mysqli_stmt $statement, string $types = '', array $params = []): void
{
    if ($types === '' || $params === []) {
        return;
    }

    $bindParams = [$types];

    foreach (array_keys($params) as $index) {
        $bindParams[] = &$params[$index];
    }

    if (!call_user_func_array([$statement, 'bind_param'], $bindParams)) {
        error_log('Result QR Generator parameter bind failed: ' . $statement->error);

        if (APP_DEBUG) {
            exit('Database parameter binding failed: ' . e($statement->error));
        }

        exit('Database parameter binding failed.');
    }
}

function db_execute_statement(mysqli_stmt $statement): void
{
    if (!$statement->execute()) {
        error_log('Result QR Generator statement execution failed: ' . $statement->error);

        if (APP_DEBUG) {
            exit('Database query execution failed: ' . e($statement->error));
        }

        exit('Database query execution failed.');
    }
}

function db_fetch_all_from_statement(mysqli_stmt $statement): array
{
    $metadata = $statement->result_metadata();

    if ($metadata === false) {
        return [];
    }

    $fields = $metadata->fetch_fields();
    $metadata->free();

    $row = [];
    $boundColumns = [];

    foreach ($fields as $field) {
        $row[$field->name] = null;
        $boundColumns[] = &$row[$field->name];
    }

    call_user_func_array([$statement, 'bind_result'], $boundColumns);

    $results = [];

    while ($statement->fetch()) {
        $currentRow = [];

        foreach ($row as $key => $value) {
            $currentRow[$key] = $value;
        }

        $results[] = $currentRow;
    }

    $statement->free_result();

    return $results;
}

function db_fetch_all(string $sql, string $types = '', array $params = []): array
{
    $statement = db_prepare($sql);
    db_bind_params($statement, $types, $params);
    db_execute_statement($statement);
    $rows = db_fetch_all_from_statement($statement);
    $statement->close();

    return $rows;
}

function db_fetch_one(string $sql, string $types = '', array $params = []): ?array
{
    $rows = db_fetch_all($sql, $types, $params);

    return $rows[0] ?? null;
}

function db_fetch_value(string $sql, string $types = '', array $params = []): mixed
{
    $row = db_fetch_one($sql, $types, $params);

    if ($row === null) {
        return null;
    }

    return reset($row);
}

function db_execute(string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $statement = db_prepare($sql);
    db_bind_params($statement, $types, $params);
    db_execute_statement($statement);

    return $statement;
}

function db_last_insert_id(): int
{
    return (int) db()->insert_id;
}

function app_base_url(): string
{
    if (APP_BASE_URL !== '') {
        return rtrim(APP_BASE_URL, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = str_replace('\\', '/', dirname($scriptName));

    if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
        $basePath = '';
    }

    return rtrim($scheme . '://' . $host . $basePath, '/');
}

function url(string $path = ''): string
{
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    $path = ltrim($path, '/');
    $base = app_base_url();

    return $path === '' ? $base : $base . '/' . $path;
}

function asset_path(string $relativePath): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $flash;
}

function set_old_form_data(array $data): void
{
    $_SESSION['old_form_data'] = $data;
}

function get_old_form_data(): array
{
    if (!isset($_SESSION['old_form_data']) || !is_array($_SESSION['old_form_data'])) {
        return [];
    }

    $data = $_SESSION['old_form_data'];
    unset($_SESSION['old_form_data']);

    return $data;
}

function clear_old_form_data(): void
{
    unset($_SESSION['old_form_data']);
}

function is_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('Please log in to continue.', 'warning');
        redirect('login.php');
    }
}

function admin_name(): string
{
    return $_SESSION['admin_name'] ?? 'Administrator';
}

function format_datetime(?string $value): string
{
    if (empty($value)) {
        return 'N/A';
    }

    return date('d M Y, h:i A', strtotime($value));
}

function result_status_badge_class(?string $status): string
{
    return match (strtolower(trim((string) $status))) {
        'pass' => 'success',
        'fail' => 'danger',
        'pending' => 'warning text-dark',
        default => 'primary',
    };
}

function calculate_percentage(float $obtainedMarks, float $totalMarks): float
{
    if ($totalMarks <= 0) {
        return 0.00;
    }

    return round(($obtainedMarks / $totalMarks) * 100, 2);
}

function sanitize_filename(string $value): string
{
    $sanitized = preg_replace('/[^A-Za-z0-9_-]+/', '_', $value);
    $sanitized = trim((string) $sanitized, '_');

    return $sanitized !== '' ? $sanitized : 'result';
}

function generate_qr_token(): string
{
    return bin2hex(random_bytes(16));
}

function public_result_url(string $token): string
{
    return url('result.php?token=' . urlencode($token));
}

function fetch_remote_file(string $fileUrl): string|false
{
    if (function_exists('curl_init')) {
        $ch = curl_init($fileUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['User-Agent: ResultQRGenerator/1.0'],
        ]);

        $data = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($data !== false && $httpCode >= 200 && $httpCode < 300) ? $data : false;
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 20,
            'header' => "User-Agent: ResultQRGenerator/1.0\r\n",
        ],
    ]);

    return @file_get_contents($fileUrl, false, $context);
}

function create_qr_image(string $publicUrl, string $relativeFilePath): bool
{
    $apiUrl = 'https://quickchart.io/qr?size=400&format=png&margin=2&text=' . urlencode($publicUrl);
    $imageData = fetch_remote_file($apiUrl);

    if ($imageData === false) {
        return false;
    }

    $absolutePath = asset_path($relativeFilePath);
    $directory = dirname($absolutePath);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return file_put_contents($absolutePath, $imageData) !== false;
}

function normalize_decimal_value(mixed $value): ?float
{
    if ($value === null) {
        return null;
    }

    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    if (!is_numeric($value)) {
        return null;
    }

    return round((float) $value, 2);
}

function normalize_result_payload(array $input): array
{
    $totalMarks = normalize_decimal_value($input['total_marks'] ?? null);
    $obtainedMarks = normalize_decimal_value($input['obtained_marks'] ?? null);

    return [
        'student_name' => trim((string) ($input['student_name'] ?? '')),
        'father_name' => trim((string) ($input['father_name'] ?? '')),
        'roll_no' => trim((string) ($input['roll_no'] ?? '')),
        'registration_no' => trim((string) ($input['registration_no'] ?? '')),
        'program' => trim((string) ($input['program'] ?? '')),
        'department' => trim((string) ($input['department'] ?? '')),
        'session' => trim((string) ($input['session'] ?? '')),
        'semester' => trim((string) ($input['semester'] ?? '')),
        'exam_title' => trim((string) ($input['exam_title'] ?? '')),
        'total_marks' => $totalMarks === false ? null : $totalMarks,
        'obtained_marks' => $obtainedMarks === false ? null : $obtainedMarks,
        'grade' => trim((string) ($input['grade'] ?? '')),
        'result_status' => trim((string) ($input['result_status'] ?? '')),
    ];
}

function validate_result_payload(array $data): array
{
    $errors = [];

    if ($data['student_name'] === '') {
        $errors[] = 'Student name is required.';
    }

    if ($data['father_name'] === '') {
        $errors[] = 'Father name is required.';
    }

    if ($data['roll_no'] === '') {
        $errors[] = 'Roll number is required.';
    }

    if ($data['total_marks'] === null || $data['total_marks'] <= 0) {
        $errors[] = 'Total marks or total CGPA must be a valid number greater than zero.';
    }

    if ($data['obtained_marks'] === null || $data['obtained_marks'] < 0) {
        $errors[] = 'Obtained marks or CGPA must be a valid non-negative number.';
    }

    if (
        $data['total_marks'] !== null
        && $data['obtained_marks'] !== null
        && $data['obtained_marks'] > $data['total_marks']
    ) {
        $errors[] = 'Obtained marks cannot be greater than total marks.';
    }

    if ($data['result_status'] === '') {
        $errors[] = 'Result status is required.';
    }

    return $errors;
}

function default_result_values(): array
{
    return [
        'id' => '',
        'student_name' => '',
        'father_name' => '',
        'roll_no' => '',
        'registration_no' => '',
        'program' => '',
        'department' => '',
        'session' => '',
        'semester' => '',
        'exam_title' => '',
        'total_marks' => '',
        'obtained_marks' => '',
        'percentage' => '',
        'grade' => '',
        'result_status' => 'Pass',
        'qr_token' => '',
        'qr_image' => '',
        'created_at' => '',
    ];
}
