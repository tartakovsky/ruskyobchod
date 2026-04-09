<?php

/**
 * Product Feed Cronjob Handler
 * 
 * Handles monthly monitoring of Product Feed report data
 * and shows WordPress notifications when data is available.
 *
 * @since 9.9.0
 */
class ExactMetrics_Product_Feed_Cronjob extends ExactMetrics_Notification_Event {

	/**
	 * The cron hook name
	 *
	 * @var string
	 */
	public $cron_hook = 'exactmetrics_product_feed_monthly_check';

	/**
	 * Constructor
	 */
	public function __construct() {
		
		// Add cron schedule first
		add_filter( 'cron_schedules', array( $this, 'add_monthly_schedule' ) );
		
		// Schedule cronjob when WordPress is ready
		add_action( 'init', array( $this, 'schedule_cron' ) );
		
		// Add cron hook
		add_action( $this->cron_hook, array( $this, 'check_product_feed_data' ) );
		
		// Add AJAX handler for manual check
		add_action( 'wp_ajax_exactmetrics_manual_product_feed_check', array( $this, 'manual_check' ) );
	}

	/**
	 * Schedule the monthly cronjob
	 */
	public function schedule_cron() {
		if ( ! wp_next_scheduled( $this->cron_hook ) ) {
			wp_schedule_event( time(), 'monthly', $this->cron_hook );
		}
	}

	/**
	 * Add monthly schedule to WordPress cron schedules
	 *
	 * @param array $schedules Existing schedules
	 * @return array Modified schedules
	 */
	public function add_monthly_schedule( $schedules ) {
		$schedules['monthly'] = array(
			'interval' => 30 * DAY_IN_SECONDS, // 30 days
			'display'  => __( 'Monthly', 'exactmetrics-premium' )
		);
		return $schedules;
	}

	/**
	 * Check Product Feed data and show notification if not empty
	 */
	public function check_product_feed_data() {
		
		// Check if ExactMetrics is properly loaded
		if ( ! function_exists( 'ExactMetrics' ) || ! ExactMetrics() ) {
			return;
		}
		
		// Check if reporting system is available
		if ( ! ExactMetrics()->reporting ) {
			return;
		}
		
		// Check if WooCommerce Product Feed Pro plugin is active
		$woo_feed_pro_active = class_exists( 'AdTribes\PFP\App' ) || defined( 'ADT_PFP_PLUGIN_FILE' );
		
		// Check if ExactMetrics Pro is active
		$exactmetrics_pro_active = exactmetrics_is_pro_version();

		// Show notification if:
		// WooCommerce Product Feed Pro is active AND ExactMetrics Pro is NOT active (but has data)
		if ( $woo_feed_pro_active && ! $exactmetrics_pro_active ) {
			
			// Check if there's data in the Product Feed report to show upgrade notification
			$report = ExactMetrics()->reporting->get_report( 'ecommerce_product_feed' );
			
			if ( ! $report ) {
				return;
			}

			// Get data for the last 30 days
			$args = array(
				'start' => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
				'end'   => gmdate( 'Y-m-d', strtotime( '-1 day' ) ),
			);

			try {
				$data = $report->get_data( $args );
				
				// Check if report was successful and has data
				if ( ! empty( $data['success'] ) && $data['success'] === true ) {
					$product_feeds_table = isset( $data['data']['landing_pages_table'] ) ? $data['data']['landing_pages_table'] : array();
					
					// If there's data, show upgrade notification
					if ( ! empty( $product_feeds_table ) && is_array( $product_feeds_table ) && count( $product_feeds_table ) > 0 ) {
						$this->add_upgrade_notification();
					} else {
					}
				} else {
				}
			} catch ( Exception $e ) {
			}
		}
	}

	/**
	 * Add upgrade notification to ExactMetrics notification system
	 */
	private function add_upgrade_notification() {
		// Check if notification was already shown recently (within last 30 days)
		$last_notification = get_option( 'exactmetrics_product_feed_upgrade_notification', 0 );
		if ( time() - $last_notification < 30 * DAY_IN_SECONDS ) {
			return;
		}

		// Update last notification time
		update_option( 'exactmetrics_product_feed_upgrade_notification', time() );

		// Check if ExactMetrics notifications system is available
		if ( ! class_exists( 'ExactMetrics_Notifications' ) ) {
			return;
		}

		// Create notification data
		$notification = array(
			'id'       => 'product-feed-upgrade-' . time(),
			'type'     => array( 'lite' ), // Only show for lite users
			'priority' => 1, // High priority
			'start'    => gmdate( 'Y-m-d H:i:s' ),
			'end'      => gmdate( 'Y-m-d H:i:s', strtotime( '+7 days' ) ), // Show for 7 days
			'title'    => __( 'See How Your Feeds Perform' ),
			'content'  => __( 'With ExactMetrics Pro, you can easily measure and track the performance of your product feeds, automatically, with no coding needed. Get started now for 50% off.', 'exactmetrics-premium' ),
			'btns'     => array(
				'save_50_percent' => array(
					'url'         => $this->get_upgrade_url(),
					'text'        => __( 'Save 50%', 'exactmetrics-premium' ),
					'is_external' => true,
				),
			),
		);
		// Add notification to ExactMetrics system
		$notifications = new ExactMetrics_Notifications();
		$notifications->add( $notification );
	}


	/**
	 * Manual check via AJAX (for testing purposes)
	 * 
	 * @since 9.9.0
	 */
	public function manual_check() {
		// Security check
		if ( ! current_user_can( 'exactmetrics_view_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'exactmetrics-premium' ) ) );
		}

		// Run the check
		$this->check_product_feed_data();

		wp_send_json_success( array( 'message' => __( 'Product Feed check completed', 'exactmetrics-premium' ) ) );
	}

	/**
	 * Clear scheduled cronjob
	 */
	public function clear_cron() {
		wp_clear_scheduled_hook( $this->cron_hook );
		delete_option( 'exactmetrics_product_feed_upgrade_notification' );
	}
}

// Initialize the cronjob
new ExactMetrics_Product_Feed_Cronjob();
