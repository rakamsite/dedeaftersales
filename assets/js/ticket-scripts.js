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
        const form = $(this);
        const hasAttachment = form.find('input[type="file"]').toArray().some(input => input.files && input.files.length > 0);

        if (hasAttachment) {
            return true;
        }

        e.preventDefault(); // Prevent default form submission

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

                window.location.href = 'https://dede.ir/ticket-ok/';
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

    $('#ticket-success-close').on('click', function() {
        $('#ticket-success-overlay').addClass('hidden').removeClass('flex');
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
                    const issueDescription = response.data.issue_description || '';
                    const $responsesContainer = $('#responses-container').empty();
                    const userFullName = response.data.user_full_name || 'کاربر';
                    
                    const visibleResponses = (responses || []).filter(
                        response => !(issueDescription && response.author === 'user' && response.message === issueDescription)
                    );

                    if (visibleResponses.length > 0) {
                        visibleResponses.forEach(response => {
                            const author = response.author === 'admin' ? 'پشتیبان' : userFullName;
                            $responsesContainer.append(
                                `<p class="border border-gray-200 p-3 rounded"><strong>${author}:</strong><br>${response.message}</p>`
                            );
                        });

                        applyResponsesMaxHeight($responsesContainer);

                        // Scroll to bottom to show latest response
                        $responsesContainer.scrollTop($responsesContainer[0].scrollHeight);
                    } else {
                        $responsesContainer.append('<p class="text-gray-500">هیچ پاسخی ثبت نشده است.</p>');
                        applyResponsesMaxHeight($responsesContainer);
                    }
                }
            },
            error: function() {
                console.log('Error refreshing ticket data');
            }
        });
    }

    function applyResponsesMaxHeight($responsesContainer) {
        const $responses = $responsesContainer.children('p');
        if ($responses.length === 0) {
            $responsesContainer.css('max-height', '');
            return;
        }

        const responseCount = Math.min(2, $responses.length);
        let maxHeight = 0;

        for (let i = 0; i < responseCount; i += 1) {
            maxHeight += $responses.eq(i).outerHeight(true);
        }

        const paddingTop = parseFloat($responsesContainer.css('padding-top')) || 0;
        const paddingBottom = parseFloat($responsesContainer.css('padding-bottom')) || 0;

        maxHeight += paddingTop + paddingBottom;
        $responsesContainer.css('max-height', `${maxHeight}px`);
    }

    function populateIssueItems(issueItems, issueDescription) {
        const $issueContainer = $('#issue-items-container').empty();
        const normalizedItems = Array.isArray(issueItems) ? issueItems : [];

        if (normalizedItems.length === 0 && issueDescription) {
            $issueContainer.append(
                $('<div class="border border-gray-200 rounded-lg p-4 bg-gray-50 text-gray-700"></div>')
                    .text(issueDescription)
            );
            return;
        }

        if (normalizedItems.length === 0) {
            $issueContainer.append('<p class="text-gray-500">جزئیاتی برای این درخواست ثبت نشده است.</p>');
            return;
        }

        normalizedItems.forEach((item, index) => {
            const $card = $('<div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2"></div>');
            const title = item.product_name ? item.product_name : `مورد ${index + 1}`;
            $('<p class="text-base font-semibold text-gray-800"></p>').text(title).appendTo($card);

            const $grid = $('<div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700"></div>');
            $('<div></div>').text(`تعداد: ${item.quantity || 'نامشخص'}`).appendTo($grid);
            $('<div></div>').text(`نوع مشکل: ${item.issue_type || 'نامشخص'}`).appendTo($grid);
            $grid.appendTo($card);

            if (item.issue_description) {
                $('<p class="text-sm text-gray-600"></p>').text(item.issue_description).appendTo($card);
            }

            if (item.attachment) {
                const $link = $('<a class="text-indigo-700 hover:text-indigo-900 text-sm font-medium" target="_blank" rel="noopener noreferrer"></a>');
                $link.attr('href', item.attachment);
                $link.text('مشاهده یا دانلود ضمیمه');
                $card.append($link);
            }

            $issueContainer.append($card);
        });
    }

    function populateResponses(responses, userFullName, issueDescription) {
        const $responsesContainer = $('#responses-container').empty();
        const visibleResponses = (responses || []).filter(
            response => !(issueDescription && response.author === 'user' && response.message === issueDescription)
        );
        if (visibleResponses.length > 0) {
            visibleResponses.forEach(response => {
                const author = response.author === 'admin' ? 'پشتیبان' : userFullName;
                $responsesContainer.append(
                    `<p class="border border-gray-200 p-3 rounded"><strong>${author}:</strong><br>${response.message}</p>`
                );
            });
            applyResponsesMaxHeight($responsesContainer);
        } else {
            $responsesContainer.append('<p class="text-gray-500">هیچ پاسخی ثبت نشده است.</p>');
            applyResponsesMaxHeight($responsesContainer);
        }
    }

    function setTicketLoadingState() {
        $('#ticket-number').text('...');
        $('#ticket-status').text('...');
        $('#order-number').text('...');
        $('#order-date').text('...');
        $('#ticket-owner').text('');
        $('#issue-items-container').html('<p class="text-gray-500">در حال دریافت جزئیات...</p>');
        $('#responses-container').html('<p class="text-gray-500">در حال دریافت پاسخ‌ها...</p>');
    }

    function openTicketPopup(details, ticketId) {
        $('#ticket-id').val(ticketId);
        $('#ticket-number').text(details.ticket_number || '-');
        $('#ticket-status').text(details.status || '-');
        $('#order-number').text(details.order_number || '-');
        $('#order-date').text(details.order_date || '-');
        $('#ticket-owner').text(details.user_full_name ? `ثبت‌کننده: ${details.user_full_name}` : '');

        populateIssueItems(details.issue_items, details.issue_description);
        populateResponses(details.responses, details.user_full_name || 'کاربر', details.issue_description || '');

        $('#ticket-popup').removeClass('hidden');

        setTimeout(function() {
            const $responsesContainer = $('#responses-container');
            if ($responsesContainer.length) {
                $responsesContainer.scrollTop($responsesContainer[0].scrollHeight);
            }
        }, 100);
    }

    // Handle popup open
    $('.view-ticket').on('click', function() {
        const ticketId = $(this).data('ticket-id');
        const ticketNonce = $(this).data('ticket-nonce');

        $('#ticket-popup').removeClass('hidden');
        setTicketLoadingState();

        $.ajax({
            url: ajaxurl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'get_ticket_details',
                ticket_id: ticketId,
                nonce: ticketNonce
            },
            success: function(response) {
                if (response.success) {
                    openTicketPopup(response.data, ticketId);
                } else {
                    $('#issue-items-container').html('<p class="text-red-600">خطا در دریافت جزئیات درخواست.</p>');
                }
            },
            error: function() {
                $('#issue-items-container').html('<p class="text-red-600">خطا در ارتباط با سرور.</p>');
            }
        });
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
