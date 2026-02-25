jQuery(document).ready(function($) {
    var purchaseDatePart = $('#purchase_date_part');
    var purchaseDateTool = $('#purchase_date_tool');
    var installationDate = $('#installation_date');

    if (purchaseDatePart.length) {
        purchaseDatePart.persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            toolbox: {
                calendarSwitch: {
                    enabled: false
                }
            }
        });
    }

    if (purchaseDateTool.length) {
        purchaseDateTool.persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            toolbox: {
                calendarSwitch: {
                    enabled: false
                }
            }
        });
    }

    if (installationDate.length) {
        installationDate.persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            toolbox: {
                calendarSwitch: {
                    enabled: false
                }
            }
        });
    }

    function toggleSection($section, isActive) {
        $section.toggleClass('hidden', !isActive);
        $section.find('input, select, textarea').each(function() {
            var $field = $(this);
            $field.prop('disabled', !isActive);
            if ($field.data('required')) {
                $field.prop('required', isActive);
            }
            if (!isActive && ($field.is('input') || $field.is('textarea'))) {
                $field.val('');
            }
        });
    }

    function updateCategorySections() {
        var category = $('input[name="product_category"]:checked').val();
        toggleSection($('#warranty-section-part'), category === 'part');
        toggleSection($('#warranty-fields-part'), category === 'part');
        toggleSection($('#warranty-section-tool'), category === 'tool');
        toggleSection($('#warranty-fields-tool'), category === 'tool');
    }

    $('input[name="product_category"]').on('change', updateCategorySections);

    var hologramDigits = $('.hologram-digit');
    var hologramCodeField = $('#hologram_code');

    function updateHologramCode() {
        var code = '';

        hologramDigits.each(function() {
            code += $(this).val();
        });

        hologramCodeField.val(code);
    }

    hologramDigits.on('input', function() {
        var $current = $(this);
        var value = $current.val().replace(/\D/g, '');

        $current.val(value);

        if (value) {
            $current.next('.hologram-digit').focus();
        }

        updateHologramCode();
    });

    hologramDigits.on('keydown', function(event) {
        var $current = $(this);

        if (event.key === 'Backspace' && !$current.val()) {
            $current.prev('.hologram-digit').focus();
        }
    });

    hologramDigits.on('paste', function(event) {
        var pastedData = (event.originalEvent.clipboardData || window.clipboardData).getData('text');
        var digits = pastedData.replace(/\D/g, '').slice(0, hologramDigits.length).split('');

        if (!digits.length) {
            return;
        }

        event.preventDefault();

        hologramDigits.each(function(index) {
            $(this).val(digits[index] || '');
        });

        var focusIndex = Math.min(digits.length, hologramDigits.length - 1);
        hologramDigits.eq(focusIndex).focus();
        updateHologramCode();
    });

    updateCategorySections();
    updateHologramCode();
});
