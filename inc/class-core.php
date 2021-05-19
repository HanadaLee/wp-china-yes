<?php
/**
 * 插件核心功能
 */

namespace LitePress\WP_China_Yes\Inc;

use _WP_Dependency;
use WP_Scripts;
use WP_Styles;
use const LitePress\WP_China_Yes\LPAPI_DOWNLOAD_URL;
use const LitePress\WP_China_Yes\LPAPI_URL;
use const LitePress\WP_China_Yes\WPAPI_MIRROR_DOWNLOAD_URL;
use const LitePress\WP_China_Yes\WPAPI_MIRROR_URL;

final class Core {

    const WPAPI = 'wpapi';

    const WPAPI_MIRROR = 'wpapi-mirror';

    const LPAPI = 'lpapi';

    /**
     * @var Core
     */
    private static $instance;

    private $asset_replace_queues;

    /**
     * @var string
     */
    private $wpapi_replacement_mode = self::WPAPI_MIRROR;

    /**
     * @var string
     */
    private $is_replace_googlefonts = '';

    /**
     * @var string
     */
    private $is_replace_googleajax = '';

    /**
     * @var string
     */
    private $is_replace_gravatar = '';

    /**
     * @var string
     */
    private $is_replace_admin_assets = '';

    /**
     * 单例模式下禁用类构造
     */
    private function __construct() {
    }

    /**
     * 单例模式下禁用Clone
     */
    private function __clone() {
    }

    public static function get_instance() {
        if ( ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function register_hook() {
        $this->replace_admin_assets();
        $this->replace_googlefonts();
        $this->replace_googleajax();

        add_filter( 'pre_http_request', array( $this, 'replace_wpapi' ), 1, 3 );

        add_filter( 'get_avatar', array( $this, 'replace_gravatar' ), 1 );
        /** 终极会员插件有自己的头像函数，需要单独替换 */
        add_filter( 'um_user_avatar_url_filter', array( $this, 'replace_gravatar' ), 1 );

        add_action( 'wp_enqueue_scripts', array( $this, 'handle_asset_replace_queues' ), 9999999999999999999999 );
        add_action( 'admin_enqueue_scripts', array( $this, 'handle_asset_replace_queues' ), 9999999999999999999999 );
    }

    public function get_is_replace_googlefonts() {
        return $this->is_replace_googlefonts;
    }

    /**
     * @param $is_replace_googlefonts string
     */
    public function set_is_replace_googlefonts( $is_replace_googlefonts ) {
        $this->is_replace_googlefonts = $is_replace_googlefonts;
    }

    public function get_is_replace_googleajax() {
        return $this->is_replace_googleajax;
    }

    /**
     * @param $is_replace_googleajax string
     */
    public function set_is_replace_googleajax( $is_replace_googleajax ) {
        $this->is_replace_googleajax = $is_replace_googleajax;
    }

    public function get_is_replace_gravatar() {
        return $this->is_replace_gravatar;
    }

    /**
     * @param $is_replace_gravatar string
     */
    public function set_is_replace_gravatar( $is_replace_gravatar ) {
        $this->is_replace_gravatar = $is_replace_gravatar;
    }

    public function get_is_replace_admin_assets() {
        return $this->is_replace_admin_assets;
    }

    /**
     * @param $is_replace_admin_assets string
     */
    public function set_is_replace_admin_assets( $is_replace_admin_assets ) {
        $this->is_replace_admin_assets = $is_replace_admin_assets;
    }

    private function get_asset_replace_queues() {
        return $this->asset_replace_queues;
    }

    public function get_wpapi_replacement_mode() {
        return $this->wpapi_replacement_mode;
    }

    /**
     * @param $mode string
     */
    public function set_wpapi_replacement_mode( $mode ) {
        $this->wpapi_replacement_mode = $mode;
    }

    /**
     * @param $func callable 需要调用的替换函数
     * @param $old string 旧的字符串
     * @param $new string 要替换为的新字符串
     * @param $level int 替换级别 @see Switch_Status
     */
    private function add_asset_replace_queue( $func, $old, $new, $level ) {
        $args = array(
            'func'  => $func,
            'old'   => $old,
            'new'   => $new,
            'level' => $level,
        );

        $this->asset_replace_queues[] = $args;
    }

    public function handle_asset_replace_queues() {
        global $wp_styles;
        global $wp_scripts;

        if ( ! ( $wp_styles instanceof WP_Styles ) || ! ( $wp_scripts instanceof WP_Scripts ) ) {
            return;
        }

        $olds      = array( 'wp_styles' => $wp_styles->registered, 'wp_scripts' => $wp_scripts->registered );
        $olds_text = json_encode( $olds );

        foreach ( $this->get_asset_replace_queues() as $value ) {
            if ( ! Switch_Status::check_status( $value['level'] ) ) {
                continue;
            }

            $olds_text = call_user_func_array( $value['func'], array( $value['old'], $value['new'], $olds_text ) );
        }

        $news = json_decode( $olds_text, true );

        $add_dependency = function ( $value ) {
            $dependency                    = new _WP_Dependency( $value['handle'], $value['src'], $value['deps'], $value['ver'], $value['args'] );
            $dependency->extra             = $value['extra'];
            $dependency->textdomain        = $value['textdomain'];
            $dependency->translations_path = $value['translations_path'];

            return $dependency;
        };

        foreach ( $news['wp_styles'] as $value ) {
            $wp_styles->registered[ $value['handle'] ] = $add_dependency( $value );
        }

        foreach ( $news['wp_scripts'] as $value ) {
            $wp_scripts->registered[ $value['handle'] ] = $add_dependency( $value );
        }
    }

    public function replace_wpapi( $preempt, $r, $url ) {
        if ( ( ! stristr( $url, 'api.wordpress.org' ) && ! stristr( $url, 'downloads.wordpress.org' ) ) ) {
            return false;
        }

        if ( self::WPAPI === $this->get_wpapi_replacement_mode() ) {
            return false;
        }

        if ( self::LPAPI === $this->get_wpapi_replacement_mode() ) {
            $url = str_replace( '//api.wordpress.org', LPAPI_URL, $url );
            $url = str_replace( '//downloads.wordpress.org', LPAPI_DOWNLOAD_URL, $url );
        } else {
            $url = str_replace( '//api.wordpress.org', WPAPI_MIRROR_URL, $url );
            $url = str_replace( '//downloads.wordpress.org', WPAPI_MIRROR_DOWNLOAD_URL, $url );
        }

        // 如果CURL版本小于7.15.0，说明不支持SNI，无法通过HTTPS访问又拍云的节点，故而改用HTTP
        if ( version_compare( get_curl_version(), '7.15.0', '<' ) ) {
            $url = str_replace( 'https://', 'http://', $url );
        }

        return wp_remote_request( $url, $r );
    }

    public function replace_googlefonts() {
        $this->add_asset_replace_queue( 'str_replace', 'fonts.googleapis.com', 'googlefonts.wp-china-yes.net', $this->get_is_replace_googlefonts() );
    }

    public function replace_googleajax() {
        $this->add_asset_replace_queue( 'str_replace', 'ajax.googleapis.com', 'googleajax.wp-china-yes.net', $this->get_is_replace_googleajax() );
    }

    public function replace_gravatar( $avatar ) {
        if ( ! Switch_Status::check_status( $this->get_is_replace_gravatar() ) ) {
            return $avatar;
        }

        return str_replace( [
            'www.gravatar.com',
            '0.gravatar.com',
            '1.gravatar.com',
            '2.gravatar.com',
            'secure.gravatar.com',
            'cn.gravatar.com'
        ], 'gravatar.wp-china-yes.net', $avatar );
    }

    public function replace_admin_assets() {
        if ( stristr( $GLOBALS['wp_version'], 'alpha' ) || stristr( $GLOBALS['wp_version'], 'beta' ) ) {
            return;
        }

        /** 管理后台替换方法需要把ON替换为ONLY_ADMIN */
        $status = Switch_Status::ON === $this->get_is_replace_admin_assets() ? Switch_Status::ONLY_ADMIN : Switch_Status::OFF;

        $this->add_asset_replace_queue(
            'preg_replace',
            '~"\\\/(wp-admin|wp-includes)\\\/(css|js)\\\/~',
            sprintf( '"https://a2.wp-china-yes.net/WordPress@%s/$1/$2/', $GLOBALS['wp_version'] ),
            $status
        );
    }

}
