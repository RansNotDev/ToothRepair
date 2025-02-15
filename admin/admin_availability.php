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
            
            // Only update time_start and time_end, preserve existing max_daily_appointments
            $stmt = $conn->prepare("UPDATE availability_tb 
                                  SET time_start = ?, time_end = ?
                                  WHERE available_date = ?");
            
            foreach ($dates as $date) {
                if (DateTime::createFromFormat('Y-m-d', $date)) {
                    $stmt->bind_param("sss", $timeStart, $timeEnd, $date);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            echo json_encode(['success' => true]);
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
        overflow-y: auto !important;
    }
    #calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        padding: 16px;
        width: 100%;
        margin-bottom: 20px;
    }
    .calendar-day {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        cursor: pointer;
        min-height: 60px;
        position: relative;
    }
    .past-date {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f3f4f6;
    }
    .calendar-day input[type="checkbox"] {
        margin-right: 8px;
    }
    .calendar-day span {
        text-align: center;
    }
    .day-header {
        font-weight: bold;
        text-align: center;
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    .max-appointments-indicator {
        position: absolute;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.75rem;
        color: #666;
    }
    .calendar-day:not(.past-date):not(.empty):hover {
        background-color: #f3f4f6;
        cursor: pointer;
    }

    .calendar-day input[type="checkbox"] {
        z-index: 2;
        position: relative;
    }

    .calendar-day.past-date {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
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
                  <div class="flex items-center gap-4">
                    <h3 id="currentMonth" class="text-xl font-bold self-center"></h3>
                    <label class="flex items-center space-x-2">
                      <input type="checkbox" id="selectAll" class="form-checkbox h-5 w-5 text-blue-600">
                      <span class="text-gray-700">Select All</span>
                    </label>
                  </div>
                  <button type="button" id="nextMonth" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Next
                  </button>
                </div>


                <div id="calendar" class="grid grid-cols-7 gap-2 mb-4"></div>

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
            .then(data => {
                if (Array.isArray(data)) {
                    const availabilityMap = new Map();
                    data.forEach(item => {
                        availabilityMap.set(item.date, {
                            timeStart: item.timeStart,
                            timeEnd: item.timeEnd,
                            max_daily_appointments: item.max_daily_appointments
                        });
                    });
                    callback(availabilityMap);
                } else if (data.error) {
                    console.error('Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                callback(new Map());
            });
    }

    function updateAvailability(date, isChecked) {
        const formData = new FormData();
        formData.append('date', date);
        formData.append('checked', isChecked);
        
        if (isChecked) {
            const timeStart = document.getElementById('time_start').value;
            const timeEnd = document.getElementById('time_end').value;
            const maxDailyAppointments = document.getElementById('max_daily_appointments').value || 8;
            
            if (!timeStart || !timeEnd) {
                alert('Please set start and end times first');
                return Promise.resolve(false);
            }
            
            formData.append('timeStart', timeStart);
            formData.append('timeEnd', timeEnd);
            formData.append('max_daily_appointments', maxDailyAppointments);
        }

        return fetch('update-availability.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return true;
            }
            throw new Error(data.error || 'Failed to update availability');
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        });
    }

    function toggleCheckboxes() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        const timeStart = document.getElementById('time_start').value;
        const timeEnd = document.getElementById('time_end').value;
        const hasValidTimes = timeStart && timeEnd;
        
        checkboxes.forEach(checkbox => {
            const dateObj = new Date(checkbox.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Only disable if it's a past date
            const isPastDate = dateObj < today;
            checkbox.disabled = isPastDate;
            
            // Add visual indication if times are not set
            if (!hasValidTimes) {
                checkbox.parentElement.style.opacity = '0.7';
            } else {
                checkbox.parentElement.style.opacity = '1';
            }
        });
    }

    // Handle select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(#selectAll)');
        const timeStart = document.getElementById('time_start').value;
        const timeEnd = document.getElementById('time_end').value;
        
        if (!timeStart || !timeEnd) {
            this.checked = false;
            Swal.fire({
                title: 'Warning',
                text: 'Please set start and end times first',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        checkboxes.forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = this.checked;
                const event = new Event('change');
                checkbox.dispatchEvent(event);
            }
        });
    });



    document.getElementById('time_start').addEventListener('change', toggleCheckboxes);
    document.getElementById('time_end').addEventListener('change', toggleCheckboxes);

    function renderCalendar(date, availabilityMap) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                            'July', 'August', 'September', 'October', 'November', 'December'];

        calendarEl.innerHTML = '';
        currentMonthEl.textContent = `${monthNames[month]} ${year}`;

        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
            const dayEl = document.createElement('div');
            dayEl.className = 'day-header';
            dayEl.textContent = day;
            calendarEl.appendChild(dayEl);
        });

        for (let i = 0; i < firstDayOfMonth; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'calendar-day empty';
            calendarEl.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dateObj = new Date(year, month, day);
            const dayEl = document.createElement('div');
            dayEl.className = 'calendar-day';
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const isPastDate = dateObj < today;
            
            if (isPastDate) {
                dayEl.classList.add('past-date');
            }

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'available_dates[]';
            checkbox.value = dateStr;
            checkbox.className = 'mr-2';
            checkbox.disabled = isPastDate;
            
            const dayNumber = document.createElement('span');
            dayNumber.textContent = day;

            const dateInfo = availabilityMap.get(dateStr);
            if (dateInfo) {
                checkbox.checked = true;
                if (dateInfo.max_daily_appointments) {
                    const maxApptsIndicator = document.createElement('div');
                    maxApptsIndicator.className = 'max-appointments-indicator';
                    maxApptsIndicator.textContent = `Max: ${dateInfo.max_daily_appointments}`;
                    dayEl.appendChild(maxApptsIndicator);
                }
            }

            if (!isPastDate) {
                dayEl.addEventListener('click', (e) => {
                    if (e.target !== checkbox) {
                        checkbox.checked = !checkbox.checked;
                        const event = new Event('change');
                        checkbox.dispatchEvent(event);
                    }
                });
            }

            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                const isChecked = this.checked;
                const timeStart = document.getElementById('time_start').value;
                const timeEnd = document.getElementById('time_end').value;
                const dateStr = this.value;
                const checkbox = this;
                
                if (isChecked && (!timeStart || !timeEnd)) {
                    this.checked = false;
                    Swal.fire({
                        title: 'Warning',
                        text: 'Please set start and end times first',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                if (isChecked) {
                    let maxAppts = 8; // Default value
                    
                    const saveAvailability = (maxAppointments) => {
                        const formData = new FormData();
                        formData.append('date', dateStr);
                        formData.append('checked', 'true');
                        formData.append('timeStart', timeStart);
                        formData.append('timeEnd', timeEnd);
                        formData.append('max_daily_appointments', maxAppointments);
                        
                        return fetch('update-availability.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Update UI
                                let existingIndicator = checkbox.parentElement.querySelector('.max-appointments-indicator');
                                if (!existingIndicator) {
                                    existingIndicator = document.createElement('div');
                                    existingIndicator.className = 'max-appointments-indicator';
                                    checkbox.parentElement.appendChild(existingIndicator);
                                }
                                existingIndicator.textContent = `Max: ${maxAppointments}`;
                                
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Availability has been saved.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                throw new Error(data.error || 'Failed to save availability');
                            }
                        })
                        .catch(error => {
                            checkbox.checked = false;
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: error.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                    };
                    
                    Swal.fire({
                        title: 'Set Maximum Appointments',
                        html: `
                            <div id="maxAppointmentsContainer">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Maximum appointments for ${dateStr}
                                    </label>
                                    <input type="number" 
                                           id="single_date_max_appointments" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                           min="1"
                                           value="8">
                                </div>
                            </div>
                        `,
                        showConfirmButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Save',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const maxInput = document.getElementById('single_date_max_appointments');
                            maxAppts = parseInt(maxInput.value) || 8;
                            if (maxAppts < 1) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Please enter a valid number',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                                checkbox.checked = false;
                                return;
                            }
                            saveAvailability(maxAppts);
                        } else {
                            checkbox.checked = false;
                        }
                    });
                } else {
                    // Handle unchecking - remove availability
                    const formData = new FormData();
                    formData.append('date', dateStr);
                    formData.append('checked', 'false');
                    
                    fetch('update-availability.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const indicator = checkbox.parentElement.querySelector('.max-appointments-indicator');
                            if (indicator) {
                                indicator.remove();
                            }
                            Swal.fire({
                                title: 'Success!',
                                text: 'Availability has been removed.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            throw new Error(data.error || 'Failed to remove availability');
                        }
                    })
                    .catch(error => {
                        checkbox.checked = true;
                        Swal.fire({
                            title: 'Error!',
                            text: error.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });

            dayEl.appendChild(checkbox);
            dayEl.appendChild(dayNumber);
            calendarEl.appendChild(dayEl);
        }

        toggleCheckboxes();
    }

    function updateDateAvailability(formData, checkbox) {
        fetch('update-availability.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const isChecked = formData.get('checked') === 'true';
                const existingIndicator = checkbox.parentElement.querySelector('.max-appointments-indicator');
                
                if (isChecked) {
                    if (existingIndicator) {
                        existingIndicator.textContent = `Max: ${formData.get('max_daily_appointments')}`;
                    } else {
                        const maxApptsIndicator = document.createElement('div');
                        maxApptsIndicator.className = 'max-appointments-indicator';
                        maxApptsIndicator.textContent = `Max: ${formData.get('max_daily_appointments')}`;
                        checkbox.parentElement.appendChild(maxApptsIndicator);
                    }
                } else if (existingIndicator) {
                    existingIndicator.remove();
                }
            } else {
                checkbox.checked = !checkbox.checked;
                throw new Error(data.error || 'Failed to update availability');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            checkbox.checked = !checkbox.checked;
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    }

    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        fetchAvailability(availability => renderCalendar(currentDate, availability));
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        fetchAvailability(availability => renderCalendar(currentDate, availability));
    });

    fetchAvailability(availability => renderCalendar(currentDate, availability));
});
  </script>
</body>
</html>
