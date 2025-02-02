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
            $conn->query("DELETE FROM availability_tb");
            
            $stmt = $conn->prepare("INSERT INTO availability_tb (available_date, time_start, time_end) VALUES (?, ?, ?)");
            
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
  <style>
   
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
                  <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                    Save Availability
                  </button>
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
});
  </script>
</body>
</html>