<?php

/**
 * Plugin Name: Site Checkup
 * Description: Make Site Checkup
 * Version: 1.07
 * Text Domain: site-checkup
 * Author: Bill Minozzi
 * WordPress username: sminozzi
 * Author URI: http://billminozzi.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {
    exit;
}
$sitecheckup_plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$sitecheckup_plugin_version = $sitecheckup_plugin_data['Version'];
define('SITECHECKUPPATH', plugin_dir_path(__FILE__));
define('SITECHECKUPURL', plugin_dir_url(__FILE__));
define('SITECHECKUPVERSION', $sitecheckup_plugin_version);
$site_checkup_is_admin = site_checkup_check_wordpress_logged_in_cookie();

// Add a link to the plugin description on the plugins page
// add_filter('plugin_row_meta', 'site_checkup_plugin_row_meta', 10, 2);
add_filter('plugin_action_links', 'site_checkup_plugin_action_links', 10, 2);


function site_checkup_plugin_row_meta($links, $file)
{
    // Check if this is our plugin
    //
    //
    if (strpos($file, 'site-checkup.php') !== false) {
        $links[] = '<a href="' . esc_url(admin_url('tools.php?page=site-checkup')) . '">Go to Plugin Dashboard</a>';
    }
    return $links;
}



function site_checkup_plugin_action_links($links, $file)
{
    if (strpos($file, 'site-checkup.php') !== false) {
        /////$settings_link = '<a href="' . esc_url(admin_url('tools.php?page=site-checkup')) . '">' . esc_attr__('Dashboard', 'site-checkup') . '</a>';
        // https://minozzi.eu/wp-admin/tools.php?page=site-checkup&customize_changeset_uuid=
        /////array_unshift($links, $settings_link);
        $links[] = '<a href="' . esc_url(admin_url('tools.php?page=site-checkup')) . '">Go to Plugin Dashboard</a>';
    }
    return $links;
}







function site_checkup_check_wordpress_logged_in_cookie()
{
    foreach ($_COOKIE as $key => $value) {
        if (strpos($key, 'wordpress_logged_in_') === 0) {
            return true;
        }
    }
    return false;
}
//
//
function site_checkup_bill_hooking_diagnose()
{
    global $site_checkup_is_admin;
    if ($site_checkup_is_admin and current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "Bill_Diagnose") !== false) {
                return;
            }
        }
        $plugin_slug = 'site-checkup';
        $plugin_text_domain = $plugin_slug;
        $notification_url = "https://wpmemory.com/fix-low-memory-limit/";
        $notification_url2 =
            "https://wptoolsplugin.com/site-language-error-can-crash-your-site/";
        require_once dirname(__FILE__) . "/includes/diagnose/class_bill_diagnose.php";
    }
}
add_action("init", "site_checkup_bill_hooking_diagnose", 10);
function site_checkup_bill_hooking_catch_errors()
{
    global $site_checkup_plugin_slug;
    $declared_classes = get_declared_classes();
    foreach ($declared_classes as $class_name) {
        if (strpos($class_name, "bill_catch_errors") !== false) {
            return;
        }
    }
    $site_checkup_plugin_slug = 'site_checkup';
    require_once dirname(__FILE__) . "/includes/catch-errors/class_bill_catch_errors.php";
}
add_action("init", "site_checkup_bill_hooking_catch_errors", 15);
require_once plugin_dir_path(__FILE__) . 'functions/functions.php';
require_once plugin_dir_path(__FILE__) . 'dashboard/dashboard.php';

function site_checkup_localization_init()
{
    $path = SITECHECKUPPATH . 'language/';
    $locale = apply_filters('plugin_locale', determine_locale(), 'site-checkup');

    // Caminho completo do arquivo de tradução específico (e.g., es_AR.mo)
    $specific_translation_path = $path . "site-checkup-$locale.mo";
    $specific_translation_loaded = false;



    // Verificar se o arquivo de tradução específico existe e tentar carregá-lo
    if (file_exists($specific_translation_path)) {
        $specific_translation_loaded = load_textdomain('site-checkup', $specific_translation_path);
        if ($specific_translation_loaded) {
            //echo 'Specific translation loaded successfully.<br>';
        } else {
            // echo 'Failed to load specific translation.<br>';
        }
    } else {
        //echo 'Specific translation file does not exist.<br>';
    }

    // Lista de idiomas que devem ter fallback para um local específico
    $fallback_locales = [
        'de' => 'de_DE',  // Alemão
        'fr' => 'fr_FR',  // Francês
        'it' => 'it_IT',  // Italiano
        'es' => 'es_ES',  // Espanhol
        'pt' => 'pt_BR',  // Português (fallback para Brasil)
        'nl' => 'nl_NL'   // Holandês (fallback para Holanda)
    ];

    // Se a tradução específica não foi carregada, tenta o fallback
    if (!$specific_translation_loaded) {
        $language = explode('_', $locale)[0];  // Pegar apenas o código da língua, ignorando o país (e.g., es de es_AR)
        //echo 'Fallback language code: ' . esc_html($language) . '<br>';

        if (array_key_exists($language, $fallback_locales)) {
            // Caminho completo do arquivo de fallback genérico (e.g., es_ES.mo)
            $fallback_translation_path = $path . "site-checkup-{$fallback_locales[$language]}.mo";

            // Verificar se o arquivo de fallback existe e tentar carregá-lo
            if (file_exists($fallback_translation_path)) {
                // echo 'Fallback translation file exist.<br>';

                $fallback_loaded = load_textdomain('site-checkup', $fallback_translation_path);
                if ($fallback_loaded) {
                    //echo 'Fallback translation loaded successfully.<br>';
                } else {
                    //echo 'Failed to load fallback translation.<br>';
                }
            } else {
                //echo 'Fallback translation file does not exist.<br>';
            }
        } else {
            //echo 'No fallback language available.<br>';
        }
    }

    echo '</pre>';
}


if ($site_checkup_is_admin) {
    add_action('plugins_loaded', 'site_checkup_localization_init');
    add_action('wp_ajax_site_checkup_install_plugin', 'site_checkup_install_plugin');
}

function site_checkup_admin_enqueue_scripts()
{
    wp_register_script('bill-feedback-site-checkup-js', SITECHECKUPURL . 'includes/feedback/activated-manager.js', array('jquery'), SITECHECKUPVERSION, true);
    wp_enqueue_script('bill-feedback-site-checkup-js');
}

add_action('admin_enqueue_scripts', 'site_checkup_admin_enqueue_scripts');

function site_checkup_admin_enqueue_styles_dashboard()
{

    wp_enqueue_style('site-checkup-custom-css', plugins_url('assets/css/custom-style-dashboard.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'site_checkup_admin_enqueue_styles_dashboard');


function site_checkup_install_plugin()
{


    if (!current_user_can('manage_options')) {
        // O usuário é administrador, execute o código aqui
        die("User not admin!");
    }





    if (isset($_POST['nonce'])) {
        $nonce = sanitize_text_field($_POST['nonce']);
        if (! wp_verify_nonce($nonce, 'sitecheckup-nonce'))
            die('Bad Nonce');
    } else
        wp_die('nonce not set');


    if (isset($_POST['slug'])) {
        $slug = sanitize_text_field($_POST['slug']);
    } else {
        echo 'Fail error (-5)';
        wp_die();
    }

    if ($slug != "antibots" && $slug != "site-checkup" && $slug != "database-backup" &&  $slug != "bigdump-restore" &&  $slug != "easy-update-urls" &&  $slug != "s3cloud" &&  $slug != "toolsfors3" && $slug != "antihacker" && $slug != "toolstruthsocial" && $slug != "stopbadbots" && $slug != "wptools" && $slug != "recaptcha-for-all" && $slug != "wp-memory") {
        wp_die('wrong slug');
    }

    $plugin['source'] = 'repo'; // $_GET['plugin_source']; // Plugin source.
    require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api.
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.
    // get plugin information
    $api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false)));
    if (is_wp_error($api)) {
        echo 'Fail error (-1)';
        wp_die();
        // proceed
    } else {
        // Set plugin source to WordPress API link if available.
        if (isset($api->download_link)) {
            $plugin['source'] = $api->download_link;
            $source =  $api->download_link;
        } else {
            echo 'Fail error (-2)';
            wp_die();
        }
        $nonce = 'install-plugin_' . $api->slug;
        /*
        $type = 'web';
        $url = $source;
        $title = 'wptools';
        */
        $plugin = $slug;
        // verbose...
        //    $upgrader = new Plugin_Upgrader($skin = new Plugin_Installer_Skin(compact('type', 'title', 'url', 'nonce', 'plugin', 'api')));
        class site_checkup_QuietSkin extends \WP_Upgrader_Skin
        {
            public function site_checkup_feedback($string, ...$args)
            { /* no output */
            }
            public function site_checkup_header()
            { /* no output */
            }
            public function site_checkup_footer()
            { /* no output */
            }
        }
        $skin = new site_checkup_QuietSkin(array('api' => $api));
        $upgrader = new Plugin_Upgrader($skin);
        // var_dump($upgrader);
        try {
            $upgrader->install($source);
            //	get all plugins
            $all_plugins = get_plugins();
            // scan existing plugins
            foreach ($all_plugins as $key => $value) {
                // get full path to plugin MAIN file
                // folder and filename
                $plugin_file = $key;
                $slash_position = strpos($plugin_file, '/');
                $folder = substr($plugin_file, 0, $slash_position);
                // match FOLDER against SLUG
                // if matched then ACTIVATE it
                if ($slug == $folder) {
                    /*
					// Activate
					$result = activate_plugin(ABSPATH . 'wp-content/plugins/' . $plugin_file);
					if (is_wp_error($result)) {
						// Process Error
						echo 'Fail error (-3)';
						wp_die();
					}
					*/
                } // if matched
            }
        } catch (Exception $e) {
            echo 'Fail error (-4)';
            wp_die();
        }
    } // activation
    echo 'OK';
    wp_die();
}
//
// Hook that triggers when the plugin is activated
register_activation_hook(__FILE__, 'site_checkup_plugin_activate');
function site_checkup_plugin_activate() {
    // Add an option to indicate if the pointer has been shown
    update_option('site_checkup_pointer_dismissed', false);
}

// Hook that triggers when the plugin is deactivated
register_deactivation_hook(__FILE__, 'site_checkup_plugin_deactivate');
function site_checkup_plugin_deactivate() {
    // Reset the option to ensure the pointer appears on next activation
    delete_option('site_checkup_pointer_dismissed');
}

// Add a hook for admin script loading
add_action('admin_enqueue_scripts', 'site_checkup_enqueue_pointer_scripts');
function site_checkup_enqueue_pointer_scripts() {
    // Check if the pointer has already been shown
    $pointer_dismissed = get_option('site_checkup_pointer_dismissed');

    // If the pointer hasn't been shown, register the necessary scripts
    if (!$pointer_dismissed) {
        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('wp-pointer');
        add_action('admin_print_footer_scripts', 'site_checkup_show_pointer');
    }
}

// Function that displays the pointer
function site_checkup_show_pointer() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Set the pointer content
            var content = '<h3><?php echo esc_attr__("Check the Tools Menu", "site-checkup"); ?></h3>';
            content += '<p><?php echo esc_attr__("Click here to access the Site Checkup dashboard.", "site-checkup"); ?></p>';

            // Point to the "Tools" menu in the admin
            $('#menu-tools').pointer({
                content: content,
                position: {
                    edge: 'left', // Point from the left
                    align: 'center' // Aligned to the center
                },
                close: function() {
                    // When the pointer is closed, update the option so it won't show again
                    $.post(ajaxurl, {
                        pointer: 'site_checkup_pointer',
                        action: 'dismiss_wp_pointer'
                    });
                }
            }).pointer('open');
        });
    </script>
    <?php
}

// Hook to update the option when the pointer is closed
add_action('wp_ajax_dismiss_wp_pointer', 'site_checkup_dismiss_pointer');
function site_checkup_dismiss_pointer() {
    update_option('site_checkup_pointer_dismissed', true);
}
