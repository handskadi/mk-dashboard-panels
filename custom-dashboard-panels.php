<?php
/*
Plugin Name: MK Dashboard Panels
Description: Adds custom panels to the WordPress dashboard for selected user roles and disables other panels. Also allows hiding Jetpack menu item and customizing welcome message and logo.
Version: 1.0
Author: Mohamed KADI
Author URI: https://mohamedkadi.com
Plugin URI: https://mohamedkadi.com/project/mk-dashboard-panels
License: GPL v2 or later
Tested up to: 6.4.3
Requires at least: 6.0.0
*/

// Hook activation and deactivation functions
register_activation_hook(__FILE__, 'mk_dashboard_panels_activate');
register_deactivation_hook(__FILE__, 'mk_dashboard_panels_deactivate');

// Function to execute on plugin activation
function mk_dashboard_panels_activate() {
    // Add any activation tasks here
}

// Function to execute on plugin deactivation
function mk_dashboard_panels_deactivate() {
    // Add any deactivation tasks here
}

// Add custom dashboard panels based on selected user roles and disable other panels
function mk_add_dashboard_panels() {
    $selected_role = get_option('mk_dashboard_selected_role', '');
    $disable_other_panels = get_option('mk_dashboard_disable_other_panels', false);
    $welcome_message = get_option('mk_dashboard_welcome_message', '');
    $dashboard_logo = get_option('mk_dashboard_logo', '');
    $site_title = get_option('mk_dashboard_site_title', '');

    // Check if the current user has the selected role
    $current_user = wp_get_current_user();
    if (!in_array($selected_role, $current_user->roles)) {
        return; // Exit if the current user does not have the selected role
    }

    wp_add_dashboard_widget(
        'mk_custom_dashboard_panel_id',    // Widget slug
        'MK Custom Dashboard Panel',       // Widget title
        'mk_dashboard_panel_content'       // Callback function to display content
    );

    // Disable other dashboard panels if the option is enabled
    if ($disable_other_panels) {
        // Remove default dashboard widgets except for the custom one
        $default_widgets = array(
            'dashboard_activity',
            'dashboard_right_now',
            'dashboard_recent_comments',
            'dashboard_incoming_links',
            'dashboard_plugins',
            'dashboard_quick_press',
            'dashboard_recent_drafts',
            'dashboard_primary',
            'dashboard_secondary'
        );
        foreach ($default_widgets as $widget) {
            remove_meta_box($widget, 'dashboard', 'normal');
        }
        
        // Remove WooCommerce and WordPress Events and News widgets
        remove_meta_box('woocommerce_dashboard_status', 'dashboard', 'normal');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
    }

    // Remove Jetpack and Marketing menu items
    remove_menu_page('jetpack');
    remove_menu_page('woocommerce-marketing');

    // Display custom welcome header
    echo '<div style="text-align: center;">';
    if (!empty($dashboard_logo)) {
        echo '<img src="' . esc_url($dashboard_logo) . '" alt="Dashboard Logo" style="max-width: 100px; height: auto;" /><br>';
    }
    if (!empty($site_title)) {
        echo '<h2>' . esc_html($site_title) . '</h2>';
    }
    if (!empty($welcome_message)) {
        echo '<p>' . esc_html($welcome_message) . '</p>';
    }
    echo '</div>';
}
add_action('wp_dashboard_setup', 'mk_add_dashboard_panels');


// Remove Screen Options and Help tabs if enabled
function mk_remove_dashboard_tabs() {
    $disable_dashboard_tabs = get_option('mk_dashboard_disable_tabs', false);
    $selected_role = get_option('mk_dashboard_selected_role', '');

    // Check if the current user has the selected role
    $current_user = wp_get_current_user();
    if ($disable_dashboard_tabs && in_array($selected_role, $current_user->roles)) {
        echo '<style>#screen-meta, #screen-options-link-wrap, #contextual-help-link-wrap { display: none !important; }</style>';
    }
}
add_action('admin_head', 'mk_remove_dashboard_tabs');

// Function to display content of custom dashboard panel
function mk_dashboard_panel_content() {
    // Content of your custom dashboard panel
    echo '<p>This is a custom dashboard panel content.</p>';
}

// Add settings link on plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mk_dashboard_settings_link');
function mk_dashboard_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=mk-dashboard-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Add settings page to allow selection of user role and enable/disable other panels
function mk_dashboard_settings_page() {
    add_options_page(
        'MK Dashboard Settings',
        'MK Dashboard Settings',
        'manage_options',
        'mk-dashboard-settings',
        'mk_dashboard_settings_page_content'
    );
}
add_action('admin_menu', 'mk_dashboard_settings_page');

// Function to render settings page content
function mk_dashboard_settings_page_content() {
    $all_roles = wp_roles()->get_names();
    $selected_role = get_option('mk_dashboard_selected_role', '');
    $disable_other_panels = get_option('mk_dashboard_disable_other_panels', false);
    $hide_jetpack_menu = get_option('mk_dashboard_hide_jetpack_menu', false);
    $welcome_message = get_option('mk_dashboard_welcome_message', '');
    $dashboard_logo = get_option('mk_dashboard_logo', '');
    $site_title = get_option('mk_dashboard_site_title', '');
    $disable_dashboard_tabs = get_option('mk_dashboard_disable_tabs', false);

    echo '<div class="wrap">';
    echo '<h1>MK Dashboard Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('mk_dashboard_settings');
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row">Select User Role</th>';
    echo '<td>';
    echo '<select name="mk_dashboard_selected_role">';
    foreach ($all_roles as $role_key => $role_name) {
        echo '<option value="' . esc_attr($role_key) . '" ' . selected($role_key, $selected_role, false) . '>' . esc_html($role_name) . '</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Disable other panels</th>';
    echo '<td><input type="checkbox" name="mk_dashboard_disable_other_panels" value="1" ' . checked($disable_other_panels, true, false) . ' /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Hide Jetpack Menu</th>';
    echo '<td><input type="checkbox" name="mk_dashboard_hide_jetpack_menu" value="1" ' . checked($hide_jetpack_menu, true, false) . ' /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Welcome Message</th>';
    echo '<td><input type="text" name="mk_dashboard_welcome_message" value="' . esc_attr($welcome_message) . '" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Dashboard Logo</th>';
    echo '<td>';
    echo '<input type="text" name="mk_dashboard_logo" id="mk_dashboard_logo" value="' . esc_attr($dashboard_logo) . '" />';
    echo '<input type="button" id="upload_dashboard_logo_button" class="button" value="Upload Image" />';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Site Title</th>';
    echo '<td><input type="text" name="mk_dashboard_site_title" value="' . esc_attr($site_title) . '" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Disable Screen Options and Help Tabs</th>';
    echo '<td><input type="checkbox" name="mk_dashboard_disable_tabs" value="1" ' . checked($disable_dashboard_tabs, true, false) . ' /></td>';
    echo '</tr>';
    echo '</table>';
    echo submit_button('Save Settings');
    echo '</form>';
    echo '</div>';
}

// Register settings
function mk_dashboard_register_settings() {
    register_setting('mk_dashboard_settings', 'mk_dashboard_selected_role');
    register_setting('mk_dashboard_settings', 'mk_dashboard_disable_other_panels', 'intval');
    register_setting('mk_dashboard_settings', 'mk_dashboard_hide_jetpack_menu', 'intval');
    register_setting('mk_dashboard_settings', 'mk_dashboard_welcome_message', 'sanitize_text_field');
    register_setting('mk_dashboard_settings', 'mk_dashboard_logo', 'esc_url');
    register_setting('mk_dashboard_settings', 'mk_dashboard_site_title', 'sanitize_text_field');
    register_setting('mk_dashboard_settings', 'mk_dashboard_disable_tabs', 'intval');
}
add_action('admin_init', 'mk_dashboard_register_settings');

// Enqueue media uploader script
function mk_dashboard_media_uploader_script() {
    wp_enqueue_media();
    wp_enqueue_script('mk-dashboard-media-uploader', plugin_dir_url(__FILE__) . 'js/media-uploader.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'mk_dashboard_media_uploader_script');
