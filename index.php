<?php
function my_pre_http_request($preempt, $r, $url) {
    if ( ! stristr($url, 'api.wordpress.org') && ! stristr($url, 'downloads.wordpress.org')) {
        return false;
    }
    $url = str_replace('api.wordpress.org', 'your api domain', $url);
    $url = str_replace('downloads.wordpress.org', 'your download domain', $url);
    return wp_remote_request($url, $r);
}

add_filter('pre_http_request', 'my_pre_http_request', 10, 3);