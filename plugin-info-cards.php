<?php
/**
 * Plugin Name: Plugin Info Cards
 * Plugin URI:  https://github.com/gagan0123/plugin-info-cards
 * Description: Adds shortcode to display plugin info for given slugs
 * Version:     1.0
 * Author:      Gagan Deep Singh
 * Author URI:  https://gagan0123.com
 * License:     GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plugin-info-cards
 * Domain Path: /languages
 *
 * @package Plugin_Info_Cards
 */

/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GS_PIC_PATH' ) ) {
	/**
	 * Path to the plugin directory.
	 *
	 * @since 1.0
	 */
	define( 'GS_PIC_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( ! defined( 'GS_PIC_URL' ) ) {
	/**
	 * URL to the current plugin directory.
	 *
	 * @since 1.0
	 */
	define( 'GS_PIC_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
}

/**
 * The core plugin class
 */
require_once GS_PIC_PATH . 'includes/class-plugin-info-cards.php';
