<?php
function loadEnv($file)
{
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

// Load the .env file
loadEnv(__DIR__ . '/.env');

$conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $caregiver_id = $_POST['caregiver_id'];
    $time_slot = $_POST['time_slot'];

    // Check if caregiver is available for the selected time slot
    $stmt = $conn->prepare("SELECT * FROM caregiver_schedule WHERE caregiver_id = ? AND time_slot = ?");
    $stmt->bind_param("is", $caregiver_id, $time_slot);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: Caregiver is already assigned for this time slot.";
    } else {
        // Assign the patient to the caregiver
        $stmt = $conn->prepare("INSERT INTO caregiver_schedule (caregiver_id, patient_id, time_slot) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $caregiver_id, $patient_id, $time_slot);

        if ($stmt->execute()) {
            $stmt = $conn->prepare("UPDATE patients SET caregiver_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $caregiver_id, $patient_id);

            if ($stmt->execute()) {
                header("Location: dashboard.php");
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
