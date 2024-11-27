<?php
function loadEnv($file) {
    if (!file_exists($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignore comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split by '=' sign
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

// Load the .env file
loadEnv(__DIR__ . '/.env');

$conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));

if (isset($_GET['id'])) {
    $patient_id = $_GET['id'];

    $stmt = $conn->prepare("UPDATE patients SET caregiver_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $patient_id);

    if ($stmt->execute()) {
        $stmt = $conn->prepare("DELETE FROM caregiver_schedule WHERE patient_id = ?");
        $stmt->bind_param("i", $patient_id);
        if ($stmt->execute()) {
        header("Location: dashboard.php");
        }else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
