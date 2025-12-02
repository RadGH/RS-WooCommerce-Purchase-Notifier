<?php
/*
Plugin Name: RS WooCommerce Purchase Notifier
Description: Set up custom email notifications to be sent based on a WooCommerce purchase of a specified product.
Author: Radley Sustaire, ZingMap LLC
Authors URI: https://zingmap.com/
Version: 1.0.1
GitHub Plugin URI: https://github.com/RadGH/RS-WooCommerce-Purchase-Notifier
GitHub Branch: master
Alchemy Update URI: https://plugins.zingmap.com/plugin/rs-woocommerce-purchase-notifier/
*/

define( 'RS_WCPN_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'RS_WCPN_PATH', dirname(__FILE__) );
define( 'RS_WCPN_VERSION', '1.0.1' );

class RS_WCPN_Plugin {
	
	/**
	 * Checks that required plugins are loaded before continuing
	 * @return void
	 */
	public static function load_plugin() {
		
		// Check for required plugins
		$missing_plugins = array();
		
		if ( ! function_exists('WC') ) {
			$missing_plugins[] = 'WooCommerce';
		}
		
		if ( $missing_plugins ) {
			self::add_admin_notice( '<strong>RS WooCommerce Purchase Notifier:</strong> The following plugins are required: ' . implode( ', ', $missing_plugins ) . '.', 'error' );
			return;
		}
		
		// Include plugin files
		include( RS_WCPN_PATH . '/includes/alchemy-updater.php' ); // For automatic updates
		include( RS_WCPN_PATH . '/includes/emails.php' );
		include( RS_WCPN_PATH . '/includes/orders.php' );
		include( RS_WCPN_PATH . '/includes/products.php' );
		include( RS_WCPN_PATH . '/includes/settings.php' );
		
	}
	
	/**
	 * Adds an admin notice to the dashboard's "admin_notices" hook.
	 *
	 * @param string $message The message to display
	 * @param string $type    The type of notice: info, error, warning, or success. Default is "info"
	 * @param bool $format    Whether to format the message with wpautop()
	 *
	 * @return void
	 */
	public static function add_admin_notice( $message, $type = 'info', $format = true ) {
		add_action( 'admin_notices', function() use ( $message, $type, $format ) {
			?>
			<div class="notice notice-<?php echo $type; ?>">
				<?php echo $format ? wpautop( $message ) : $message; ?>
			</div>
			<?php
		} );
	}
	
	/**
	 * Add a link to the settings page
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public static function add_settings_link( $links ) {
		// Change settings URL if using a custom parent slug
		// $settings_url = 'options-general.php?page=example-settings';
		// array_unshift( $links, '<a href="'. esc_attr($settings_url) .'">Settings</a>' );
		return $links;
	}
	
}

// Add a link to the settings page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'RS_WCPN_Plugin', 'add_settings_link' ) );

// Initialize the plugin
add_action( 'plugins_loaded', array( 'RS_WCPN_Plugin', 'load_plugin' ), 20 );