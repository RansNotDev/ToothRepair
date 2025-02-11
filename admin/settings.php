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
    // Validate and sanitize inputs
    $max_daily = filter_input(INPUT_POST, 'max_daily', FILTER_VALIDATE_INT);
    if ($max_daily === false || $max_daily < 1) {
        $_SESSION['error'] = "Maximum daily appointments must be at least 1";
        header("Location: settings.php");
        exit();
    }

    // Remove the upper limit check to allow admin flexibility
    $contact_number = filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $about_content = $_POST['about_content'];
    
    // Improve holiday handling
    $holidays = isset($_POST['holidays']) ? $_POST['holidays'] : '[]';
    $holidaysArray = json_decode($holidays, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $_SESSION['error'] = "Invalid holiday data format";
        header("Location: settings.php");
        exit();
    }

    // File Upload Handling with validation
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Logo upload handling
    if (!empty($_FILES['logo']['tmp_name'])) {
        $fileInfo = pathinfo($_FILES['logo']['name']);
        $newLogoName = 'logo_' . time() . '.' . $fileInfo['extension'];
        $logoPath = $uploadDir . $newLogoName;
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($fileInfo['extension']), $allowedTypes)) {
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
                $logo = $logoPath;
            }
        }
    }

    // Cover photo upload handling
    if (!empty($_FILES['cover']['tmp_name'])) {
        $fileInfo = pathinfo($_FILES['cover']['name']);
        $newCoverName = 'cover_' . time() . '.' . $fileInfo['extension'];
        $coverPath = $uploadDir . $newCoverName;
        
        // Validate file type
        if (in_array(strtolower($fileInfo['extension']), $allowedTypes)) {
            if (move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath)) {
                $cover = $coverPath;
            }
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
        if (!mysqli_stmt_bind_param($stmt, "isssssss", 
            $max_daily, $contact_number, $email, $address, 
            $about_content, $holidays_json, $logo, $cover)) {
            $_SESSION['error'] = "Error binding parameters: " . mysqli_error($conn);
            header("Location: settings.php");
            exit();
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            $_SESSION['error'] = "Error updating settings: " . mysqli_error($conn);
        } else {
            $_SESSION['success'] = "Settings updated successfully!";
        }
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- SB Admin 2 Template -->
<link href="css/sb-admin-2.min.css" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">



</head>
<body>
    

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Clinic Settings</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
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
                    <label>Maximum Appointments Per Day</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="max_daily" 
                               value="<?= htmlspecialchars($max_daily) ?>" 
                               min="1" max="50" required>
                        <div class="input-group-append">
                            <span class="input-group-text">patients</span>
                        </div>
                    </div>
                    <small class="form-text text-muted">Set the maximum number of appointments allowed per day</small>
                </div>

                <!-- Holiday Settings -->
                <div class="form-group mt-4">
                    <label>Holiday Management</label>
                    <div class="input-group mb-3">
                        <input type="date" id="holidayDate" class="form-control" 
                               min="<?= date('Y-m-d') ?>">
                        <input type="text" id="holidayName" class="form-control" 
                               placeholder="Holiday Name">
                        <div class="input-group-append">
                            <button type="button" id="addHoliday" class="btn btn-primary">
                                Add Holiday
                            </button>
                        </div>
                    </div>
                    
                    <div id="holidayList" class="list-group mt-3">
                        <!-- Holidays will be listed here -->
                    </div>
                    <input type="hidden" name="holidays" id="holidaysInput" 
                           value="<?= htmlspecialchars($holidays) ?>">
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Clinic Logo</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="logo" name="logo" 
                                       accept="image/png, image/jpeg">
                                <label class="custom-file-label" for="logo">Choose file</label>
                            </div>
                            <?php if (!empty($logo)): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($logo) ?>" alt="Current Logo" 
                                         class="img-thumbnail" style="max-height: 100px">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cover Photo</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="cover" name="cover" 
                                       accept="image/png, image/jpeg">
                                <label class="custom-file-label" for="cover">Choose file</label>
                            </div>
                            <?php if (!empty($cover)): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($cover) ?>" alt="Current Cover" 
                                         class="img-thumbnail" style="max-height: 150px">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Update Settings</button>
    </form>
</div>

<?php include_once('includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const holidayDate = document.getElementById('holidayDate');
    const holidayName = document.getElementById('holidayName');
    const addHolidayBtn = document.getElementById('addHoliday');
    const holidayList = document.getElementById('holidayList');
    const holidaysInput = document.getElementById('holidaysInput');
    
    let holidays = [];
    
    // Load existing holidays with error handling
    try {
        const savedHolidays = holidaysInput.value.trim();
        holidays = savedHolidays ? JSON.parse(savedHolidays) : [];
        if (!Array.isArray(holidays)) {
            throw new Error('Invalid holiday data structure');
        }
        renderHolidays();
    } catch (e) {
        console.error('Error parsing holidays:', e);
        holidays = [];
        Swal.fire({
            icon: 'warning',
            title: 'Holiday Data Reset',
            text: 'There was an issue with the saved holiday data. The list has been reset.'
        });
    }
    
    // Add holiday with validation
    addHolidayBtn.addEventListener('click', function() {
        const date = holidayDate.value;
        const name = holidayName.value.trim();
        
        if (!date || !name) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: 'Please enter both date and holiday name'
            });
            return;
        }

        // Check if date is in the past
        if (new Date(date) < new Date().setHours(0,0,0,0)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'Cannot add holidays in the past'
            });
            return;
        }
        
        // Check if date already exists
        if (holidays.some(h => h.date === date)) {
            Swal.fire({
                icon: 'warning',
                title: 'Date Already Added',
                text: 'This date is already marked as a holiday'
            });
            return;
        }
        
        holidays.push({
            date: date,
            name: name
        });
        
        holidayDate.value = '';
        holidayName.value = '';
        
        renderHolidays();
    });
    
    // Improved removeHoliday function
    window.removeHoliday = function(index) {
        if (index < 0 || index >= holidays.length) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid holiday index'
            });
            return;
        }

        const holiday = holidays[index];
        Swal.fire({
            title: 'Remove Holiday?',
            html: `Are you sure you want to remove:<br><strong>${formatDate(holiday.date)}</strong><br>${holiday.name}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                holidays.splice(index, 1);
                renderHolidays();
                Swal.fire(
                    'Removed!',
                    'The holiday has been removed.',
                    'success'
                );
            }
        });
    };
    
    function renderHolidays() {
        // Sort holidays by date
        holidays.sort((a, b) => new Date(a.date) - new Date(b.date));
        
        // Update hidden input
        holidaysInput.value = JSON.stringify(holidays);
        
        // Render list with empty state handling
        if (holidays.length === 0) {
            holidayList.innerHTML = '<div class="list-group-item text-muted">No holidays added</div>';
            return;
        }
        
        holidayList.innerHTML = holidays.map((holiday, index) => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${formatDate(holiday.date)}</strong>
                    <span class="ml-2">${holiday.name}</span>
                </div>
                <button type="button" class="btn btn-danger btn-sm" 
                        onclick="removeHoliday(${index})"
                        title="Remove Holiday">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');
    }
    
    function formatDate(dateStr) {
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        return new Date(dateStr).toLocaleDateString('en-US', options);
    }
});
</script>
</body>
</html>