// Filter for Patients
document.getElementById("patientSearch").addEventListener("input", function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll("#patientsTable tbody tr");

    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        row.style.display = name.includes(query) ? "" : "none";
    });
});

// Filter for Caregivers
document.getElementById("caregiverSearch").addEventListener("input", function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll("#caregiversTable tbody tr");

    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        row.style.display = name.includes(query) ? "" : "none";
    });
});

function showAddPatientForm() {
    document.getElementById('addPatientForm').style.display = 'block';
}

function showAddCaregiverForm() {
    document.getElementById('addCaregiverForm').style.display = 'block';
}

function closeForm(formId) {
    document.getElementById(formId).style.display = 'none';
}
