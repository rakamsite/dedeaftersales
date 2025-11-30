jQuery(document).ready(function($) {
    $("#order_date").persianDatepicker({
        format: "YYYY/MM/DD",
        autoClose: true,
        toolbox: {
            calendarSwitch: {
                enabled: false
            }
        }
    });

    let redirectTimeout;

    // Handle ticket form submission with AJAX
    $('.ticket-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        const form = $(this);
        const submitButton = form.find('button[type="submit"]');
        const formData = new FormData(form[0]);

        // Show loading state
        submitButton.prop('disabled', true);
        submitButton.text('در حال ارسال...');
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                console.log('Ticket submitted successfully', response);

                // Show success modal instead of redirecting
                $('#ticket-success-modal').removeClass('hidden');

                // Delay redirect to give users time to click the link
                if (redirectTimeout) {
                    clearTimeout(redirectTimeout);
                }

                redirectTimeout = setTimeout(function() {
                    window.location.href = 'https://dede.ir/all-tickets/';
                }, 7000);

                // Reset button state
                submitButton.prop('disabled', false);
                submitButton.text('ارسال درخواست');
            },
            error: function(xhr, status, error) {
                console.log('Error submitting ticket', error);
                alert('خطا در ارسال درخواست. لطفاً دوباره تلاش کنید.');

                // Reset button state
                submitButton.prop('disabled', false);
                submitButton.text('ارسال درخواست');
            }
        });
        return false; // Extra safeguard to prevent full page reload
    });

    // Function to refresh ticket data via AJAX
    function refreshTicketData(ticketId) {
        $.ajax({
            url: ajaxurl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'get_ticket_responses',
                ticket_id: ticketId,
                nonce: $('#user_response_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    const responses = response.data.responses;
                    const $responsesContainer = $('#responses-container').empty();
                    const userFullName = response.data.user_full_name || 'کاربر';
                    
                    if (responses && responses.length > 0) {
                        responses.forEach(response => {
                            const author = response.author === 'admin' ? 'پشتیبان' : userFullName;
                            $responsesContainer.append(
                                `<p class="border border-gray-200 p-3 rounded"><strong>${author}:</strong><br>${response.message}</p>`
                            );
                        });
                        
                        // Scroll to bottom to show latest response
                        $responsesContainer.scrollTop($responsesContainer[0].scrollHeight);
                    } else {
                        $responsesContainer.append('<p class="text-gray-500">هیچ پاسخی ثبت نشده است.</p>');
                    }
                }
            },
            error: function() {
                console.log('Error refreshing ticket data');
            }
        });
    }

    // Handle popup open
    $('.view-ticket').on('click', function() {
        console.log('View ticket clicked'); // For debugging
        const details = $(this).data('ticket-details');
        const ticketId = $(this).data('ticket-id');
        if (details) {
            $('#popup-ticket-number').text(details.ticket_number);
            $('#popup-order-number').text(details.order_number);
            $('#popup-order-date').text(details.order_date);
            $('#popup-delivery-method').text(details.delivery_method);
            $('#popup-issue-type').text(details.issue_type);
            $('#popup-issue-description').text(details.issue_description);
            $('#popup-status').text(details.status);
            $('#ticket-id').val(ticketId);

            // Populate responses
            const $responsesContainer = $('#responses-container').empty();
            const userFullName = details.user_full_name || 'کاربر'; // استفاده از نام و نام خانوادگی
            if (details.responses && details.responses.length > 0) {
                details.responses.forEach(response => {
                    const author = response.author === 'admin' ? 'پشتیبان' : userFullName;
                    $responsesContainer.append(
                        `<p class="border border-gray-200 p-3 rounded"><strong>${author}:</strong><br>${response.message}</p>`
                    );
                });
            } else {
                $responsesContainer.append('<p class="text-gray-500">هیچ پاسخی ثبت نشده است.</p>');
            }

            if (details.attachment) {
                $('#attachment-link').show().attr('href', details.attachment);
            } else {
                $('#attachment-link').hide();
            }
            $('#ticket-popup').removeClass('hidden');
            
            // Scroll to bottom of responses container to show latest responses
            setTimeout(function() {
                const $responsesContainer = $('#responses-container');
                if ($responsesContainer.length) {
                    $responsesContainer.scrollTop($responsesContainer[0].scrollHeight);
                }
            }, 100);
        } else {
            console.log('No ticket details found');
        }
    });

    // Handle popup close
    $('#close-popup, #ticket-popup').on('click', function(e) {
        console.log('Close attempt', e.target.id); // For debugging
        if ($(e.target).is('#ticket-popup') || $(e.target).is('#close-popup')) {
            $('#ticket-popup').addClass('hidden');
        }
    });

    // Handle success modal close actions
    $('#ticket-success-modal').on('click', function(e) {
        if ($(e.target).is('#ticket-success-modal') || $(e.target).is('#close-success-modal')) {
            $('#ticket-success-modal').addClass('hidden');
        }
    });

    // Handle user response form submission
    $('.user-response-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        const form = $(this);
        const formData = form.serialize();
        const ticketId = $('#ticket-id').val();
        const submitButton = form.find('button[type="submit"]');
        const originalButtonText = submitButton.text();
        
        // Show loading state
        submitButton.prop('disabled', true);
        submitButton.html('<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>در حال ارسال...');
        
        $.post(window.location.href, formData, function(response) {
            console.log('Response submitted', response);
            
            // Clear the form
            $('#user_response').val('');
            
            // Refresh ticket data without reloading the page
            if (ticketId) {
                refreshTicketData(ticketId);
                
                // Show success message above the button
                setTimeout(function() {
                    const $form = $('form');
                    if (!$('.success-message').length) {
                        $('<div class="success-message bg-green-100 text-green-700 p-3 rounded-lg text-center mb-3">پاسخ شما با موفقیت ثبت شد.</div>')
                            .insertBefore($form);
                    }
                    
                    // Hide success message after 3 seconds
                    setTimeout(function() {
                        $('.success-message').fadeOut();
                    }, 3000);
                }, 500);
            }
            
        }).fail(function() {
            console.log('Error submitting response');
            alert('خطا در ارسال پاسخ. لطفاً دوباره تلاش کنید.');
        }).always(function() {
            // Reset button state
            submitButton.prop('disabled', false);
            submitButton.text(originalButtonText);
        });
    });
});