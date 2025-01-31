<?php
// Start output buffering at the very top
ob_start();

// Include necessary files
include_once('../database/db_connection.php');
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');

// Get current settings
$settingsQuery = "SELECT * FROM clinic_settings LIMIT 1";
$result = mysqli_query($conn, $settingsQuery);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$settings = mysqli_fetch_assoc($result) ?? [];

// Provide Default Values
$max_daily = $settings['max_daily_appointments'] ?? 10;
$contact_number = $settings['contact_number'] ?? 'Not Set';
$email = $settings['email'] ?? 'Not Set';
$address = $settings['address'] ?? 'Not Set';
$about_content = $settings['about_content'] ?? 'No content available.';
$holidays = isset($settings['holidays']) ? $settings['holidays'] : '';
$logo = $settings['logo'] ?? 'uploads/default_logo.png';
$cover = $settings['cover'] ?? 'uploads/default_cover.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Other form data
    $max_daily = (int)$_POST['max_daily'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $about_content = $_POST['about_content'];

    // Get holidays from the hidden input
    $holidays = isset($_POST['holidays']) ? $_POST['holidays'] : '[]';
    $holidays = json_decode($holidays, true); // Decode JSON string to array

    // File Upload Handling (unchanged)
    $uploadDir = "uploads/";
    if (!empty($_FILES['logo']['tmp_name'])) {
        $logoPath = $uploadDir . basename($_FILES['logo']['name']);
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
            $logo = $logoPath;
        }
    }
    if (!empty($_FILES['cover']['tmp_name'])) {
        $coverPath = $uploadDir . basename($_FILES['cover']['name']);
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath)) {
            $cover = $coverPath;
        }
    }

    // Update or insert settings
    $checkQuery = "SELECT * FROM clinic_settings LIMIT 1";
    $checkResult = mysqli_query($conn, $checkQuery);
    $settingsExist = mysqli_num_rows($checkResult) > 0;

    if ($settingsExist) {
        // Update existing settings
        $updateQuery = "UPDATE clinic_settings SET
                        max_daily_appointments = ?, 
                        contact_number = ?, 
                        email = ?, 
                        address = ?, 
                        about_content = ?, 
                        holidays = ?, 
                        logo = ?, 
                        cover = ? 
                        WHERE setting_id = 1";

        $stmt = mysqli_prepare($conn, $updateQuery);
        $holidays_json = json_encode($holidays);
        mysqli_stmt_bind_param($stmt, "isssssss", $max_daily, $contact_number, $email, $address, $about_content, $holidays_json, $logo, $cover);
    } else {
        // Insert new settings
        $insertQuery = "INSERT INTO clinic_settings (
                        max_daily_appointments, 
                        contact_number, 
                        email, 
                        address, 
                        about_content, 
                        holidays, 
                        logo, 
                        cover
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $insertQuery);
        $holidays_json = json_encode($holidays);
        mysqli_stmt_bind_param($stmt, "isssssss", $max_daily, $contact_number, $email, $address, $about_content, $holidays_json, $logo, $cover);
    }

    mysqli_stmt_execute($stmt);

    // Redirect after processing
    header("Location: settings.php");
    exit();
}

// End output buffering and send output to the browser
ob_end_flush();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Clinic Settings</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Clinic Information</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" value="<?= htmlspecialchars($contact_number) ?>" required>
                </div>
                <div class="form-group">
                    <label>Office Address</label>
                    <textarea class="form-control" name="address" rows="3" required><?= htmlspecialchars($address) ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Appointment Settings</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Max Patients Per Day</label>
                    <input type="number" class="form-control" name="max_daily" value="<?= htmlspecialchars($max_daily) ?>" min="1" required>
                </div>
                <div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">Holiday Settings</h6>
    </div>
    <div class="card-body">
        <!-- Date Picker for Holidays -->
        <div class="form-group">
            <label>Select Holiday Date</label>
            <input type="date" id="holidayDate" class="form-control">
        </div>

        <!-- Holiday Name Input (Hidden by Default) -->
        <div class="form-group" id="holidayNameGroup" style="display: none;">
            <label>Holiday Name</label>
            <input type="text" id="holidayName" name="holidayName" class="form-control" placeholder="Enter holiday name">
        </div>

        <!-- Display Selected Holidays -->
        <div class="form-group">
            <label>Selected Holidays</label>
            <ul id="selectedHolidaysList" class="list-group">
                <!-- Dynamically populated by JavaScript -->
            </ul>
        </div>

        <!-- Hidden Input to Store Holidays for Form Submission -->
        <input type="hidden" id="holidays" name="holidays" value="<?= htmlspecialchars($holidays) ?>">
    </div>
</div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">About Us</h6>
            </div>
            <div class="card-body">
                <textarea class="form-control summernote" name="about_content" rows="6"><?= htmlspecialchars($about_content) ?></textarea>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Clinic Media</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Logo</label>
                    <input type="file" class="form-control" name="logo">
                    <?php if (!empty($logo)): ?>
                        <img src="<?= htmlspecialchars($logo) ?>" alt="Clinic Logo" class="img-thumbnail mt-2" style="max-width: 150px;">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" class="form-control" name="cover">
                    <?php if (!empty($cover)): ?>
                        <img src="<?= htmlspecialchars($cover) ?>" alt="Clinic Cover" class="img-thumbnail mt-2" style="max-width: 300px;">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Update Settings</button>
    </form>
</div>

<?php include_once('includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const holidayDateInput = document.getElementById('holidayDate');
    const holidayNameGroup = document.getElementById('holidayNameGroup');
    const holidayNameInput = document.getElementById('holidayName');
    const selectedHolidaysList = document.getElementById('selectedHolidaysList');
    const holidaysHiddenInput = document.getElementById('holidays');

    // Array to store selected holidays
    let selectedHolidays = [];

    // Parse existing holidays from the hidden input
    if (holidaysHiddenInput.value) {
        selectedHolidays = JSON.parse(holidaysHiddenInput.value);
        updateHolidaysList();
    }

    // Show holiday name input when a date is selected
    holidayDateInput.addEventListener('change', function () {
        if (this.value) {
            holidayNameGroup.style.display = 'block';
        } else {
            holidayNameGroup.style.display = 'none';
        }
    });

    // Add holiday to the list when the user finishes typing the name
    holidayNameInput.addEventListener('blur', function () {
        const date = holidayDateInput.value;
        const name = this.value.trim();

        if (date && name) {
            // Check if the date is already added
            const exists = selectedHolidays.some(holiday => holiday.date === date);
            if (!exists) {
                // Add the holiday to the list
                selectedHolidays.push({ date, name });
                updateHolidaysList();

                // Clear inputs
                holidayDateInput.value = '';
                holidayNameInput.value = '';
                holidayNameGroup.style.display = 'none';
            } else {
                alert('This date is already added as a holiday.');
            }
        }
    });

    // Update the displayed list of holidays and the hidden input
    function updateHolidaysList() {
        // Clear the list
        selectedHolidaysList.innerHTML = '';

        // Add each holiday to the list
        selectedHolidays.forEach((holiday, index) => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.innerHTML = `
                ${holiday.date} - ${holiday.name}
                <button type="button" class="btn btn-danger btn-sm" onclick="removeHoliday(${index})">Remove</button>
            `;
            selectedHolidaysList.appendChild(listItem);
        });

        // Update the hidden input with the JSON string of holidays
        holidaysHiddenInput.value = JSON.stringify(selectedHolidays);
    }

    // Function to remove a holiday from the list
    window.removeHoliday = function (index) {
        selectedHolidays.splice(index, 1);
        updateHolidaysList();
    };
});
</script>