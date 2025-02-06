function cancelAppointment(appointmentId) {
    const reason = prompt("Please enter the reason for cancellation:");
    if (reason === null) return; // User clicked Cancel

    $.ajax({
        url: 'pages/cancel_appointment.php',
        type: 'POST',
        data: {
            appointment_id: appointmentId,
            cancel_reason: reason
        },
        success: function(response) {
            if (response.success) {
                alert('Appointment cancelled successfully');
                // Update progress bar or status display
                updateAppointmentStatus(appointmentId, 'Cancelled');
                // Refresh the page or update UI
                location.reload();
            } else {
                alert('Failed to cancel appointment: ' + response.message);
            }
        },
        error: function() {
            alert('Error processing request');
        }
    });
}

function updateAppointmentStatus(appointmentId, status) {
    const statusElement = document.querySelector(`[data-appointment-id="${appointmentId}"] .status`);
    if (statusElement) {
        statusElement.textContent = status;
    }
}