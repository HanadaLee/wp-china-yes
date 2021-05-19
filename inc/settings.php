<?php

namespace LitePress\WP_China_Yes\Inc;

add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', function () {
    add_submenu_page(
        is_multisite() ? 'settings.php' : 'options-general.php',
        'WP-China-Yes',
        'WP-China-Yes',
        is_multisite() ? 'manage_network_options' : 'manage_options',
        'wp-china-yes',
        'LitePress\WP_China_Yes\Inc\settings_html'
    );
} );

function settings_html() {
    require_once WCY_ROOT_PATH . 'template/settings.php';
}
