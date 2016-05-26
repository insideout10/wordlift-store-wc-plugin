<?php

/**
 * The Subscriptions service. 
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
	 * Called on 'woocommerce_subscription_status_updated' hook.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Subscription $subscription The current subscription obejct.
	 * @param string $new_status New status for the current subscription.
	 * @param string $old_status Old status for the current subscription.
	 */
	public function woocommerce_subscription_status_updated( $subscription, $new_status, $old_status ) {

		$this->log_service->debug( "Subscription $subscription->id changed status from $old_status to $new_status" );
		// $this->log_service->debug( var_export( $subscription, true ) );

		switch ( $new_status ) {

			case 'pending' :
			case 'switched' :
				$this->log_service->debug( "Subscription $subscription->id $new_status is $new_status: nothing to do here..." );	
			break;

			case 'pending-cancel' : 
				
				$this->log_service->debug( "Related WordLift key has to be suspended!" );			
				do_action( 'wordlift_notify_key_suspension', $subscription );
				
				$stored_start_date = $subscription->get_time( 'start_date' );
				// If the customer cancelled the subscription within 14 days from the purchase 
				// we have to consider this action as a withdrawal and proceed to refund the customer
				if ( $stored_start_date > ( gmdate( 'U' ) - 14 * DAY_IN_SECONDS ) ) { 
					$this->log_service->debug( "Withdrawal requested for subscription $subscription->id!" );			
					do_action( 'wordlift_notify_withdrawal_request', $subscription );
				}
			break;

			case 'failed' : // core WC order status mapped internally to avoid exceptions
			case 'on-hold' :
			case 'cancelled' :
			case 'expired' :
				$this->log_service->debug( "Related WordLift key has to be suspended!" );			
				do_action( 'wordlift_notify_key_suspension', $subscription );
			break;

			case 'completed' : // core WC order status mapped internally to avoid exceptions
			case 'active' :
				$this->log_service->debug( "Related WordLift key has to be activated!" );			
				do_action( 'wordlift_notify_key_activation', $subscription );
			break;

		}
	}
}
