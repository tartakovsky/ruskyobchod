<?php
/**
 * AMP Compatibility Handler for ExactMetrics
 * 
 * @package ExactMetrics
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_AMP_Compatibility
 */
class ExactMetrics_AMP_Compatibility {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Only run if we're in AMP context
		if ( $this->is_amp() ) {
			$this->init();
		}
	}

	/**
	 * Initialize AMP compatibility
	 */
	private function init() {
		// Remove all ExactMetrics scripts and styles with highest priority
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_exactmetrics_assets' ), 999999 );
		
		// Remove ExactMetrics tracking scripts with highest priority
		add_action( 'wp_head', array( $this, 'remove_exactmetrics_tracking' ), 1 );
		add_action( 'wp_footer', array( $this, 'remove_exactmetrics_tracking' ), 1 );
		
		// Intercept specific hooks that ExactMetrics uses
		$this->intercept_exactmetrics_hooks();
		
		// Remove any remaining scripts from output
		add_action( 'wp_print_scripts', array( $this, 'remove_remaining_scripts' ), 999999 );
		add_action( 'wp_print_footer_scripts', array( $this, 'remove_remaining_scripts' ), 999999 );
	}

	/**
	 * Intercept specific ExactMetrics hooks
	 */
	private function intercept_exactmetrics_hooks() {
		// Remove the specific hook that's causing the problem
		remove_action( 'cmplz_before_statistics_script', 'exactmetrics_tracking_script', 10 );
		
		// Remove other potential ExactMetrics hooks
		remove_action( 'wp_head', 'exactmetrics_tracking_script' );
		remove_action( 'wp_footer', 'exactmetrics_tracking_script' );
		
		// Remove any gtag scripts
		remove_action( 'wp_head', array( $this, 'remove_gtag_scripts' ) );
		add_action( 'wp_head', array( $this, 'remove_gtag_scripts' ), 1 );
		
		// Remove any remaining ExactMetrics output
		add_filter( 'exactmetrics_tracking_script', '__return_false', 999999 );
		add_filter( 'exactmetrics_frontend_tracking_options', '__return_false', 999999 );
	}

	/**
	 * Remove gtag scripts from head
	 */
	public function remove_gtag_scripts() {
		// Remove any gtag scripts that might be added
		remove_action( 'wp_head', array( $this, 'output_gtag_script' ) );
		remove_action( 'wp_footer', array( $this, 'output_gtag_script' ) );
	}

	/**
	 * Remove all ExactMetrics assets
	 */
	public function dequeue_exactmetrics_assets() {
		// Remove all ExactMetrics scripts
		wp_dequeue_script( 'exactmetrics-frontend-script' );
		wp_dequeue_script( 'exactmetrics-frontend-script-js' );
		wp_dequeue_script( 'exactmetrics-gtag' );
		wp_dequeue_script( 'exactmetrics-dual-tracking' );
		
		// Remove all ExactMetrics styles
		wp_dequeue_style( 'exactmetrics-frontend' );
		wp_dequeue_style( 'exactmetrics-admin' );
		
		// Remove any other ExactMetrics assets
		global $wp_scripts, $wp_styles;
		
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $handle, 'exactmetrics' ) !== false ) {
					wp_dequeue_script( $handle );
				}
			}
		}
		
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( strpos( $handle, 'exactmetrics' ) !== false ) {
					wp_dequeue_style( $handle );
				}
			}
		}
	}

	/**
	 * Remove ExactMetrics tracking from head and footer
	 */
	public function remove_exactmetrics_tracking() {
		// Remove any inline scripts that ExactMetrics might add
		ob_start();
		// This will capture any output and prevent it from being displayed
	}

	/**
	 * Remove any remaining scripts
	 */
	public function remove_remaining_scripts() {
		// Remove any remaining ExactMetrics scripts
		global $wp_scripts;
		
		if ( isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( strpos( $handle, 'exactmetrics' ) !== false ) {
					wp_dequeue_script( $handle );
				}
			}
		}
	}

	/**
	 * Check if we are in an AMP context
	 *
	 * @return bool
	 */
	private function is_amp() {
		// Check for AMP plugin
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return true;
		}
		
		// Check for AMP theme
		if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
			return true;
		}
		
		// Check for AMP query parameter
		if ( isset( $_GET['amp'] ) && $_GET['amp'] === '1' ) {
			return true;
		}
		
		// Check for AMP in URL path
		if ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], '/amp/' ) ) {
			return true;
		}
		
		// Check for AMP in theme
		if ( function_exists( 'amp_is_canonical' ) && amp_is_canonical() ) {
			return true;
		}
		
		return false;
	}
}

// Initialize the class
new ExactMetrics_AMP_Compatibility();
