$(document).ready(function() {
    // Initialize DataTable
    var table = $('#dataTable').DataTable({
        "dom": 't<"bottom"ip>', // Only show table, pagination, and info
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Alphabetical filter
    $('.alpha-filter button').on('click', function() {
        let letter = $(this).data('letter');
        
        // Remove active class from all buttons
        $('.alpha-filter button').removeClass('active');
        // Add active class to clicked button
        $(this).addClass('active');

        if (letter === 'all') {
            table.column(0) // Assuming first column is name
                .search('')
                .draw();
        } else {
            table.column(0) // Assuming first column is name
                .search('^' + letter, true, false)
                .draw();
        }
    });
});