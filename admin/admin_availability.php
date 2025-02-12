<?php
session_start();
include_once('../database/db_connection.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

function deletePastAvailabilityDates($conn) {
    $currentDate = date("Y-m-d");
    $stmt = $conn->prepare("DELETE FROM availability_tb WHERE available_date < ?");
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $stmt->close();
}

deletePastAvailabilityDates($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['available_dates']) && isset($_POST['time_start']) && isset($_POST['time_end'])) {
        $dates = $_POST['available_dates'];
        $timeStart = date("H:i:s", strtotime($_POST['time_start']));
        $timeEnd = date("H:i:s", strtotime($_POST['time_end']));

        if (empty($timeStart) || empty($timeEnd)) {
            http_response_code(400);
            echo json_encode(['error' => 'Please set both time fields.']);
            exit();
        }

        try {
            $conn->begin_transaction();
            
            // Instead of deleting all records, we'll only insert new ones
            $stmt = $conn->prepare("INSERT INTO availability_tb (available_date, time_start, time_end) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE time_start = VALUES(time_start), time_end = VALUES(time_end)");
            
            foreach ($dates as $date) {
                if (DateTime::createFromFormat('Y-m-d', $date)) {
                    $stmt->bind_param("sss", $date, $timeStart, $timeEnd);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            header("Location: admin_availability.php?success=1");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => 'Error saving availability']);
            exit();
        }
    }
}

include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clinic Availability Management</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- SB Admin 2 Template -->
<link href="css/sb-admin-2.min.css" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <style>
   body {
    overflow: hidden;
  }
  </style>
</head>
<body class="bg-gray-100">
  <div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
      <h1 class="h3 mb-0 text-gray-800">Manage Clinic Availability</h1>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card shadow mb-4">
          <div class="card-body">
            <form method="post" action="admin_availability.php">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label for="time_start" class="block text-sm font-medium text-gray-700">Start Time</label>
                  <input type="time" name="time_start" id="time_start" required
                         class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                  <label for="time_end" class="block text-sm font-medium text-gray-700">End Time</label>
                  <input type="time" name="time_end" id="time_end" required
                         class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
              </div>

              <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between mb-4">
                  <button type="button" id="prevMonth" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Previous
                  </button>
                  <h3 id="currentMonth" class="text-xl font-bold self-center"></h3>
                  <button type="button" id="nextMonth" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Next
                  </button>
                </div>

                <div id="calendar" class="grid grid-cols-7 gap-2 mb-4"></div>

                <div class="text-center">
                  <button type="submit" id="saveAvailability" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                    Save Availability
                  </button>
                </div>
              </div>

              <!-- Max Appointments Modal -->
              <div id="maxAppointmentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
                <div class="bg-white p-8 rounded-lg shadow-xl w-96">
                  <h3 class="text-xl font-bold mb-4">Set Maximum Daily Appointments</h3>
                  <div class="mb-4">
                    <label for="maxAppointments" class="block text-sm font-medium text-gray-700 mb-2">
                      Number of Slots Available Per Day
                    </label>
                    <input type="number" id="maxAppointments" name="max_appointments" min="1" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                  </div>
                  <div class="flex justify-end space-x-4">
                    <button type="button" id="cancelModal" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                      Cancel
                    </button>
                    <button type="button" id="confirmMaxAppointments" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                      Confirm
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const currentMonthEl = document.getElementById('currentMonth');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    let currentDate = new Date();

    function fetchAvailability(callback) {
        fetch('fetch-availability.php')
            .then(response => response.json())
            .then(data => callback(data))
            .catch(error => console.error('Error:', error));
    }

    function updateAvailability(date, isChecked) {
        const formData = new FormData();
        formData.append('date', date);
        formData.append('checked', isChecked);
        
        if (isChecked) {
            const timeStart = document.getElementById('time_start').value;
            const timeEnd = document.getElementById('time_end').value;
            
            if (!timeStart || !timeEnd) {
                alert('Please set start and end times first');
                return false;
            }
            formData.append('timeStart', timeStart);
            formData.append('timeEnd', timeEnd);
        }

        return fetch('update-availability.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh calendar data instead of page reload
                fetchAvailability(availability => renderCalendar(currentDate, availability));
                return true;
            }
            return false;
        })
        .catch(() => false);
    }

    // Add time input handlers
    function toggleCheckboxes() {
        const timeStart = document.getElementById('time_start').value;
        const timeEnd = document.getElementById('time_end').value;
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(:checked)');
        
        checkboxes.forEach(checkbox => {
            const dateObj = new Date(checkbox.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Disable unchecked boxes if no time set or date is in past
            checkbox.disabled = (!timeStart || !timeEnd || dateObj < today);
        });
    }

    document.getElementById('time_start').addEventListener('change', toggleCheckboxes);
    document.getElementById('time_end').addEventListener('change', toggleCheckboxes);

    function renderCalendar(date, availability) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                            'July', 'August', 'September', 'October', 'November', 'December'];

        calendarEl.innerHTML = '';
        currentMonthEl.textContent = `${monthNames[month]} ${year}`;

        // Create day headers
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
            const dayEl = document.createElement('div');
            dayEl.className = 'font-bold text-center text-gray-600';
            dayEl.textContent = day;
            calendarEl.appendChild(dayEl);
        });

        // Add empty cells for days before the first of the month
        for (let i = 0; i < firstDayOfMonth; i++) {
            calendarEl.appendChild(document.createElement('div'));
        }

        // Create date cells
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dateObj = new Date(year, month, day);
            const dayEl = document.createElement('div');
            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            const dayNumber = document.createElement('span');

            // Configure checkbox
            checkbox.type = 'checkbox';
            checkbox.name = 'available_dates[]';
            checkbox.value = dateStr;
            checkbox.className = 'mr-2';

            // Configure day number display
            dayNumber.className = 'inline-block w-8 h-8 leading-8';
            dayNumber.textContent = day;

            // Check if date is available
            if (availability.includes(dateStr)) {
                checkbox.checked = true;
            }

            // Handle past dates
            if (dateObj < today) {
                dayEl.classList.add('past-date');
                checkbox.disabled = true;
            }

            // Add click handler for visual feedback
            label.addEventListener('click', function(e) {
                if (!checkbox.disabled) {
                    // Remove toggling of 'bg-blue-500', 'text-white', 'rounded-full'
                }
            });

            checkbox.addEventListener('change', function(e) {
                if (this.checked && (!timeStart.value || !timeEnd.value)) {
                    e.preventDefault();
                    this.checked = false;
                    alert('Please set start and end times first');
                    return;
                }

                if (this.checked) {
                    // Remove dayNumber.classList.add('bg-blue-500', 'text-white', 'rounded-full');
                } else {
                    // Remove dayNumber.classList.remove('bg-blue-500', 'text-white', 'rounded-full');
                }
                updateAvailability(dateStr, this.checked);
            });

            label.className = 'calendar-day';
            label.appendChild(checkbox);
            label.appendChild(dayNumber);
            dayEl.appendChild(label);
            calendarEl.appendChild(dayEl);
        }

        // Call toggleCheckboxes after rendering
        toggleCheckboxes();
    }

    // Month navigation
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        fetchAvailability(availability => renderCalendar(currentDate, availability));
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        fetchAvailability(availability => renderCalendar(currentDate, availability));
    });

    // Initial render
    fetchAvailability(availability => renderCalendar(currentDate, availability));

    // Add this inside your existing DOMContentLoaded event listener

    const form = document.querySelector('form');
    const modal = document.getElementById('maxAppointmentsModal');
    const saveBtn = document.getElementById('saveAvailability');
    const confirmBtn = document.getElementById('confirmMaxAppointments');
    const cancelBtn = document.getElementById('cancelModal');
    
    // Prevent form from submitting directly
    form.onsubmit = (e) => e.preventDefault();

    // Show modal when save button is clicked
    saveBtn.addEventListener('click', () => {
        // Validate if any dates are selected
        const selectedDates = document.querySelectorAll('input[name="available_dates[]"]:checked');
        if (selectedDates.length === 0) {
            alert('Please select at least one date');
            return;
        }
        
        // Validate time inputs
        const timeStart = document.getElementById('time_start').value;
        const timeEnd = document.getElementById('time_end').value;
        if (!timeStart || !timeEnd) {
            alert('Please set both start and end times');
            return;
        }

        modal.classList.remove('hidden');
    });

    // Hide modal when cancel is clicked
    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Handle form submission with max appointments
    confirmBtn.addEventListener('click', () => {
        const maxAppointments = document.getElementById('maxAppointments').value;
        
        if (!maxAppointments || maxAppointments < 1) {
            alert('Please enter a valid number of maximum appointments');
            return;
        }

        // Get only the newly selected dates
        const selectedDates = Array.from(document.querySelectorAll('input[name="available_dates[]"]:checked'))
            .map(checkbox => checkbox.value);

        // Create FormData from the existing form
        const formData = new FormData();
        formData.append('max_appointments', maxAppointments);
        
        // Add only the newly selected dates
        selectedDates.forEach(date => {
            formData.append('available_dates[]', date);
        });
        
        formData.append('time_start', document.getElementById('time_start').value);
        formData.append('time_end', document.getElementById('time_end').value);

        // Send the data to the server
        fetch('save_availability.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.classList.add('hidden');
                Swal.fire({
                    title: 'Success!',
                    text: 'Availability and maximum appointments have been saved.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.error || 'Failed to save');
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
});
  </script>
</body>
</html>