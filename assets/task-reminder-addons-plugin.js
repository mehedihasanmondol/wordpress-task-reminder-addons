// SLIDERS SIMPLE-FADE
// parámetros: (claseSlider, intervalo)
jQuery(document).ready(function($) {

    $(".notice-dismiss").click(function () {
        $(this).closest(".notice.is-dismissible").remove();
    })

    $('#submit-work-button').on('click', function() {
            $.ajax({
                url: submitWorkAjax.ajax_url, // Use the localized AJAX URL
                type: 'POST',
                data: {
                    action: 'submit_work_action',
                    post_id: submitWorkAjax.post_id // Use the localized post ID
                },
                success: function(response) {
                    alert('Work submitted successfully.');
                    window.location.reload();
                },
                error: function() {
                    alert('There was an error submitting the work.');
                }
            });
        });

        

});
