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
define('DB_NAME', 'result_qr_generator');
define('DB_USER', 'root');
define('DB_PASS', '');

/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
| Set this when deploying to cPanel, for example:
| https://yourdomain.com/result-qr-generator
| Leave it empty on localhost to auto-detect from the current request.
*/
define('APP_BASE_URL', '');

function pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        exit('Database connection failed. Please update config/db.php with the correct credentials.');
    }

    return $pdo;
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

function normalize_result_payload(array $input): array
{
    $totalMarks = filter_var($input['total_marks'] ?? null, FILTER_VALIDATE_INT);
    $obtainedMarks = filter_var($input['obtained_marks'] ?? null, FILTER_VALIDATE_INT);

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
        $errors[] = 'Total marks must be a valid number greater than zero.';
    }

    if ($data['obtained_marks'] === null || $data['obtained_marks'] < 0) {
        $errors[] = 'Obtained marks must be a valid non-negative number.';
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
