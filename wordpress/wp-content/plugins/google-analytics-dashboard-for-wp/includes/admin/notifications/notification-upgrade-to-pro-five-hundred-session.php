<?php

/**
 * Check if the user has hit 500 sessions and show a notification to upgrade to pro.
 * Recurrence: 5 Days
 */
final class ExactMetrics_Notification_Upgrade_To_Pro_Five_Hundred_Session extends ExactMetrics_Notification_Event {

	public $notification_id = 'exactmetrics_notification_upgrade_to_pro_five_hundred_session';
	public $notification_interval = 5; // We will check in every 5 days.
	public $notification_type = array( 'lite', 'plus' );
	public $notification_icon = 'star';
	public $notification_category = 'insight';
	public $notification_priority = 3;

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 */
	public function prepare_notification_data( $notification ) {
		$last_added = get_option('exactmetrics_notification_upgrade_to_pro_five_hundred_session_last_added', 0);

		// Check if the last notification was added before 30 days.
		if ( $last_added && intval( $last_added ) + ( 30 * DAY_IN_SECONDS ) > time() ) {
			return false;
		}

		$report = $this->get_report();

		$sessions = isset( $report['data']['infobox']['sessions']['value'] ) ? $report['data']['infobox']['sessions']['value'] : 0;

		// If the user has less than 500 sessions.
		if ( $sessions < 500 ) {
			return false;
		}

		$notification['title'] = __( "Congrats! You've hit 500 monthly sessions!", 'google-analytics-dashboard-for-wp' );
		// Translators: upgrade to pro notification content
		$notification['content'] = __( 'Folks like you who upgrade to ExactMetrics Pro get access to advanced reporting, powerful integrations to help you grow even more!', 'google-analytics-dashboard-for-wp' );
		$notification['btns']    = array(
			"upgrade_to_pro" => array(
				'url'         => $this->get_upgrade_url(),
				'text'        => __( 'Upgrade Now', 'google-analytics-dashboard-for-wp' ),
				'is_external' => true,
			),
		);

		update_option('exactmetrics_notification_upgrade_to_pro_five_hundred_session_last_added', time());

		return $notification;
	}

}

// initialize the class
new ExactMetrics_Notification_Upgrade_To_Pro_Five_Hundred_Session();
