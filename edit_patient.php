<?php
// Establish a database connection
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

// Check if the form has been submitted with data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input to prevent SQL injection
    $patient_id = $conn->real_escape_string($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $medical_records = $conn->real_escape_string($_POST['medical_records']);

    // Update the patient record in the database
    $update_query = "UPDATE patients SET name='$name', address='$address', medical_records='$medical_records' WHERE id='$patient_id'";

    if ($conn->query($update_query) === TRUE) {
        // Redirect to the dashboard or display a success message
        header('Location: dashboard.php?update=success');
        exit();
    } else {
        // If there was an error with the query, show an error message
        echo "Error: " . $conn->error;
    }
}
?>
