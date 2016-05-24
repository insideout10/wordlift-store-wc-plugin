<?php

/**
 * The Subscriptions service. 
 * Uses WooCommerce Subscriptions hooks to activate/deactivate WordLift key
 * @see https://docs.woothemes.com/document/subscriptions/develop/action-reference/
 * @see https://docs.woothemes.com/document/subscriptions/develop/version-2/
 * @see https://docs.woothemes.com/document/subscriptions/renewal-process/
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
	public function activated_subscription( $subscription ) {

		$this->log_service->debug( "Subscription with status " . $subscription->get_status() );
		$this->log_service->debug( var_export( $subscription, true ) );

		$user = $subscription->get_user();
		$this->log_service->debug( "For user ..." );
		$this->log_service->debug( var_export( $user, true ) );
	
	}

	/**
	 * Called on 'woocommerce_subscription_status_updated' hook.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user for whom the subscription was activated.
	 * @param string $subscription_key The key for the subscription that was just set as activated on the user’s account.
	 */
	public function woocommerce_subscription_status_updated( $subscription, $new_status, $old_status ) {

		$this->log_service->debug( "Subscription $subscription->id changed status from $old_status to $new_status" );

		switch ( $new_status ) {

			case 'pending' :
			case 'switched' :
				$this->log_service->debug( "Subscription $subscription->id $new_status is pending: nothing to do here" );	
			break;

			case 'pending-cancel' :
			case 'failed' : // core WC order status mapped internally to avoid exceptions
			case 'on-hold' :
			case 'cancelled' :
			case 'expired' :
				$this->log_service->debug( "Related WordLift key has to be suspended!" );			
			break;

			case 'completed' : // core WC order status mapped internally to avoid exceptions
			case 'active' :
				$this->log_service->debug( "Related WordLift key has to be activated!" );			
			break;

		}
	}

}
