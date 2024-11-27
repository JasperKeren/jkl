<?php
// Database connection with environment variables for credentials
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}
function loadEnv($file)
{
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

// Check for successful connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
        <script>

        function openEditPatientModal(id, name, address, medicalRecords) {
        console.log(id,name,address,medicalRecords)
            document.getElementById('editPatientId').value = id;
            document.getElementById('editPatientName').value = name;
            document.getElementById('editPatientAddress').value = address;
            document.getElementById('editPatientMedicalRecords').value = medicalRecords;
            document.getElementById('editPatientForm').style.display = 'block';
        }

        function openEditCaregiverModal(id, name) {
            document.getElementById('editCaregiverId').value = id;
            document.getElementById('editCaregiverName').value = name;
            document.getElementById('editCaregiverForm').style.display = 'block';
        }

        function closeForm(formId) {
            document.getElementById(formId).style.display = 'none';
        }

        function showAddPatientForm() {
            document.getElementById('addPatientForm').style.display = 'block';
        }

        function showAddCaregiverForm() {
            document.getElementById('addCaregiverForm').style.display = 'block';
        }
    </script>

</head>

<body>
    <div class="container">
        <header>
            <h1>Healthcare Management Dashboard</h1>
            <p>Manage patients and caregivers effectively</p>
            <a href="logout.php" style="text-align: right;" class="logout-button">Logout</a>
        </header>


        <div id="patients" class="tab-content">
            <div class="actions">
                <h2>Patients</h2>
                <button class="actions" onclick="showAddPatientForm()">Add Patient</button>
            </div>
            <div class="search-bar">
                <input type="text" id="patientSearch" placeholder="Search Patients...">
            </div>
            <table id="patientsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Medical Records</th>
                        <th>Assigned Caregiver</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT p.id, p.name, p.address, p.medical_records, c.name AS caregiver 
                                        FROM patients p 
                                        LEFT JOIN caregivers c ON p.caregiver_id = c.id");
                    $stmt->execute();
                    $patients = $stmt->get_result();
                    if($patients->num_rows){
                        while ($row = $patients->fetch_assoc()) {
                            echo "<tr>
                            <td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($row['medical_records'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . ($row['caregiver'] ? htmlspecialchars($row['caregiver'], ENT_QUOTES, 'UTF-8') : 'None') . "</td>
                            <td>
                                <a href='#' onclick='openEditPatientModal(" 
                                    . json_encode($row['id']) . ", " 
                                    . json_encode($row['name']) . ", " 
                                    . json_encode($row['address']) . ", " 
                                    . json_encode($row['medical_records']) . 
                                ")' class='btn edit'>Edit</a>
                                <a href='delete_patient.php?id=" . urlencode($row['id']) . "' class='btn delete' onclick=\"return confirm('Are you sure?')\">Delete</a>"
                                . ($row['caregiver'] ? 
                                    "<a href='unassign_patient.php?id=" . urlencode($row['id']) . "' class='btn unassign'>Unassign</a>" : 
                                    "") . 
                            "</td>
                        </tr>";
                        
                        }
                    }else{
                        echo "<tr><td colspan='5'>No patients found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="caregivers" class="tab-content">
            <div class="actions">
                <h2>Caregivers</h2>
                <button onclick="showAddCaregiverForm()">Add Caregiver</button>
            </div>
            <div class="search-bar">
                <input type="text" id="caregiverSearch" placeholder="Search Caregivers...">
            </div>
            <table id="caregiversTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <!-- <th>Assigned Patients</th> -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT c.id, c.name, p.name AS patient_name 
                                        FROM caregivers c 
                                        LEFT JOIN patients p ON c.id = p.caregiver_id
                                        GROUP BY c.id");
                    $stmt->execute();
                    $caregivers = $stmt->get_result();
                    if($caregivers->num_rows){
                        while ($row = $caregivers->fetch_assoc()) {
                            echo "<tr>
                                <td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>
                                    <a href='#' onclick=\"openEditCaregiverModal({$row['id']}, '" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "')\" class='btn edit'>Edit</a>
                                    <a href='delete_caregiver.php?id={$row['id']}' class='btn delete' onclick=\"return confirm('Are you sure?')\">Delete</a>
                                </td>
                              </tr>";
                        }
                    }else{
                        echo "<tr><td colspan='3'>No caregivers found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="assign" class="tab-content">
            <h2>Assign Patient to Caregiver</h2>
            <?php
function getAvailableTimeSlots($caregiver_id, $conn) {
    // Define all possible time slots
    $allTimeSlots = [
        '10:00 AM - 11:00 AM',
        '11:00 AM - 12:00 PM',
        '12:00 PM - 01:00 PM',
        '01:00 PM - 02:00 PM',
        '02:00 PM - 03:00 PM',
        '03:00 PM - 04:00 PM',
        '04:00 PM - 05:00 PM'
    ];

    // Fetch assigned time slots for the caregiver
    $stmt = $conn->prepare("SELECT time_slot FROM caregiver_schedule WHERE caregiver_id = ?");
    $stmt->bind_param("i", $caregiver_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignedSlots = [];
    while ($row = $result->fetch_assoc()) {
        $assignedSlots[] = $row['time_slot'];
    }

    // Return unassigned time slots
    return array_diff($allTimeSlots, $assignedSlots);
}
?>

<form action="assign_patient.php" method="POST">
    <div class="form-group">
        <label for="patient_id">Select Patient:</label>
        <select name="patient_id" class="select-dropdown" required>
            <option value="" disabled selected>Select Patient</option>
            <?php
            $stmt = $conn->prepare("SELECT id, name FROM patients WHERE id NOT IN (SELECT patient_id FROM caregiver_schedule)");
            $stmt->execute();
            $patients = $stmt->get_result();

            while ($row = $patients->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="caregiver_id">Select Caregiver:</label>
        <select name="caregiver_id" class="select-dropdown" id="caregiver_id" required>
            <option value="" disabled selected>Select Caregiver</option>
            <?php
            $stmt = $conn->prepare("SELECT id, name FROM caregivers");
            $stmt->execute();
            $caregivers = $stmt->get_result();

            while ($row = $caregivers->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group" id="time_slot_container">
        <label for="time_slot">Select Time Slot:</label>
        <select name="time_slot" class="select-dropdown" id="time_slot" required>
            <option value="" disabled selected>Select Time Slot</option>
            <!-- Time slots will be populated dynamically via JavaScript -->
        </select>
    </div>

    <button type="submit" class="btn assign">Assign</button>
</form>

<script>
    document.getElementById('caregiver_id').addEventListener('change', function () {
        const caregiverId = this.value;
        const timeSlotDropdown = document.getElementById('time_slot');

        // Clear existing options
        timeSlotDropdown.innerHTML = '<option value="" disabled selected>Select Time Slot</option>';

        if (caregiverId) {
            // Fetch available time slots for the selected caregiver
            fetch(`get_time_slots.php?caregiver_id=${caregiverId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.timeSlots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = slot;
                            option.textContent = slot;
                            timeSlotDropdown.appendChild(option);
                        });
                    } else {
                        alert('Error fetching time slots');
                    }
                });
        }
    });
</script>
        </div>

    </div>

    <!-- Modal and other sections remain the same -->
    <!-- Add Patient Modal -->
    <div id="addPatientForm" class="modal">
        <form action="add_patient.php" method="POST">
            <h2>Add Patient</h2>
            <label for="name">Name:</label>
            <input type="text" name="name" required>
            <label for="address">Address:</label>
            <input type="text" name="address" required>
            <label for="medical_records">Medical Records:</label>
            <textarea name="medical_records" required></textarea>
            <button type="submit" class="btn add">Add Patient</button>
            <button type="button" onclick="closeForm('addPatientForm')" class="btn cancel">Cancel</button>
        </form>
    </div>

    <!-- Add Caregiver Modal -->
    <div id="addCaregiverForm" class="modal">
        <form action="add_caregiver.php" method="POST">
            <h2>Add Caregiver</h2>
            <label for="name">Name:</label>
            <input type="text" name="name" required>
            <button type="submit" class="btn add">Add Caregiver</button>
            <button type="button" onclick="closeForm('addCaregiverForm')" class="btn cancel">Cancel</button>
        </form>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editPatientForm" class="modal">
        <form action="edit_patient.php" method="POST">
            <input type="hidden" name="id" id="editPatientId">
            <h2>Edit Patient</h2>
            <label for="name">Name:</label>
            <input type="text" name="name" id="editPatientName" required>
            <label for="address">Address:</label>
            <input type="text" name="address" id="editPatientAddress" required>
            <label for="medical_records">Medical Records:</label>
            <textarea name="medical_records" id="editPatientMedicalRecords" required></textarea>
            <button type="submit" class="btn add">Save Changes</button>
            <button type="button" onclick="closeForm('editPatientForm')" class="btn cancel">Cancel</button>
        </form>
    </div>

    <!-- Edit Caregiver Modal -->
    <div id="editCaregiverForm" class="modal">
        <form action="edit_caregiver.php" method="POST">
            <input type="hidden" name="id" id="editCaregiverId">
            <h2>Edit Caregiver</h2>
            <label for="name">Name:</label>
            <input type="text" name="name" id="editCaregiverName" required>
            <button type="submit" class="btn add">Save Changes</button>
            <button type="button" onclick="closeForm('editCaregiverForm')" class="btn cancel">Cancel</button>
        </form>
    </div>

</body>

</html>