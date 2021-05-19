<?php

namespace LitePress\WP_China_Yes\Inc;

function get_curl_version() {
    $curl_version = '1.0.0';

    if ( function_exists( 'curl_version' ) ) {
        $curl_version_array = curl_version();
        if ( is_array( $curl_version_array ) && key_exists( 'version', $curl_version_array ) ) {
            $curl_version = $curl_version_array['version'];
        }
    }

    return $curl_version;
}

function replace_page_str( $replace_func, $param ) {
    ob_start( function ( $buffer ) use ( $replace_func, $param ) {
        $param[] = $buffer;

        return call_user_func_array( $replace_func, $param );
    } );
}

function get_options() {
    $args = get_option( 'wp-china-yes', array() );

    $defaults = array(
        'wpapi_replacement_mode'  => Core::LPAPI,
        'is_replace_gravatar'     => Switch_Status::ON,
        'is_replace_admin_assets' => Switch_Status::OFF,
        'is_replace_googleajax'   => Switch_Status::OFF,
        'is_replace_googlefonts'  => Switch_Status::OFF,
    );

    return wp_parse_args( $args, $defaults );
}

function update_option( $args ) {
    \update_option( 'wp-china-yes', $args );
}
