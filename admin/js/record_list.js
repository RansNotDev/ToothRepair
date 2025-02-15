$(document).ready(function() {
    // Initialize DataTable
    const table = $('#dataTable').DataTable({
        "dom": 't<"bottom"ip>',
        "ordering": true,
        "pageLength": 10
    });

    // Handle sort button clicks
    $('.sort-btn').click(function() {
        // Remove active class from all sort buttons
        $('.sort-btn').removeClass('active');
        // Add active class to clicked button
        $(this).addClass('active');
        
        if ($(this).data('letter') === 'all') {
            // Reset sorting and filtering when "All" is clicked
            table.search('').order([]).draw();
        } else if ($(this).hasClass('sort-asc')) {
            table.order([0, 'asc']).draw();
        } else if ($(this).hasClass('sort-desc')) {
            table.order([0, 'desc']).draw();
        }
    });

    // Handle letter navigation
    $('.nav-prev, .nav-next').click(function() {
        const current = $('.alpha-page.active');
        const isNext = $(this).hasClass('nav-next');
        const nextPage = isNext ? current.next('.alpha-page') : current.prev('.alpha-page');

        if (nextPage.length) {
            current.hide().removeClass('active');
            nextPage.show().addClass('active');
            
            // Update navigation buttons
            $('.nav-prev').prop('disabled', !nextPage.prev('.alpha-page').length);
            $('.nav-next').prop('disabled', !nextPage.next('.alpha-page').length);
        }
    });

    // Handle letter filtering
    $('.alpha-page button[data-letter]').click(function() {
        const letter = $(this).data('letter');
        $('.alpha-page button').removeClass('active');
        $(this).addClass('active');
        table.column(0).search('^' + letter, true, false).draw();
    });

    // Handle search input
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
});