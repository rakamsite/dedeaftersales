jQuery(document).ready(function($) {
    var frame;

    $('.sts-media-upload').on('click', function(e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'انتخاب تصویر هولوگرام',
            button: {
                text: 'استفاده از تصویر'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#sts_hologram_sample_image').val(attachment.url);
            $('.sts-media-preview').html(
                '<img src="' + attachment.url + '" alt="نمونه هولوگرام" style="max-width:200px;margin-top:10px;" />'
            );
        });

        frame.open();
    });
});
