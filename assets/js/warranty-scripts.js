jQuery(document).ready(function($) {
    $("#installation_date").persianDatepicker({
        format: "YYYY/MM/DD",
        autoClose: true,
        toolbox: {
            calendarSwitch: {
                enabled: false
            }
        }
    });

    // Handle form submission success popup
    $('form').on('submit', function() {
        setTimeout(function() {
            $('#warranty-success-popup').removeClass('hidden');
            setTimeout(function() {
                location.reload();
            }, 5000); // Refresh after 5 seconds
        }, 100); // Slight delay to ensure form submission starts
    });
});