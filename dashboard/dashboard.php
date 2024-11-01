<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
require_once SITECHECKUPPATH . 'wizard/wizard.php';
global $site_checkup_label_tabs;
$site_checkup_label_tabs =  [
    esc_html__('Dashboard', 'site-checkup'),
    esc_html__('Wizard', 'site-checkup')
];

function site_checkup_page_content()
{
?>

    <div id="site-checkup-logo-container">
        <img id="site-checkup-logo" src="<?php echo esc_attr(SITECHECKUPURL); ?>/assets/imagens/logo.png" alt="<?php esc_attr_e('Site Checkup Logo', 'site-checkup'); ?>" width="200px">
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'sitecheckup-nonce')) {
            $site_checkup_active_tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : '0';
            switch ($site_checkup_active_tab) {
                case '1':
                    site_checkup_tab_wizard(1);
                    break;
                default:
                    site_checkup_tab_dashboard(0);
                    break;
            }
        } else {
            echo '<p>' . esc_html__('Nonce verification failed!', 'site-checkup') . '</p>';
            site_checkup_tab_dashboard(0);
        }
    } else {
        site_checkup_tab_dashboard(0);
    }
    ?>
<?php
}

// Function to render the Dashboard page
function site_checkup_tab_dashboard($active_tab)
{
    global $site_checkup_label_tabs;
    site_checkup_render_nav_tabs($active_tab); // Pass the active tab
?>



<div class="wrap">
    <h1><?php echo esc_attr__('Site Checkup Wizard Dashboard', 'site-checkup'); ?></h1>

    <p><?php echo esc_attr__('Welcome to the Site Checkup Plugin!', 'site-checkup'); ?></p>
    <p><?php echo esc_attr__('This plugin helps you ensure that your WordPress site is running at peak performance and security levels. Follow the steps below to check your site\'s health.', 'site-checkup'); ?></p>

   

    

    <h2>⚙️ <?php echo esc_attr__('How Does the Site Checkup Wizard Work?', 'site-checkup'); ?></h2>
    <p><?php echo esc_attr__('The wizard is divided into 5 steps covering essential checks for your site:', 'site-checkup'); ?></p>
    <ol>
        <li><strong><?php echo esc_attr__('Check Memory', 'site-checkup'); ?></strong>: <?php echo esc_attr__('Ensure your WordPress site has enough memory to operate smoothly.', 'site-checkup'); ?></li>
        <li><strong><?php echo esc_attr__('Check for Errors', 'site-checkup'); ?></strong>: <?php echo esc_attr__('Check your WordPress and PHP logs for errors, warnings, or notices, including JavaScript errors.', 'site-checkup'); ?></li>
        <li><strong><?php echo esc_attr__('Check Tables', 'site-checkup'); ?></strong>: <?php echo esc_attr__('Verify the health and integrity of your WordPress database tables.', 'site-checkup'); ?></li>
        <li><strong><?php echo esc_attr__('File Permissions', 'site-checkup'); ?></strong>: <?php echo esc_attr__('Ensure that your WordPress files and folders have the correct permissions.', 'site-checkup'); ?></li>
        <li><strong><?php echo esc_attr__('Root Folder Extra Files', 'site-checkup'); ?></strong>: <?php echo esc_attr__('Identify any unnecessary or suspicious files in your root directory.', 'site-checkup'); ?></li>
    </ol>

    
    <?php echo esc_attr__('Click the "Wizard" tab to begin the process. At each step, the wizard will provide personalized recommendations based on your setup.', 'site-checkup'); ?>

    <br> <br>
    <h2>📚 <?php echo esc_attr__('Documentation & Support', 'site-checkup'); ?></h2>
    <p><?php echo esc_attr__('If you need more information or support, visit our', 'site-checkup'); ?> <a href="https://sitecheckup.eu"><?php echo esc_attr__('Official Documentation', 'site-checkup'); ?></a> 
    <hr>

    <br> 
    <h2>🔍 <?php echo esc_attr__('Troubleshooting', 'site-checkup'); ?></h2>
    <p><?php echo esc_attr__('If you encounter any issues, visit our troubleshooting page for assistance.', 'site-checkup'); ?></p>
    <a href="https://siterightaway.net/troubleshooting/" class="button button-primary"><?php echo esc_attr__('Go to Troubleshooting', 'site-checkup'); ?></a>
    </div>



<?php
}

function site_checkup_render_nav_tabs($active_tab)
{
    global $site_checkup_label_tabs;
    echo '<h2 class="nav-tab-wrapper">';
    foreach ($site_checkup_label_tabs as $tab => $label) {
        $active_class = $active_tab === $tab ? ' nav-tab-active' : '';
        echo '<form method="post" action="">';
        wp_nonce_field('sitecheckup-nonce');
        echo '<input type="hidden" name="page" value="sitecheckup">';
        echo '<input type="hidden" name="tab" value="' . esc_attr($tab) . '">';
        echo '<button type="submit" class="nav-tab' . esc_attr($active_class) . '">' . esc_html($label) . '</button>';
        echo '</form>';
    }
    echo '</h2>';
}

/*
function site_checkup_admin_enqueue_styles_dashboard()
{
    wp_enqueue_style('site-checkup-custom-css', SITECHECKUPPATH . 'assets/css/custom-style-dashboard.css');
}
// add_action('wp_enqueue_scripts', 'site_checkup_enqueue_styles_dashboard');
add_action('admin_enqueue_scripts', 'site_checkup_admin_enqueue_styles_dashboard');
*/

//
//
//


//
//

?>