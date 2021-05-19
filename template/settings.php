<?php
namespace LitePress\WP_China_Yes\Template;

use LitePress\WP_China_Yes\Inc\Core;
use LitePress\WP_China_Yes\Inc\Switch_Status;
use function LitePress\WP_China_Yes\Inc\get_options;
use function LitePress\WP_China_Yes\Inc\update_option;

global $options;

if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
    if ( wp_verify_nonce( $_POST['wcy-from-nonce'], 'wcy-from-nonce' ) ) {
        $data = $_POST['wp-china-yes'];
        foreach ( $data as $key => $value ) {
            $data[ $key ] = sanitize_key( $value );
        }

        update_option( $data );
    }
}

$options = get_options();

?>
<div class="metabox-holder">
  <div id="wpcy_basics" class="group">
    <form method="post">
      <input type="hidden" name="wcy-from-nonce" value="<?php echo wp_create_nonce( 'wcy-from-nonce' ) ?>"/>
      <h2>WP-China-Yes</h2>
      <table class="form-table" role="presentation">
        <tbody>
        <tr>
          <th scope="row">
            <label>应用市场</label>
          </th>
          <td>
            <select class="regular" name="wp-china-yes[wpapi_replacement_mode]"
                    id="wp-china-yes[wpapi_replacement_mode]">
              <option value="<?php echo Core::LPAPI; ?>" <?php selected( Core::LPAPI, $options['wpapi_replacement_mode'] ); ?>>
                LitePress应用市场
              </option>
              <option value="<?php echo Core::WPAPI_MIRROR; ?>" <?php selected( Core::WPAPI_MIRROR, $options['wpapi_replacement_mode'] ); ?>>
                WordPress应用市场镜像
              </option>
              <option value="<?php echo Core::WPAPI; ?>" <?php selected( Core::WPAPI, $options['wpapi_replacement_mode'] ); ?>>
                不接管应用市场
              </option>
            </select>
            <p class="description">选择你想使用的应用市场</p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label>加速管理后台</label>
          </th>
          <td>
              <?php Switch_Status::get_switch( 'is_replace_admin_assets', $options['is_replace_admin_assets'], 'mini' ); ?>
            <p class="description">将WordPress核心所依赖的静态文件切换为公共资源，此选项极大的加快管理后台访问速度</p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label>Gravatar头像加速</label>
          </th>
          <td>
              <?php Switch_Status::get_switch( 'is_replace_gravatar', $options['is_replace_gravatar'] ); ?>
            <p class="description">为Gravatar头像加速，推荐所有用户启用该选项</p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label>加速谷歌字体</label>
          </th>
          <td>
              <?php Switch_Status::get_switch( 'is_replace_googlefonts', $options['is_replace_googlefonts'] ); ?>
            <p class="description">请只在包含谷歌字体的情况下才启用该选项，以免造成不必要的性能损失</p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label>加速谷歌前端公共库</label>
          </th>
          <td>
              <?php Switch_Status::get_switch( 'is_replace_googleajax', $options['is_replace_googleajax'] ); ?>
            <p class="description">请只在包含谷歌前端公共库的情况下才启用该选项，以免造成不必要的性能损失</p>
          </td>
        </tr>
        </tbody>
      </table>
      <div style="padding-left: 10px">
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改"></p>
      </div>
    </form>
  </div>
</div>
