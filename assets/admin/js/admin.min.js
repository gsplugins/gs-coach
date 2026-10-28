jQuery(function($) {

    let isProActive = (gsCoachAdminData.is_pro_active !== '') && (gsCoachAdminData.is_pro_active === '1');
    let isProValid = (gsCoachAdminData.is_pro_valid !== '') && (gsCoachAdminData.is_pro_valid === '1');

    if ( ! isProActive || ! isProValid ) {
        $('.gs-coach-pro-field').addClass('gs-coach-fields-disable').append('<div class="gs-coach-pro-field--inner"><div class="gs-coach-pro-field--content"><a href="https://www.gsplugins.com/product/wordpress-coaches-plugin/pricing/?utm_source=plugin&utm_medium=admin-js&utm_campaign=wordpress-coaches-plugin">Upgrade to PRO</a></div></div>');
    }

    /*
     * Select/Upload image(s) event
     */
    $('body').on('click', '.gs_upload_image_button', function(e){
        e.preventDefault();
 
        var button = $(this),
            custom_uploader = wp.media({
            title: 'Insert image',
            library : {
                // uncomment the next line if you want to attach image to the current post
                // uploadedTo : wp.media.view.settings.post.id, 
                type : 'image'
            },
            button: {
                text: 'Use this image' // button label text
            },
            multiple: false // for multiple image selection set to true
        }).on('select', function() { // it also has "open" and "close" events 
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $(button).removeClass('button').html('<img class="true_pre_image" src="' + attachment.url + '" />').next().val(attachment.id).next().show();
            /* if you sen multiple to true, here is some code for getting the image IDs
            var attachments = frame.state().get('selection'),
                attachment_ids = new Array(),
                i = 0;
            attachments.each(function(attachment) {
                attachment_ids[i] = attachment['id'];
                console.log( attachment );
                i++;
            });
            */
        })
        .open();
    });
 
    /*
     * Remove image event
     */
    $('body').on('click', '.gs_remove_image_button', function(){
        $(this).hide().prev().val('').prev().addClass('button').html('Upload image');
        return false;
    });


    $('.gscoach-icon-select').select2({
        width: "100%",
        templateResult: gscoach_formaticon,
        templateSelection: gscoach_formaticon
    });

    $('.gscoach-add-row').on('click', function(e) {
        e.preventDefault();
        var table = $(this).data('table'),
            row = $('#' + table + ' .empty-row.screen-reader-text').clone(true);
        row.removeClass('empty-row screen-reader-text');
        row.insertBefore('#' + table + ' tbody>tr:last');
        row.find('select').select2({
            width: "100%",
            templateResult: gscoach_formaticon,
            templateSelection: gscoach_formaticon
        });
        return false;
    });

    $('.gscoach-add-skill').on('click', function(e) {
        e.preventDefault();
        var skill = $(this).data('table'),
            row = $('#' + skill + ' .empty-skill.screen-reader-text').clone(true);
        row.removeClass('empty-skill screen-reader-text');
        row.insertBefore('#' + skill + ' tbody>tr:last');
        row.find('select').select2({
            width: "100%",
            templateResult: gscoach_formaticon,
            templateSelection: gscoach_formaticon
        });
        return false;
    });

    // remove
    $('.remove-row').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });
    
    $('.gscoach-sorable-table tbody').sortable({
        items: "tr",
        axis: "y",
        cursor: 'move',
    });
   
    function gscoach_formaticon(icon) {
        if (!icon.id) {
            return icon.text; }
        var $icon = $(
            '<span><i class="' + icon.text.toLowerCase() + '"></i>  ' + icon.text.replace(/fa-|fab|fas|far/g, '').trim().replaceAll('-', ' ') + '</span>'
        );
        return $icon;
    }

    $("#gsCoachAvailable").flatpickr({
        mode: "range",
        altInput: true,
        altFormat: "F j, Y",
        altInputClass: "gs-flatpickr-alt form-control input",
        onReady: function(selectedDates, dateStr, instance) {
            // Add custom ID when alt input is ready
            instance.altInput.id = "gsCoachAvailableField";
        },
        dateFormat: "F j, Y"
    });

});