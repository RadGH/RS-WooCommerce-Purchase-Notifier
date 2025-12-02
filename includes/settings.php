<?php

class RS_WCPN_Settings {
	
	public function __construct() {
		
		// Display admin notices from the plugin
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
		
	}
	
	// Singleton instance
	protected static $instance = null;
	
	public static function get_instance() {
		if ( !isset( self::$instance ) ) self::$instance = new static();
		return self::$instance;
	}
	
	// Utilities
	
	// Hooks
	
	/**
	 * Display an admin notices from the plugin
	 */
	public function display_admin_notices() {
		$notice = $_GET['rswcpn_notice'] ?? false;
		if ( ! $notice ) return;
		
		$message = false;
		$type = 'error';
		
		switch( $notice ) {
			
			case 'product_invalid_emails':
				RS_WCPN_Products::get_notice_product_invalid_emails();
				break;
			
			default:
				$message = 'Unknown notice type "'. esc_html($notice) .'".';
				break;
				
		}
		
		// Display the notice if we have a message
		if ( $message ) {
			$message = '[RS WooCommerce Purchase Notifier] ' . $message;
			
			?>
			<div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
				<?php echo wpautop( $message ); ?>
			</div>
			<?php
		}
	}
	
	
}

RS_WCPN_Settings::get_instance();