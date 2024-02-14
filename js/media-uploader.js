jQuery(document).ready(function($) {
    $('#upload_dashboard_logo_button').click(function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
            title: 'Upload Dashboard Logo',
            button: {
                text: 'Choose Logo'
            },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#mk_dashboard_logo').val(attachment.url);
        });
        mediaUploader.open();
    });
});
