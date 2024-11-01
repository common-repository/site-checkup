<?php
if (!defined('ABSPATH')) {
    exit;
}
// General functions file
// Function to create the administration menu
function site_checkup_admin_menu()
{
    // Add a submenu page under "Tools"
    add_management_page(
        'Site Checkup',
        'Site Checkup',
        'manage_options',
        'site-checkup', // slug
        'site_checkup_page_content'
    );
}

add_action('admin_menu', 'site_checkup_admin_menu');
