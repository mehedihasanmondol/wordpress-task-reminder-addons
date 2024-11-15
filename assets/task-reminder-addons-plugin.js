// SLIDERS SIMPLE-FADE
// parámetros: (claseSlider, intervalo)
jQuery(document).ready(function($) {
    $('#submit-work-button').on('click', function() {
        console.log("dkfdkfkdjf");
            $.ajax({
                url: submitWorkAjax.ajax_url, // Use the localized AJAX URL
                type: 'POST',
                data: {
                    action: 'submit_work_action',
                    post_id: submitWorkAjax.post_id // Use the localized post ID
                },
                success: function(response) {
                    alert('Work submitted successfully.');
                },
                error: function() {
                    alert('There was an error submitting the work.');
                }
            });
        });

});