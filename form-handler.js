
jQuery(document).on('submit_success', function(e, form) {
    const $form = jQuery(form);
    if ($form.find('[name="form_name"]').val() === 'order_form') {
        const formData = {};
        $form.serializeArray().forEach(field => {
            formData[field.name] = field.value;
        });

        jQuery.post(alfiesAjax.ajaxurl, {
            action: 'submit_alfies_order',
            ...formData
        });
    }
});