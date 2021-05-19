<?php
/**
 * 插件装载文件
 *
 * @package WP_China_Yes
 */

namespace LitePress\WP_China_Yes;

use LitePress\WP_China_Yes\Inc\Core;
use function LitePress\WP_China_Yes\Inc\get_options;

require_once 'config.php';
require_once 'inc/functions.php';
require_once 'inc/class-switch-status.php';
require_once 'inc/class-core.php';
require_once 'inc/settings.php';

$options = get_options();
$core    = Core::get_instance();
$core->set_wpapi_replacement_mode( $options['wpapi_replacement_mode'] );
$core->set_is_replace_gravatar( $options['is_replace_gravatar'] );
$core->set_is_replace_admin_assets( $options['is_replace_admin_assets'] );
$core->set_is_replace_googleajax( $options['is_replace_googleajax'] );
$core->set_is_replace_googlefonts( $options['is_replace_googlefonts'] );
$core->register_hook();
