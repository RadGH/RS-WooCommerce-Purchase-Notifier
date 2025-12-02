<?php

class RS_WCPN_Emails {
	
	public static $enable_html_emails = true;
	
	public function __construct() {
	
	}
	
	// Singleton instance
	protected static $instance = null;
	
	public static function get_instance() {
		if ( !isset( self::$instance ) ) self::$instance = new static();
		return self::$instance;
	}
	
	// Utilities
	
	/**
	 * Sends an email to a recipient
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string[] $headers
	 * @param array $attachments
	 *
	 * @return bool Whether the email was sent successfully.
	 */
	public static function send_email( $to, $subject, $message, $headers = array(), $attachments = array() ) {
		if ( empty($headers) ) $headers = array();
		
		if ( self::$enable_html_emails ) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$message = wpautop($message);
		}else{
			echo 1;
			exit;
		}
		
		return wp_mail( $to, $subject, $message, $headers, $attachments );
	}
	
	// Hooks
	
	
}

RS_WCPN_Emails::get_instance();