<?php
header('Content-Type: application/json; charset=utf-8');

// ---- Edit these 4 lines with the details from cPanel > MySQL Databases ----
$DB_HOST = 'localhost';
$DB_NAME = 'chess_club';
$DB_USER = 'root';
$DB_PASS = 'root';
// ----------------------------------------------------------------------------

function fail(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail(405, 'Invalid request.');
}

// Clean and limit every input
function field(string $key, int $max): string {
    return mb_substr(trim($_POST[$key] ?? ''), 0, $max);
}

$club      = field('club', 50);
$full_name = field('full_name', 120);
$reg_no    = field('reg_no', 50);
$phone     = field('phone', 30);
$email     = field('email', 120);
$course    = field('course', 120);
$year      = field('year', 20);
$level     = field('level', 30);

// Only accept known values
$allowedClubs  = ['chess'];
$allowedLevels = ['Beginner', 'Intermediate', 'Advanced'];
$allowedDays   = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

if (!in_array($club, $allowedClubs, true))   fail(422, 'Unknown club.');
if (!in_array($level, $allowedLevels, true)) fail(422, 'Select your playing level.');
if (count(preg_split('/\s+/', $full_name)) < 2) fail(422, 'Enter your first and last name.');
if (strlen($reg_no) < 3 || $course === '' || $year === '') fail(422, 'Please fill in all required fields.');
if (!preg_match('/^[+\d][\d\s\-]{6,}$/', $phone)) fail(422, 'Enter a valid phone number.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   fail(422, 'Enter a valid email address.');
if (empty($_POST['consent'])) fail(422, 'Please agree to be contacted.');

$days = [];
if (isset($_POST['days']) && is_array($_POST['days'])) {
    $days = array_values(array_intersect($allowedDays, $_POST['days']));
}
$daysText = implode(', ', $days);

// Save to MySQL using a prepared statement (blocks SQL injection)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $db->set_charset('utf8mb4');

    $stmt = $db->prepare(
        'INSERT INTO registrations (club, full_name, reg_no, phone, email, course, year, level, days)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssssssss', $club, $full_name, $reg_no, $phone, $email, $course, $year, $level, $daysText);
    $stmt->execute();

    echo json_encode(['ok' => true]);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        fail(409, 'This registration number is already registered for this club.');
    }
    error_log('Registration error: ' . $e->getMessage());
    fail(500, 'Something went wrong on our side. Please try again later.');
}
