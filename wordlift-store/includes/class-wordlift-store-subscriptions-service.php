<?php

/**
 * The Subscriptions service. 
 *
 * @since 1.0.0
 */
class Wordlift_Store_Subscriptions_Service {

	const WS_API_URL = 'http://wls.bla/';

	const NOTIFY_KEY_ACTIVATION = 'activate';
	const NOTIFY_KEY_SUSPENSION = 'suspend';

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
				
				// Suspend related WL key
				$this->notify_key_suspension_for( $subscription );
			
				$stored_start_date = $subscription->get_time( 'start_date' );
				// If the customer cancelled the subscription within 14 days from the purchase 
				// we have to consider this action as a withdrawal and proceed to refund the customer
				if ( $stored_start_date > ( gmdate( 'U' ) - 14 * DAY_IN_SECONDS ) ) { 
					$this->log_service->debug( "Withdrawal requested for subscription $subscription->id!" );			
					// Add a custom not to the order
					$message = __( 'Withdrawal requested for the related subscription. Refund is required.', 'wordlift-store' );
					$subscription->add_order_note( $message );
				}

			break;

			case 'failed' : // core WC order status mapped internally to avoid exceptions
			case 'on-hold' :
			case 'cancelled' :
			case 'expired' :
				// Suspend related WL key
				$this->notify_key_suspension_for( $subscription );
			break;

			case 'completed' : // core WC order status mapped internally to avoid exceptions
			case 'active' :
				// Activate related WL key
				$this->notify_key_activation_for( $subscription );
			break;

		}
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Subscription $subscription The current subscription obejct.
	 */
	public function notify_key_activation_for( $subscription ) {
		$this->notify( $subscription, self::NOTIFY_KEY_ACTIVATION );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Subscription $subscription The current subscription obejct.
	 */
	public function notify_key_suspension_for( $subscription ) {
		$this->notify( $subscription, self::NOTIFY_KEY_SUSPENSION );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Subscription $subscription The current subscription obejct.
	 * @param string $action Action to be performed on WL key related to $subscription.
	 */
	public function notify( $subscription, $action ) {

		$this->log_service->debug( "Going to $action WL key for subscription $subscription->id!" );						
		// $this->log_service->debug( var_export( $subscription, true ) );
		$this->log_service->debug( var_export( $subscription->get_items(), true ) );

		// Retrieve user obj
		$user = $subscription->get_user();
		// Retrieve the first order item
		$item = array_shift( array_values( $subscription->get_items() ) );
		// Retrieve the product obj
		$product = WC()->product_factory->get_product( $item[ 'product_id' ] );

		// Prepare params
		$params = array(
			'method' => 'POST',
			'body'   => array(
				'sku'				=> $product->get_sku(),
				'order_id'			=> $subscription->id, // Check
				'user_id'			=> $user->id,
				'user_last_name'	=> $user->last_name,
				'user_first_name'	=> $user->first_name,
				'user_email'		=> $user->user_email,
				'action'			=> $action
			)
		);

		$this->log_service->debug( var_export( $params, true ) );
		// Perform notification
		wp_remote_post( self::WS_API_URL . 'subscriptions', $params );
	}
}
