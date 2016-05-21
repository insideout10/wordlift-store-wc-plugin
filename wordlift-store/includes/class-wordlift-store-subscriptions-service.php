<?php

/**
 * The Subscriptions service. 
 * Uses WooCommerce Subscriptions hooks to activate/deactivate WordLift key
 *
 * @since 1.0.0
 */
class Wordlift_Store_Subscriptions_Service {

	/**
	 * The Log service.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var \Wordlift_Store_Log_Service The Log service.
	 */
	private $log_service;

	/**
	 * Create an instance of the Thumbnail service.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->log_service = Wordlift_Store_Log_Service::get_logger( 'Wordlift_Store_Subscriptions_Service' );

	}

	/**
	 * Log a message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $level The log level.
	 * @param string $message The message to log.
	 */
	public function foo() {

	}

}
