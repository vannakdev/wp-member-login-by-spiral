<?php

/**
 * @package   Spiral_Member_Login
 * @author    SPIRAL Inc.
 * @license   GPLv2
 * @link      https://www.spiral-platform.co.jp/
 * @copyright (c) SPIRAL Inc.
 * @copyright Portions copyright (c) Eric Mann
 *
 * @wordpress-plugin
 * Plugin Name: WP Member Login by SPIRAL
 * Description: Add membership management and secure authentication by SPIRAL&reg; into your WordPress site.
 * Version:     1.2.5
 * Author:      SPIRAL Inc.
 * Author URI:  https://www.spiral-platform.co.jp/
 * License:     GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: spiral-member-login
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}
// Setup
define( 'SML_PLUGIN_URL', __FILE__ );
define( 'SML_PLUGIN_SLUG_V1', "spiral-member-login" );
define( 'SML_PLUGIN_SLUG_V2', "spiral-v2-member-login" );

if (get_option('sml_version') == false) {
	add_option('sml_version', 1);
	add_option('sml_is_setup',true);
}

require_once(plugin_dir_path(__FILE__) . 'version_one/class-spiral-member-login-session.php');
require_once(plugin_dir_path(__FILE__) . 'version_one/class-spiral-member-login-base.php');
require_once(plugin_dir_path(__FILE__) . 'libs/translator.php');

if (get_option('sml_version') == 1) {
	require_once(plugin_dir_path(__FILE__) . 'version_one/class-spiral-api.php');
	require_once(plugin_dir_path(__FILE__) . 'version_one/class-spiral-member-login.php');
	require_once(plugin_dir_path(__FILE__) . 'version_one/class-spiral-member-login-template.php');
	require_once(plugin_dir_path(__FILE__) . 'version_one/class-spiral-member-login-widget.php');
	require_once(plugin_dir_path(__FILE__) . 'custom_blocks/version_one/enqueue.php');
	add_action( 'enqueue_block_editor_assets', 'r_enqueue_block_editor_assets' );
	add_action( 'enqueue_block_assets', 'r_enqueue_block_assets' );
}


if (get_option('sml_version') == 2) {
	require_once(plugin_dir_path(__FILE__) . 'version_two/class-spiral-platform-api.php');
	require_once(plugin_dir_path(__FILE__) . 'version_two/class-spiral-v2-member-login.php');
	require_once(plugin_dir_path(__FILE__) . 'version_two/class-spiral-v2-member-login-template.php');
	require_once(plugin_dir_path(__FILE__) . 'version_two/class-spiral-v2-member-login-widget.php');
	require_once(plugin_dir_path(__FILE__) . 'custom_blocks/version_two/enqueue.php');
	add_action( 'enqueue_block_editor_assets', 'r_enqueue_block_editor_assets' );
	add_action( 'enqueue_block_assets', 'r_enqueue_block_assets' );
}

register_uninstall_hook(__FILE__, array('Spiral_Member_Login', 'uninstall'));
register_activation_hook(__FILE__, array('Spiral_Member_Login', 'activate'));
register_deactivation_hook(__FILE__, array('Spiral_Member_Login', 'deactivate'));

Spiral_Member_Login::get_instance();

if (!function_exists('sml_is_logged_in')) :
	function sml_is_logged_in()
	{
		return Spiral_Member_Login::get_instance()->is_logged_in();
	}
endif;

if (!function_exists('sml_user_prop')) :
	function sml_user_prop($key)
	{
		return Spiral_Member_Login::get_instance()->get_user_prop($key);
	}
endif;

add_action('admin_enqueue_scripts', function(string $hookSuffix) {
	if ($hookSuffix === 'widgets.php') {
		wp_dequeue_script('wp-editor');
	}
}, 10, 1);


function add_custom_plugin_block_categories( $categories ) {
	$plugin_slug = get_option('sml_version') == 1 ? "spiral-member-login" : 'spiral-v2-member-login';
	return array_merge(
        $categories,
        [
            [
                'slug'  => 'spiral-member-login',
                'title' => __( 'WP Member Login by SPIRAL' ),
            ],
        ]
    );
}
add_action( 'block_categories_all', 'add_custom_plugin_block_categories', 10, 2 );
