jQuery(document).ready(function($) {
    $('#appointment-booking-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                $('#booking-result').html('<p>' + response.data.message + '</p>');
            } else {
                $('#booking-result').html('<p>Booking failed. Please try again.</p>');
            }
        });
    });
});

jQuery(document).ready(function($) {
    console.log('load');
    $('#service-select').on('change', function() {
        const selectedService = $(this).find('option:selected').text();
        const price = $(this).find('option:selected').data('price');
        $('#selected-service').text(selectedService);
        $('#selected-price').text(price);
    });

    $('[name="booking_date"]').on('change', function() {
        $('#selected-date').text($(this).val());
    });

    $('[name="booking_time"]').on('change', function() {
        $('#selected-time').text($(this).val());
    });
});




