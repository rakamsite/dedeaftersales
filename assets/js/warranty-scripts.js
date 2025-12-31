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

    updateCategorySections();
});
