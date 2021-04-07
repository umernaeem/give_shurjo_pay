<?php
/**
 * Plugin Name: Give - Shurjo Gateway
 * Description: Process online donations via the Shurjo payment gateway.
 * Author: Umer Naeem
 * Author URI: https://givewp.com
 * Version: 1.0
 * Text Domain: give-Shurjo
 * Domain Path: /languages
 */

if ( ! class_exists( 'Give_Shurjo' ) ) {
	final class Give_Shurjo {
		static private $instance;
		public $notices = array();

		private function __construct() {
		}


		static function get_instance() {
			if ( null === static::$instance ) {
				self::$instance = new self();
				self::$instance->setup();
			}

			return self::$instance;
		}


		private function setup() {

			$this->setup_constants();

			add_action( 'give_init', array( $this, 'init' ), 10 );
			add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		}

		
		public function setup_constants() {
			
			define( 'GIVE_Shurjo_VERSION', '1.0' );
			define( 'GIVE_Shurjo_MIN_GIVE_VER', '2.4.1' );
			define( 'GIVE_Shurjo_BASENAME', plugin_basename( __FILE__ ) );
			define( 'GIVE_Shurjo_URL', plugins_url( '/', __FILE__ ) );
			define( 'GIVE_Shurjo_DIR', plugin_dir_path( __FILE__ ) );

			return self::$instance;
		}

		public function init() {

			if ( ! $this->get_environment_warning() ) {
				return;
			}

			
			$this->activation_banner();
			$this->licensing();

			if ( is_admin() ) {
				require_once GIVE_Shurjo_DIR . 'includes/admin/plugin-activation.php';
			}

			require_once GIVE_Shurjo_DIR . 'includes/functions.php';

			require_once GIVE_Shurjo_DIR . 'includes/admin/class-admin-settings.php';
			require_once GIVE_Shurjo_DIR . 'includes/payment-processing.php';
			
			require_once GIVE_Shurjo_DIR . 'includes/filters.php';
		}

		
		private function licensing() {
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( __FILE__, 'Shurjo Gateway', GIVE_Shurjo_VERSION, 'WordImpress' );
			}
		}

		
		public function check_environment() {
			
			$is_working = true;

			
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			
			$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

			if ( empty( $is_give_active ) ) {
				
				$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - Shurjo to activate.', 'give-Shurjo' ), 'https://givewp.com' ) );
				$is_working = false;
			}

			return $is_working;
		}

		
		public function get_environment_warning() {
			
			$is_working = true;

			
			if (
				defined( 'GIVE_VERSION' )
				&& version_compare( GIVE_VERSION, GIVE_Shurjo_MIN_GIVE_VER, '<' )
			) {

				
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%1$s" target="_blank">Give</a> core version %2$s for the Give - Shurjo add-on to activate.', 'give-Shurjo' ), 'https://givewp.com', GIVE_Shurjo_MIN_GIVE_VER ) );

				$is_working = false;
			}

			return $is_working;
		}

		
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		
		public function admin_notices() {

			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'span'   => array(
					'class' => array(),
				),
				'strong' => array(),
			);

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], $allowed_tags );
				echo '</p></div>';
			}

		}

		
		public function activation_banner() {

			
			if (
				! class_exists( 'Give_Addon_Activation_Banner' )
				&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
			) {
				include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
			}

			
			if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {


				$args = array(
					'file'              => __FILE__,
					'name'              => esc_html__( 'Shurjo Gateway', 'give-Shurjo' ),
					'version'           => GIVE_Shurjo_VERSION,
					'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=Shurjo' ),
					'documentation_url' => 'http://docs.givewp.com/addon-Shurjo',
					'support_url'       => 'https://givewp.com/support/',
					'testing'           => false, // Never leave true.
				);
				new Give_Addon_Activation_Banner( $args );
			}

			return true;
		}
	}

	
	function Give_Shurjo() {
		return Give_Shurjo::get_instance();
	}

	Give_Shurjo();
}
