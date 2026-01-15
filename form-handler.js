jQuery(document).on('submit_success', function(e, form) {
    if (form.find('[name="form_name"]').val() === 'order_form') {
        jQuery.post(alfiesAjax.ajaxurl, {
            action: 'submit_alfies_order',
            ...form.serializeArray()
        });
    }
});