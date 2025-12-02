<?php

class RS_WCPN_Products {
	
	public function __construct() {
	
		// Add a meta box to Products with a textarea to list emails to be notified
		add_action( 'add_meta_boxes', array( $this, 'add_notification_emails_metabox' ) );
		
		// Save the meta box data
		add_action( 'save_post_product', array( $this, 'save_notification_emails_metabox' ) );
		
	}
	
	// Singleton instance
	protected static $instance = null;
	
	public static function get_instance() {
		if ( !isset( self::$instance ) ) self::$instance = new static();
		return self::$instance;
	}
	
	// Utilities
	
	/**
	 * Split a string at commas and new lines and returns an array of the results.
	 * If the expected count does not match the valid count, the user may have entered an incorrect email.
	 *
	 * @param string $emails_string
	 *
	 * @return array {
	 *      @type string[] $emails    Array of email addresses
	 *      @type int $expected_count Number of email addresses expected
	 *      @type int $valid_count    Number of valid email addresses returned
	 * }
	 */
	public static function parse_emails( $emails_string ) {
		$result = array(
			'emails' => array(),
			'invalid_emails' => array(),
		);
		
		if ( ! $emails_string ) return $result;
		
		// Split at line breaks and spaces
		$emails = preg_split( '/[\s,]+/', $emails_string );
		
		// Trim and filter out empty emails
		foreach( $emails as $email ) {
			$email = trim( $email );
			if ( is_email($email) ) $result['emails'][] = $email;
			else $result['invalid_emails'][] = $email;
		}
		
		return $result;
	}
	
	/**
	 * Get the notice message to display for "product_invalid_emails"
	 *
	 * @return string|false
	 */
	public static function get_notice_product_invalid_emails() {
		$post_id = get_the_ID();
		if ( ! $post_id ) $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
		if ( get_post_type( $post_id ) !== 'product' ) return false;
		
		$emails_raw = get_post_meta( $post_id, '_rs_wc_purchase_notifier_emails', true );
		$emails_parsed = self::parse_emails( $emails_raw );
		
		if ( empty( $emails_parsed['invalid_emails'] ) ) return false;
		
		// Construct the message
		$message = __( 'The following Purchase Notification emails are invalid:', 'rs-woocommerce-purchase-notifier' );
		$message .= "\n\n";
		$message .= '<ol><li>';
		$message .= implode( '</li><li>', array_map( 'esc_html', $emails_parsed['invalid_emails'] ) );
		$message .= '</li></ol>';
		$message .= "\n\n";
		$message .= '<a href="#rs_wc_purchase_notifier_emails_textarea" class="button button-secondary" onclick="querySelector(\'#rs_wc_purchase_notifier_emails_textarea\').focus();">'. __('Jump to field', 'rs-woocommerce-purchase-notifier' ) .'</a>';
		
		return $message;
	}
	
	
	// Hooks
	
	/**
	 * Add a meta box to the Product edit screen for notification emails
	 */
	public function add_notification_emails_metabox() {
		add_meta_box(
			'rs_wc_purchase_notifier_emails',
			'Purchase Notifications',
			array( $this, 'render_notification_emails_metabox' ),
			'product',
			'side',
			'default'
		);
	}
	
	/**
	 * Render the notification emails meta box
	 */
	public function render_notification_emails_metabox( $post ) {
		$emails = get_post_meta( $post->ID, '_rs_wc_purchase_notifier_emails', true );
		?>
		<label for="rs_wc_purchase_notifier_emails_textarea">Enter email addresses (one per line):</label>
		<textarea name="rs_wc_purchase_notifier_emails" id="rs_wc_purchase_notifier_emails_textarea" style="width:100%;height:100px;"><?php echo esc_textarea( $emails ); ?></textarea>
		<p class="description">Emails listed above will be notified whenever a customer submits an order containing this product.</p>
		<?php
	}
	
	/**
	 * Save the notification emails meta box data
	 */
	public function save_notification_emails_metabox( $post_id ) {
		if ( ! isset( $_POST['rs_wc_purchase_notifier_emails'] ) ) return;
		
		$emails_raw = sanitize_textarea_field( $_POST['rs_wc_purchase_notifier_emails'] );
		
		// Save the string as entered by the user
		update_post_meta( $post_id, '_rs_wc_purchase_notifier_emails', $emails_raw );
		
		// Split the string and warn if the number of valid emails does not match the expected count
		$emails_parsed = $this->parse_emails( $emails_raw );
		
		// If and invalid emails are detected, redirect to the edit screen with a notice
		if ( !empty( $emails_parsed['invalid_emails'] ) ) {
			add_filter( 'redirect_post_location', function( $location ) {
				return add_query_arg( 'rswcpn_notice', 'product_invalid_emails', $location );
			} );
		}
	}
	
}

RS_WCPN_Products::get_instance();