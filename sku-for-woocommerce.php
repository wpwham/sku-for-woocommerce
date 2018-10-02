<?php
/*
Plugin Name: SKU Generator for WooCommerce
Plugin URI: https://wpfactory.com/item/sku-generator-for-woocommerce-plugin/
Description: Add full SKU support to WooCommerce.
Version: 1.2.5
Author: WP Wham
Author URI: https://www.wpwham.com
Text Domain: sku-for-woocommerce
Domain Path: /langs
Copyright: � 2018 WP Wham
WC tested up to: 3.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'sku-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'sku-for-woocommerce-pro/sku-for-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_WooCommerce_SKU' ) ) :

/**
 * Main Alg_WooCommerce_SKU Class
 *
 * @class   Alg_WooCommerce_SKU
 * @version 1.2.3
 */
final class Alg_WooCommerce_SKU {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.1.2
	 */
	public $version = '1.2.5';

	/**
	 * @var Alg_WooCommerce_SKU The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WooCommerce_SKU Instance
	 *
	 * Ensures only one instance of Alg_WooCommerce_SKU is loaded or can be loaded.
	 *
	 * @static
	 * @return Alg_WooCommerce_SKU - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WooCommerce_SKU Constructor.
	 *
	 * @version 1.2.3
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'sku-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
			$this->add_settings();
			// Version updated
			if ( get_option( 'alg_sku_generator_version', '' ) !== $this->version ) {
				add_action( 'admin_init', array( $this, 'version_updated' ) );
			}
		}

	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.2.2
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_sku' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'sku-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="' . esc_url( 'https://wpfactory.com/item/sku-generator-for-woocommerce-plugin/' ) . '">' .
				__( 'Unlock all', 'sku-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.2.2
	 */
	function includes() {
		// Core
		$this->core = require_once( 'includes/class-wc-sku.php' );
	}

	/**
	 * add_settings.
	 *
	 * @version 1.2.2
	 * @since   1.2.2
	 */
	function add_settings() {
		require_once( 'includes/settings/class-wc-sku-settings-section.php' );
		$this->settings = array();
		$this->settings['general']    = require_once( 'includes/settings/class-wc-sku-settings-general.php' );
		$this->settings['categories'] = require_once( 'includes/settings/class-wc-sku-settings-categories.php' );
		$this->settings['tags']       = require_once( 'includes/settings/class-wc-sku-settings-tags.php' );
		require_once( 'includes/settings/class-wc-sku-tools-regenerator.php' );
	}

	/**
	 * version_updated.
	 *
	 * @version 1.2.2
	 * @since   1.2.2
	 */
	function version_updated() {
		foreach ( $this->settings as $section ) {
			foreach ( $section->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', $autoload );
				}
			}
		}
		update_option( 'alg_sku_generator_version', $this->version );
	}

	/**
	 * Add Woocommerce settings tab to WooCommerce settings.
	 *
	 * @version 1.2.2
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-wc-settings-sku.php' );
		return $settings;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
}

endif;

if ( ! function_exists( 'alg_woocommerce_sku' ) ) {
	/**
	 * Returns the main instance of Alg_WooCommerce_SKU to prevent the need to use globals.
	 *
	 * @return  Alg_WooCommerce_SKU
	 * @version 1.1.3
	 */
	function alg_woocommerce_sku() {
		return Alg_WooCommerce_SKU::instance();
	}
}
alg_woocommerce_sku();