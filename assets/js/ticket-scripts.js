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

    function refreshRemoveButtons() {
        const rows = $('#issue-items .issue-item-row');
        rows.each(function(index) {
            const removeBtn = $(this).find('.remove-issue-row');
            if (index === 0) {
                removeBtn.addClass('hidden');
            } else {
                removeBtn.removeClass('hidden');
            }
        });
    }

    function addIssueRow() {
        const $rows = $('#issue-items .issue-item-row');
        const $newRow = $rows.first().clone();
        $newRow.find('input[type="text"], input[type="number"], textarea').val('');
        $newRow.find('select').prop('selectedIndex', 0);
        $newRow.find('input[type="file"]').val('');
        $('#issue-items').append($newRow);
        refreshRemoveButtons();
    }

    refreshRemoveButtons();

    $('#issue-items').on('click', '.add-issue-row', function() {
        addIssueRow();
    });

    $('#issue-items').on('click', '.remove-issue-row', function() {
        if ($('#issue-items .issue-item-row').length > 1) {
            $(this).closest('.issue-item-row').remove();
            refreshRemoveButtons();
        }
    });

    $('#issue-items').on('change', '.issue-attachment', function() {
        const file = this.files[0];
        if (!file) {
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('تنها امکان بارگذاری فرمت‌های رایج تصویر وجود دارد.');
            this.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('حجم فایل ضمیمه نباید بیش از ۱۰ مگابایت باشد.');
            this.value = '';
        }
    });

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
                if (response && response.success === false) {
                    const errorMessage = response.data && response.data.message ? response.data.message : 'خطا در ارسال درخواست. لطفاً دوباره تلاش کنید.';
                    alert(errorMessage);
                    submitButton.prop('disabled', false);
                    submitButton.text('ارسال درخواست');
                    return;
                }

                $('#ticket-success-overlay').removeClass('hidden').addClass('flex');

                setTimeout(function() {
                    window.location.href = 'https://dede.ir/all-tickets/';
                }, 1200);
            },
            error: function(xhr, status, error) {
                console.log('Error submitting ticket', error);
                const responseJSON = xhr.responseJSON || {};
                const message = (responseJSON.data && responseJSON.data.message) || responseJSON.message || 'خطا در ارسال درخواست. لطفاً دوباره تلاش کنید.';
                alert(message);

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
            const ticketNumber = details.ticket_number || '-';
            const orderNumber = details.order_number || '-';
            const orderDate = details.order_date || '-';
            const ticketStatus = details.status || '-';

            $('#popup-ticket-number').text(ticketNumber);
            $('#popup-order-number').text(orderNumber);
            $('#popup-order-date').text(orderDate);
            const summaryText = `درخواست شما به شماره ${ticketNumber} برای شماره سفارش ${orderNumber} که در تاریخ ${orderDate} دریافت شده ثبت شده است. این درخواست هم اکنون در وضعیت ${ticketStatus} می‌باشد.`;
            $('#popup-summary').text(summaryText);
            $('#ticket-id').val(ticketId);

            const items = details.issue_items || [];
            const $itemsContainer = $('#popup-items').empty();
            if (items.length) {
                items.forEach(item => {
                    const attachment = item.attachment ? `<a href="${item.attachment}" target="_blank" class="text-blue-600 hover:underline">دانلود</a>` : 'بدون فایل';
                    $itemsContainer.append(
                        `<div class="border border-gray-200 p-3 rounded">` +
                            `<p class="font-semibold">${item.product_name || ''} (تعداد: ${item.quantity || '-'} )</p>` +
                            `<p class="text-sm text-gray-700 mt-1">${item.issue_type || ''}</p>` +
                            `<p class="text-sm text-gray-600 mt-1">${item.issue_description || ''}</p>` +
                            `<p class="text-sm text-gray-600 mt-2">${attachment}</p>` +
                        `</div>`
                    );
                });
            } else {
                $itemsContainer.append('<p class="text-gray-500">مشخصات کالا ثبت نشده است.</p>');
            }

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