<?php
/**
 * Plugin Name: WP-China-Yes
 * Description: 替换WordPress官方链接为镜像
 * Author: Hanada
 * Author URI:https://hanada.info/
 * Version: 1.0.0
 * Network: True
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

if (!class_exists('WP_CHINA_YES')) {
    class WP_CHINA_YES {

        public function init() {
            if (is_admin()) {
                /**
                 * 替换api.wordpress.org和downloads.wordpress.org为wp.hanada.ltd
                 * URL替换代码来自于我爱水煮鱼(http://blog.wpjam.com/)开发的WPJAM Basic插件
                 */
                add_filter('pre_http_request', function ($preempt, $r, $url) {
                    if ( ! stristr($url, 'api.wordpress.org') && ! stristr($url, 'downloads.wordpress.org')) {
                        return false;
                    }
                    $url = str_replace('api.wordpress.org', 'wp.hanada.ltd/api', $url);
                    $url = str_replace('downloads.wordpress.org', 'wp.hanada.ltd/dl', $url);

                    $curl_version = '1.0.0';
                    if (function_exists('curl_version')) {
                        $curl_version_array = curl_version();
                        if (is_array($curl_version_array) && key_exists('version', $curl_version_array)) {
                            $curl_version = $curl_version_array['version'];
                        }
                    }

                    return wp_remote_request($url, $r);
                }, 1, 3);
            }

            /**
             * 替换Gravatar为WP-China.org维护的大陆加速节点
             */
            add_filter('get_avatar', function ($avatar) {
                return str_replace([
                    'www.gravatar.com',
                    '0.gravatar.com',
                    '1.gravatar.com',
                    '2.gravatar.com',
                    'secure.gravatar.com',
                    'cn.gravatar.com'
                ], 'gravatar.hanada.info', $avatar);
            }, 1);
        }
    }

    (new WP_CHINA_YES)->init();
}
