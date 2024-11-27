<?php
function loadEnv($file) {
    if (!file_exists($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}
loadEnv(__DIR__ . '/.env');

$conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['caregiver_id'])) {
    $caregiver_id = intval($_GET['caregiver_id']);

    $allTimeSlots = [
        '10:00 AM - 11:00 AM',
        '11:00 AM - 12:00 PM',
        '12:00 PM - 01:00 PM',
        '01:00 PM - 02:00 PM',
        '02:00 PM - 03:00 PM',
        '03:00 PM - 04:00 PM',
        '04:00 PM - 05:00 PM'
    ];

    $stmt = $conn->prepare("SELECT time_slot FROM caregiver_schedule WHERE caregiver_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $caregiver_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignedSlots = [];
    while ($row = $result->fetch_assoc()) {
        $assignedSlots[] = $row['time_slot'];
    }

    $availableSlots = array_diff($allTimeSlots, $assignedSlots);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'timeSlots' => array_values($availableSlots)]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
