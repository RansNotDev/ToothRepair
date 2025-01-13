<?php
require_once "db_connection.php"; 

if (isset($_GET['date'])) {
    $selectedDate = $_GET['date'];
    $dayOfWeek = date('l', strtotime($selectedDate)); 

    // 1. Get clinic hours for the selected day
    $sqlClinicHours = "SELECT opening_time, closing_time 
                    FROM clinic_hours 
                    WHERE day_of_week = ?";
    $stmtClinicHours = $conn->prepare($sqlClinicHours);
    $stmtClinicHours->bind_param("s", $dayOfWeek);
    $stmtClinicHours->execute();
    $stmtClinicHours->bind_result($openingTime, $closingTime);
    $stmtClinicHours->fetch();
    $stmtClinicHours->close();

    // 2. Get booked appointments for the selected date and dentist (replace 1 with actual dentist ID)
    $sqlAppointments = "SELECT appointment_time 
                        FROM appointments 
                        WHERE appointment_date = ? AND dentist_id = 1"; 
    $stmtAppointments = $conn->prepare($sqlAppointments);
    $stmtAppointments->bind_param("s", $selectedDate);
    $stmtAppointments->execute();
    $resultAppointments = $stmtAppointments->get_result();
    $bookedSlots = array();
    while ($row = $resultAppointments->fetch_assoc()) {
        $bookedSlots = $row['appointment_time'];
    }
    $stmtAppointments->close();

    // 3. Get all time slots for the dentist (replace 1 with actual dentist ID)
    $sqlTimeSlots = "SELECT start_time, end_time 
                    FROM time_slots 
                    WHERE dentist_id = 1 AND day_of_week = ?"; 
    $stmtTimeSlots = $conn->prepare($sqlTimeSlots);
    $stmtTimeSlots->bind_param("s", $dayOfWeek);
    $stmtTimeSlots->execute();
    $resultTimeSlots = $stmtTimeSlots->get_result();
    $availableSlots = array();
    while ($row = $resultTimeSlots->fetch_assoc()) {
        $startTime = $row['start_time'];
        $endTime = $row['end_time'];

        if ($startTime >= $openingTime && $endTime <= $closingTime && !in_array($startTime, $bookedSlots)) {
            $availableSlots = date('h:i A', strtotime($startTime)) . '-' . date('h:i A', strtotime($endTime));
        }
    }
    $stmtTimeSlots->close();

    echo json_encode($availableSlots); 
    $conn->close();
}
?>