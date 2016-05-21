<?php

/**
 * The Subscriptions service. 
 * Uses WooCommerce Subscriptions hooks to activate/deactivate WordLift key
 * @see https://docs.woothemes.com/document/subscriptions/develop/action-reference/
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
	 * Called on 'activated_subscription' hook.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user for whom the subscription was activated.
	 * @param string $subscription_key The key for the subscription that was just set as activated on the user’s account.
	 */
	public function activated_subscription( $user_id, $subscription_key ) {

		$this->log_service->debug( "Subscription $subscription_key activated for user $user_id" );
	}

	/**
	 * Called on 'cancelled_subscription' hook.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user for whom the subscription was cancelled.
	 * @param string $subscription_key The key for the subscription that was just cancelled on the user’s account.
	 */
	public function cancelled_subscription( $user_id, $subscription_key ) {

		$this->log_service->debug( "Subscription $subscription_key cancelled for user $user_id" );
	}

	/**
	 * Called on 'processed_subscription_payment_failure' hook.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user who owns the subscription.
	 * @param string $subscription_key The key for the subscription to which the failed payment relates.
	 */
	public function processed_subscription_payment_failure( $user_id, $subscription_key ) {

		$this->log_service->debug( "Payment failed for subscription $subscription_key owned by user $user_id" );
	}




}
