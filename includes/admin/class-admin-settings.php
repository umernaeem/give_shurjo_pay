<?php

class Give_Shurjo_Admin_Settins {
	
	static private $instance;

	private $gateways_id = '';

	private $gateways_label = '';

	private function __construct() {
	}

	
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	
	public function setup() {
		$this->gateways_id    = 'shurjo';
		$this->gateways_label = __( 'Shurjo', 'give-Shurjo' );

		add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );
		add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );
		add_filter( 'give_get_sections_gateways', array( $this, 'add_gateways_section' ) );
	}

	
	public function register_gateway( $gateways ) {
		$gateways[ $this->gateways_id ] = array(
			'admin_label'    => $this->gateways_label,
			'checkout_label' => give_Shurjo_get_payment_method_label(),
		);

		return $gateways;
	}

	
	public function add_settings( $settings ) {

		if ( $this->gateways_id !== give_get_current_setting_section() ) {
			return $settings;
		}

		$shurjo_settings = array(
			array(
				'id'   => $this->gateways_id,
				'type' => 'title',
			),
			array(
				'id'   => 'Shurjo_merchant_user_name',
				'name' => __( 'Merchant User Name', 'give-Shurjo' ),
				'desc' => __( 'This is the API Login provided by ShurjoPay when you signed up for an account.', 'give-Shurjo' ),
				'type' => 'text',
				'size' => 'regular',
			),
			array(
				'id'   => 'Shurjo_merchant_password',
				'name' => __( 'Merchant Password', 'give-Shurjo' ),
				'desc' => __( 'This is the Transaction Key provided by ShurjoPay when you signed up for an account.', 'give-Shurjo' ),
				'type' => 'password',
				'size' => 'regular',
			),
			
			array(
				'id'   => 'Shurjo_transaction_prefix',
				'name' => __( 'Transaction Prefix', 'give-Shurjo' ),
				'desc' => __( 'Transaction Prefix: Sometime NOK', 'give-Shurjo' ),
				'type' => 'text',
				'size' => 'regular',
			),
			
			
			array(
				'id'   => $this->gateways_id,
				'type' => 'sectionend',
			),
		);

		return $shurjo_settings;
	}

	
	public function add_gateways_section( $section ) {
		$section[ $this->gateways_id ] = __( 'Shurjo', 'give-Shurjo' );

		return $section;
	}
}


Give_Shurjo_Admin_Settins::get_instance()->setup();
