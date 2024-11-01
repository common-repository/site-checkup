jQuery(document).ready(function ($) {

    // console.log("site-checkup");
    jQuery('#bill-install-plugins').on('click', function () {
        // Aqui você pode adicionar a lógica que deseja executar quando o botão for clicado
        //alert('1');
        $('#modal-overlay').fadeIn(300); // Exibe o fundo semitransparente
        $('#modal-container-install').slideDown(400); // Mostra o modal com slide

        var nonce = jQuery('#nonce').val();
        // alert(nonce);
        var slug = jQuery('#slug').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'site_checkup_install_plugin',
                slug: slug,
                nonce: nonce
            },
            success: function (response) {
                console.log(response);
                var main_slug = jQuery('#main_slug').val();
                //var slug = slug;
                //if (response.trim() === 'OK') {
                if (response.includes('OK')) {
                    jQuery('#bill_imagewait').hide();
                    var msg = "Plugin " + slug + " Installed Successfully. Go to Plugin's page and activate it!";
                    alert(msg);
                    //$('body').showToast('WPtools Installed Successfully!', 5000, 'ok');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error while installing the plugin.:', error);
                alert('An error occurred while installing the plugin. Please try again later.');
            },
            complete: function () {
                console.log('Complete');
                $('#modal-container-install').remove(); // Remove o modal da página
                $('#bill-install-plugins').remove(); // Remove o modal da página
                $('#help-message').remove(); // Remove o modal da página
            }
        });
    });
    // Close
});  