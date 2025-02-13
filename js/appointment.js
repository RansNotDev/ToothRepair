$(document).ready(function() {
    // Close modal with confirmation if form has data
    $('.modal .close, .modal').on('click', function(e) {
        if ($(e.target).hasClass('modal') || $(e.target).hasClass('close') || $(e.target).parent().hasClass('close')) {
            e.preventDefault();
            
            // Check if any form field has data
            const hasData = $('#appointmentForm').find('input, textarea, select').filter(function() {
                return $(this).val();
            }).length > 0;

            if (hasData) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Your appointment details will be lost!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, close it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#appointmentModal').modal('hide');
                        // Reset form
                        $('#appointmentForm')[0].reset();
                    }
                });
            } else {
                $('#appointmentModal').modal('hide');
            }
        }
    });

    // Prevent modal from closing when clicking inside
    $('.modal-content').on('click', function(e) {
        e.stopPropagation();
    });
});