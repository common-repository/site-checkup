// File: js/site-checkup-wizard-script.js

jQuery(document).ready(function ($) {
    // Animate the progress bar
    var $progressBar = $('.site-checkup-wizard-progress-bar');
    var targetWidth = $progressBar.data('width');

    $progressBar.css('width', '0%').animate({
        width: targetWidth + '%'
    }, {
        duration: 1000, // Animation duration in milliseconds
        easing: 'swing', // You can change this to 'linear' for a constant speed
        step: function (now) {
            // Update the progress text as the bar animates
            var progressText = Math.round(now) + '%';
            $('.site-checkup-wizard-progress-text').text(progressText);
        }
    });
});