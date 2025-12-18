<?php
/**
 * SKU Generator for WooCommerce - Review Suggestion
 *
 * @version 1.6.5
 * @since   1.6.5
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_SKU_Review' ) ) :

class Alg_WC_SKU_Review {

	/**
	 * Constructor.
	 *
	 * @version 1.6.5
	 * @since   1.6.5
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'review_suggestion' ) );
		add_action( 'admin_notices', array( $this, 'review_suggestion' ) );
	}

	/**
	 * Review suggestion notice in admin.
	 *
	 * @version 1.6.5
	 * @since   1.6.5
	 */
	function review_suggestion() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! ( isset( $_GET['page'] ) && $_GET['page'] === 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'alg_sku' && isset( $_GET['section'] ) && $_GET['section'] === 'regenerator' ) ) {
			return;
		}

        $dismissed = get_option( 'alg_sku_review_dismissed' );
		if ( $dismissed === 'permanently' || ( is_numeric( $dismissed ) && $dismissed > time() ) ) {
			return;
		}

		if ( isset( $_GET['alg_sku_dismiss_review'] ) && isset( $_GET['_wpnonce'] ) ) {
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'alg_sku_dismiss' ) ) {
				if ( isset( $_GET['later'] ) ) {
					update_option( 'alg_sku_review_dismissed', time() + ( DAY_IN_SECONDS * 30 ) );
				} else {
					update_option( 'alg_sku_review_dismissed', 'permanently' );
				}
				wp_redirect( remove_query_arg( array( 'alg_sku_dismiss_review', '_wpnonce', 'later' ) ) );
				exit;
			}
		}

		$installed = get_option( 'alg_sku_installed_time' );
		if ( ! $installed ) {
			update_option( 'alg_sku_installed_time', time() );
			return;
		}

		if ( ( $installed + ( DAY_IN_SECONDS * 7 ) ) > time() ) {
			return;
		}

		if ( ! $this->has_minimum_usage() ) {
			return;
		}

		$review_url = 'https://wordpress.org/support/plugin/sku-generator-for-woocommerce/reviews/?rate=5#new-post';
		$dismiss_url = wp_nonce_url( add_query_arg( 'alg_sku_dismiss_review', '1' ), 'alg_sku_dismiss' );
		$later_url = add_query_arg( 'later', '1', $dismiss_url );
		?>
		<div class="updated woocommerce-message">
			<a class="woocommerce-message-close notice-dismiss" href="<?php echo esc_url( $later_url ); ?>"><?php esc_html_e( 'Dismiss', 'sku-for-woocommerce' ); ?></a>
			<p><?php esc_html_e( 'Finding SKU Generator useful? We\'d appreciate a 5-star review!', 'sku-for-woocommerce' ); ?></p>
			<p><a href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Sure, you deserve it', 'sku-for-woocommerce' ); ?></a></p>
			<p><a href="<?php echo esc_url( $later_url ); ?>"><?php esc_html_e( 'Maybe later', 'sku-for-woocommerce' ); ?></a></p>
			<p><a href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'I already did!', 'sku-for-woocommerce' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Check if user has minimum usage.
	 *
	 * @version 1.6.5
	 * @since   1.6.5
	 */
	function has_minimum_usage() {
		return true;
	}
}

endif;

return new Alg_WC_SKU_Review();

