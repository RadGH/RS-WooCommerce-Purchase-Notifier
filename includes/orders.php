<?php

class RS_WCPN_Orders {
	
	public function __construct() {
	
		// When an order is placed, check if any products should send a notification email
		add_action( 'woocommerce_new_order', array( $this, 'handle_order_completed' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_order_completed' ) );
		
	}
	
	// Singleton instance
	protected static $instance = null;
	
	public static function get_instance() {
		if ( !isset( self::$instance ) ) self::$instance = new static();
		return self::$instance;
	}
	
	// Utilities
	
	// Hooks
	
	public function handle_order_completed( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( !$order instanceof WC_Order ) return;
		
		// Check if already sent before
		$sent_date = get_post_meta( $order_id, '_rs_wc_purchase_notifier_emails_sent_date', true );
		if ( $sent_date ) return;
		
		// Collect a list of emails to be notified.
		// Only send one email per address, even if multiple products in the order have the same email.
		$emails_to_notify = array();
		
		foreach( $order->get_items() as $item ) {
			// Ignore fees and taxes
			if ( ! $item instanceof WC_Order_Item_Product ) continue;
			
			$product_id = $item->get_product_id();
			
			$emails_raw = get_post_meta( $product_id, '_rs_wc_purchase_notifier_emails', true );
			
			if ( $emails_raw ) {
				$parsed_emails = RS_WCPN_Products::parse_emails( $emails_raw );
				foreach( $parsed_emails['emails'] as $email ) {
					if ( !isset($emails_to_notify[ $email ]) ) $emails_to_notify[ $email ] = array();
					
					$emails_to_notify[ $email ][] = array(
						'product_name' => $item->get_name(),
						'quantity' => $item->get_quantity(),
						'link' => get_permalink( $product_id )
					);
				}
			}
			
		}
		
		if ( empty($emails_to_notify) ) return;
		
		// Send each email
		$sent = 0;
		$expected = count($emails_to_notify);
		
		foreach( $emails_to_notify as $email => $email_data ) {
			$subject = 'Purchase Notification - Order #' . $order->get_order_number();
			
			$message = 'A new order has been placed. Order details:' . "\n\n";
			$message .= 'Order Number: ' . $order->get_order_number() . "\n";
			$message .= 'Order Date: ' . wc_format_datetime( $order->get_date_created() ) . "\n\n";
			$message .= 'You are being notified because the order contains the following product(s):' . "\n";
			
			$message .= '<ul>';
			
			foreach( $email_data as $data ) {
				if ( isset($data['product_name']) ) {
					$message .= '<li>';
					$message .= 'Product: <a href="' . esc_url( $data['link'] ) . '">' . esc_html( $data['product_name'] ) . '</a>';
					$message .= ' | Quantity: ' . intval( $data['quantity'] );
					$message .= '</li>';
				}
			}
			
			$message .= '</ul>';
			
			// Add a link to view the order
			$order_url = get_edit_post_link( $order_id );
			$message .= "\n\n";
			$message .= 'View the order here: ' . esc_url( $order_url ) . "\n";
			
			// Send the email
			$result = RS_WCPN_Emails::send_email( $email, $subject, $message );
			
			if ( $result ) {
				$sent++;
			}else{
				$order->add_order_note( sprintf( 'RS WooCommerce Purchase Notifier: Failed to send notification email to %s.', $email ) );
			}
		}
		
		// Add an order note explaining how many purchase notification emails were sent
		$order->add_order_note( sprintf( 'RS WooCommerce Purchase Notifier: Sent %d out of %d notification emails.', $sent, $expected ) );
		
		// Remember that this order has had notifications sent
		update_post_meta( $order_id, '_rs_wc_purchase_notifier_emails_sent_date', date('Y-m-d H:i:s') );
	}
	
}

RS_WCPN_Orders::get_instance();