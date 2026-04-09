<?php

/**
 * Admin class.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Admin
 * @author  Chris Christoff
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register menu items for ExactMetrics.
 *
 * @return void
 * @since 6.0.0
 * @access public
 *
 */
function exactmetrics_admin_menu()
{
	$hook             = exactmetrics_get_menu_hook();
	$menu_icon_inline = exactmetrics_get_inline_menu_icon();
	$new_indicator     = sprintf(
		'<span class="exactmetrics-menu-new-indicator">&nbsp;%s</span>',
		__( 'NEW', 'google-analytics-dashboard-for-wp' )
	);

	$menu_notification_indicator = ExactMetrics()->notifications->get_menu_count();

	if ( ExactMetrics()->license->license_expired() ) {
		$menu_notification_indicator = ExactMetrics()->notifications->get_license_expired_indicator();
	}

	$parent_slug          = 'exactmetrics_reports';
	$hide_reports_submenu = false;
	$is_lite              = ! exactmetrics_is_pro_version();

	// If user not dismissed setup checklist.
	if ( ! ExactMetrics()->setup_checklist->is_dismissed() ) {
		$hide_reports_submenu = true;
	}

	// If user disabled report view, and it is a lite user.
	if ( $hook === 'exactmetrics_settings' ) {
		$hide_reports_submenu = true;
	}

	add_menu_page(__('ExactMetrics', 'google-analytics-dashboard-for-wp'), 'ExactMetrics' . $menu_notification_indicator, 'exactmetrics_view_dashboard', $parent_slug, 'exactmetrics_reports_page', $menu_icon_inline, '100.00013467543');

	if ( ! ExactMetrics()->setup_checklist->is_dismissed() ) {
		add_submenu_page( $hook, __( 'Setup Checklist', 'google-analytics-dashboard-for-wp' ), __( 'Setup Checklist', 'google-analytics-dashboard-for-wp' ) . ExactMetrics()->setup_checklist->get_menu_count(), 'exactmetrics_save_settings', 'exactmetrics_settings#/setup-checklist', 'exactmetrics_settings_page' );
	}

	if ( $hook === 'exactmetrics_reports' ) {
		// Add Overview report page (Vue 3 app) - this is the visible Reports menu item
		add_submenu_page(
			$parent_slug,
			__( 'Overview Report:', 'google-analytics-dashboard-for-wp' ),
			__( 'Reports', 'google-analytics-dashboard-for-wp' ),
			'exactmetrics_view_dashboard',
			'exactmetrics_overview_report',
			'exactmetrics_overview_report_page'
		);

		// Register reports page with empty parent to keep it accessible but hidden from menu
		add_submenu_page( '', __( 'General Reports:', 'google-analytics-dashboard-for-wp' ), __( 'Reports', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_view_dashboard', 'exactmetrics_reports', 'exactmetrics_reports_page' );
	}

	/**
	 * Output the Custom Dashboard app mount node.
	 *
	 * @return void
	 */
	function exactmetrics_custom_dashboard_page() {
		do_action( 'exactmetrics_head' );
		// Hide WordPress admin notices on this page - Vue app handles its own notifications
		echo '<style>.exactmetrics_page_exactmetrics_custom_dashboard .notice:not(.exactmetrics-notice),.exactmetrics_page_exactmetrics_custom_dashboard .error:not(.exactmetrics-notice),.exactmetrics_page_exactmetrics_custom_dashboard .updated:not(.exactmetrics-notice){display:none !important;}</style>';
		echo '<div id="exactmetrics-custom-dashboard-app" class="mi-custom-dashboard-app">';
		echo '<div class="mi-app-loading"><span class="dashicons dashicons-update mi-spin"></span></div>';
		echo '<style>.mi-app-loading{display:flex;align-items:center;justify-content:center;min-height:400px;}.mi-spin{animation:mi-spin 1s linear infinite;font-size:40px;width:40px;height:40px;color:#338eef;}@keyframes mi-spin{to{transform:rotate(360deg);}}</style>';
		echo '</div>';
	}

// 	// Add Dashboard page (Vue 3 app)
// 	add_submenu_page(
// 		$parent_slug,
// 		__( 'Dashboard:', 'google-analytics-dashboard-for-wp' ),
// 		__( 'Dashboard', 'google-analytics-dashboard-for-wp' ) . $new_indicator,
// 		'exactmetrics_view_dashboard',
// 		'exactmetrics_custom_dashboard',
// 		'exactmetrics_custom_dashboard_page'
// 	);

	// then settings page
	add_submenu_page( $parent_slug, __( 'ExactMetrics', 'google-analytics-dashboard-for-wp' ), __( 'Settings', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_save_settings', 'exactmetrics_settings', 'exactmetrics_settings_page' );

	// Add dashboard submenu.
	add_submenu_page( 'index.php', __( 'General Reports:', 'google-analytics-dashboard-for-wp' ), 'ExactMetrics', 'exactmetrics_view_dashboard', 'admin.php?page=exactmetrics_reports' );

	// Remove own auto-generated `Insights` submenu when Reports submenu is explicitly registered.
	// Because the first submenu slug is not `exactmetrics_reports`, WordPress adds this item automatically.
	if ( $hook === 'exactmetrics_reports' ) {
		remove_submenu_page( 'exactmetrics_reports', 'exactmetrics_reports' );
	}

	// If the setup checklist is not dismissed, remove the own submenu of `Insights` main menu.
	// This way the Checklist will be the first submenu which is an important thing for onboarding.
	if ( $hide_reports_submenu && $hook !== 'exactmetrics_reports' ) {

		// Check if the user has the capability to save settings and view dashboard.
		// We should skip this for editors that have only view capability have only item in the submenu, removing that would break the menu.
		if ( ! ( ! current_user_can( 'exactmetrics_save_settings' ) && current_user_can( 'exactmetrics_view_dashboard' ) ) ) {
			// Remove own submenu of `Insights` main menu.
			remove_submenu_page( 'exactmetrics_reports', 'exactmetrics_reports' );
		}
	}

	$submenu_base = add_query_arg('page', 'exactmetrics_settings', admin_url('admin.php'));

	//  Site Notes
	add_submenu_page( $parent_slug, __( 'Site Notes:', 'google-analytics-dashboard-for-wp' ), __( 'Site Notes', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_save_settings', $submenu_base . '#/site-notes' );

	// If report disabled then remove this menu.
	if ( $hook === 'exactmetrics_reports' || $is_lite ) {
		//  AI Insights
		// translators: Icon
		add_submenu_page( $parent_slug, __( 'AI Insights:', 'google-analytics-dashboard-for-wp' ), sprintf( __( '%s AI Insights', 'google-analytics-dashboard-for-wp' ), exactmetrics_get_ai_menu_icon() ), 'exactmetrics_save_settings', 'admin.php?page=exactmetrics_reports#/ai-insights' );
	}

	$license_type = ExactMetrics()->license->get_license_type();

	//  AI Chat
	if ( $is_lite || 'plus' === $license_type ) {
		// translators: Placeholder adds a svg icon
		add_submenu_page( $parent_slug, __( 'Conversations AI:', 'google-analytics-dashboard-for-wp' ), sprintf( __( '%s Conversations AI', 'google-analytics-dashboard-for-wp' ), exactmetrics_get_ai_menu_icon() ), 'exactmetrics_save_settings', 'admin.php?page=exactmetrics_reports#/ai-insights/chat' );
	}

	// Add Popular Posts menu item.
	add_submenu_page( $parent_slug, __( 'Popular Posts:', 'google-analytics-dashboard-for-wp' ), __( 'Popular Posts', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_save_settings', $submenu_base . '#/popular-posts' );

	// If report disabled then remove this menu.
	if ( $hook === 'exactmetrics_reports' || $is_lite ) {
		// Add submenu under `Insights` main menu for user journey report.
		add_submenu_page( $parent_slug, __( 'User Journey:', 'google-analytics-dashboard-for-wp' ), __( 'User Journey', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_view_dashboard', 'admin.php?page=exactmetrics_reports#/user-journey-report' );
	}

	if ( function_exists( 'aioseo' ) ) {
		$seo_url = exactmetrics_aioseo_dashboard_url();
	} else {
		$seo_url = $submenu_base . '#/seo';
	}
	// then SEO
	add_submenu_page($parent_slug, __('SEO', 'google-analytics-dashboard-for-wp'), __('SEO', 'google-analytics-dashboard-for-wp'), 'manage_options', $seo_url);

	// Google PAX
	add_submenu_page($parent_slug, __('Google Ads', 'google-analytics-dashboard-for-wp'), __('Google Ads', 'google-analytics-dashboard-for-wp') . $new_indicator, 'exactmetrics_view_dashboard', $submenu_base . '#/google-ads');

	// then tools
	add_submenu_page($parent_slug, __('Tools:', 'google-analytics-dashboard-for-wp'), __('Tools', 'google-analytics-dashboard-for-wp'), 'manage_options', $submenu_base . '#/tools');

	// then addons
	$network_key = exactmetrics_is_pro_version() ? ExactMetrics()->license->get_network_license_key() : '';
	if (!exactmetrics_is_network_active() || (exactmetrics_is_network_active() && empty($network_key))) {
		add_submenu_page($parent_slug, __('Addons:', 'google-analytics-dashboard-for-wp'), '<span style="color:' . exactmetrics_menu_highlight_color() . '"> ' . __('Addons', 'google-analytics-dashboard-for-wp') . '</span>', 'exactmetrics_save_settings', $submenu_base . '#/addons');
	}

	add_submenu_page(
		$parent_slug,
		__('UserFeedback:', 'google-analytics-dashboard-for-wp'),
		__('UserFeedback', 'google-analytics-dashboard-for-wp') . $new_indicator,
		'manage_options',
		$submenu_base . '#/userfeedback'
	);

	add_submenu_page(
		$parent_slug,
		__('Privacy Compliance:', 'google-analytics-dashboard-for-wp'),
		__('Privacy Compliance', 'google-analytics-dashboard-for-wp'),
		'manage_options',
		$submenu_base . '#/wpconsent'
	);

	// then About Us page.
	add_submenu_page($parent_slug, __('About Us:', 'google-analytics-dashboard-for-wp'), __('About Us', 'google-analytics-dashboard-for-wp'), 'manage_options', $submenu_base . '#/about');

	if (!exactmetrics_is_pro_version() && !strstr(plugin_basename(__FILE__), 'dashboard-for')) {
		// automated promotion
		exactmetrics_automated_menu($hook);
	}

	// then Upgrade To Pro.
	if (!exactmetrics_is_pro_version()) {
		add_submenu_page($parent_slug, __('Upgrade to Pro:', 'google-analytics-dashboard-for-wp'), '<span class="exactmetrics-upgrade-submenu"> ' . __('Upgrade to Pro', 'google-analytics-dashboard-for-wp') . '</span>', 'exactmetrics_save_settings', exactmetrics_get_upgrade_link('admin-menu', 'submenu', "https://www.exactmetrics.com/lite/"));
	}
}

add_action('admin_menu', 'exactmetrics_admin_menu');


function exactmetrics_automated_menu($hook){
	$display = false;

	$now = apply_filters('exactmetrics_automated_promotion_date', wp_date('M d, Y h:i:s a'));
	$conditions = [
		[
			'title' => __('Earth Day', 'google-analytics-dashboard-for-wp'),
			'start_time' => wp_date('M d, Y h:i:s a', strtotime('Apr 17, 2023')),
			'end_time' => wp_date('M d, Y h:i:s a', strtotime('Apr 18, 2023')),
			'utm_campaign' => 'lite-promo',
			'utm_source' => 'liteplugin',
			'utm_medium' => 'earth-day',
		],
		[
			'title' => __('Cinco De Mayo!', 'google-analytics-dashboard-for-wp'),
			'start_time' => wp_date('M d, Y h:i:s a', strtotime('May 01, 2023')),
			'end_time' => wp_date('M d, Y h:i:s a', strtotime('May 08, 2023')),
			'utm_campaign' => 'lite-promo',
			'utm_source' => 'liteplugin',
			'utm_medium' => 'cinco-de-mayo',
		],
		[
			'title' => __('Upgrade to GA4', 'google-analytics-dashboard-for-wp'),
			'start_time' => wp_date('M d, Y h:i:s a', strtotime('Jun 01, 2023')),
			'end_time' => wp_date('M d, Y h:i:s a', strtotime('July 01, 2023')),
			'utm_campaign' => 'lite-promo',
			'utm_source' => 'liteplugin',
			'utm_medium' => 'goodbye-ga3',
		],
		[
			'title' => __('Summer Sale', 'google-analytics-dashboard-for-wp'),
			'start_time' => wp_date('M d, Y h:i:s a', strtotime('Jul 29, 2023')),
			'end_time' => wp_date('M d, Y h:i:s a', strtotime('Aug 05, 2023')),
			'utm_campaign' => 'lite-promo',
			'utm_source' => 'liteplugin',
			'utm_medium' => 'dogdays',
		],
		[
			'title' => __('Fortune Cookie Day', 'google-analytics-dashboard-for-wp'),
			'start_time' => wp_date('M d, Y h:i:s a', strtotime('Sep 12, 2023')),
			'end_time' => wp_date('M d, Y h:i:s a', strtotime('Sep 13, 2023')),
			'utm_campaign' => 'lite-promo',
			'utm_source' => 'liteplugin',
			'utm_medium' => 'fortune-cookie',
		],
		[
			'title' => __('Halloween Sale', 'google-analytics-dashboard-for-wp'),
			'start_time' => wp_date('M d, Y h:i:s a', strtotime('Oct 26, 2023')),
			'end_time' => wp_date('M d, Y h:i:s a', strtotime('Nov 04, 2023')),
			'utm_campaign' => 'lite-promo',
			'utm_source' => 'liteplugin',
			'utm_medium' => 'halloween',
		],
	];

	foreach($conditions as $key => $condition){
		if(strtotime($now) >= strtotime($condition['start_time']) && strtotime($now) <= strtotime($condition['end_time'])){
			add_submenu_page($hook, $condition['title'], '<span class="exactmetrics-automated-submenu"> ' . $condition['title'] . '</span>', 'exactmetrics_save_settings', exactmetrics_get_upgrade_link($condition['utm_medium'], $condition['utm_campaign'], "https://www.exactmetrics.com/lite-promo/"));
			break;
		}
	}
}

/**
 * Add this separately so all the Woo menu items are loaded and the position parameter works correctly.
 */
function exactmetrics_woocommerce_menu_item()
{
	if (class_exists('WooCommerce')) {
		// Add "Insights" sub menu item for WooCommerce Analytics menu
		if (class_exists('ExactMetrics_eCommerce')) {
			add_submenu_page('wc-admin&path=/analytics/overview', 'ExactMetrics', 'ExactMetrics', 'exactmetrics_view_dashboard', admin_url('admin.php?page=exactmetrics_reports#/ecommerce'), '', 2);
		} else {
			$submenu_base = add_query_arg('page', 'exactmetrics_settings', admin_url('admin.php'));
			add_submenu_page('wc-admin&path=/analytics/overview', 'ExactMetrics', 'ExactMetrics', 'manage_options', $submenu_base . '#/woocommerce-insights', '', 1);
		}
	}
}
add_action('admin_menu', 'exactmetrics_woocommerce_menu_item', 11);

function exactmetrics_get_menu_hook()
{
	$dashboards_disabled = exactmetrics_get_option('dashboards_disabled', false);
	if ($dashboards_disabled || (current_user_can('exactmetrics_save_settings') && !current_user_can('exactmetrics_view_dashboard'))) {
		return 'exactmetrics_settings';
	} else {
		return 'exactmetrics_reports';
	}
}

function exactmetrics_network_admin_menu()
{
	// Get the base class object.
	$base = ExactMetrics();

	// First, let's see if this is an MS network enabled plugin. If it is, we should load the license
	// menu page and the updater on the network panel
	if (!function_exists('is_plugin_active_for_network')) {
		require_once(ABSPATH . '/wp-admin/includes/plugin.php');
	}

	$plugin = plugin_basename(EXACTMETRICS_PLUGIN_FILE);
	if (!is_plugin_active_for_network($plugin)) {
		return;
	}

	$menu_notification_indicator = ExactMetrics()->notifications->get_menu_count();

	if ( ExactMetrics()->license->license_expired() ) {
		$menu_notification_indicator = ExactMetrics()->notifications->get_license_expired_indicator();
	}

	$menu_icon_inline = exactmetrics_get_inline_menu_icon();
	$hook             = 'exactmetrics_network';
	$submenu_base     = add_query_arg('page', 'exactmetrics_network', network_admin_url('admin.php'));
	add_menu_page(__('Network Settings:', 'google-analytics-dashboard-for-wp'), 'ExactMetrics' . $menu_notification_indicator, 'exactmetrics_save_settings', 'exactmetrics_network', 'exactmetrics_network_page', $menu_icon_inline, '100.00013467543');

	add_submenu_page($hook, __('Network Settings:', 'google-analytics-dashboard-for-wp'), __('Network Settings', 'google-analytics-dashboard-for-wp'), 'exactmetrics_save_settings', 'exactmetrics_network', 'exactmetrics_network_page');

	add_submenu_page($hook, __('General Reports:', 'google-analytics-dashboard-for-wp'), __('Reports', 'google-analytics-dashboard-for-wp'), 'exactmetrics_view_dashboard', 'exactmetrics_reports', 'exactmetrics_reports_page');

	if (function_exists('aioseo')) {
		$seo_url = exactmetrics_aioseo_dashboard_url();
	} else {
		$seo_url = $submenu_base . '#/seo';
	}
	// then seo
	add_submenu_page($hook, __('SEO:', 'google-analytics-dashboard-for-wp'), __('SEO', 'google-analytics-dashboard-for-wp'), 'manage_options', $seo_url, 'exactmetrics_seo_page');

	// then addons
	add_submenu_page($hook, __('Addons:', 'google-analytics-dashboard-for-wp'), '<span style="color:' . exactmetrics_menu_highlight_color() . '"> ' . __('Addons', 'google-analytics-dashboard-for-wp') . '</span>', 'exactmetrics_save_settings', $submenu_base . '#/addons');

	$submenu_base = add_query_arg('page', 'exactmetrics_network', network_admin_url('admin.php'));

	// Add About us page.
	add_submenu_page($hook, __('About Us:', 'google-analytics-dashboard-for-wp'), __('About Us', 'google-analytics-dashboard-for-wp'), 'manage_options', $submenu_base . '#/about');
}

add_action('network_admin_menu', 'exactmetrics_network_admin_menu', 5);

/**
 * Adds one or more classes to the body tag in the dashboard.
 *
 * @param String $classes Current body classes.
 *
 * @return String          Altered body classes.
 */
function exactmetrics_add_admin_body_class($classes)
{
	$screen = function_exists('get_current_screen') ? get_current_screen() : false;
	if (empty($screen) || empty($screen->id) || strpos($screen->id, 'exactmetrics') === false) {
		return $classes;
	}

	return "$classes exactmetrics_page ";
}

add_filter('admin_body_class', 'exactmetrics_add_admin_body_class', 10, 1);

/**
 * Adds one or more classes to the body tag in the dashboard.
 *
 * @param String $classes Current body classes.
 *
 * @return String          Altered body classes.
 */
function exactmetrics_add_admin_body_class_tools_page($classes)
{
	$screen = function_exists('get_current_screen') ? get_current_screen() : false;

	if (empty($screen) || empty($screen->id) || strpos($screen->id, 'exactmetrics_tools') === false || 'insights_page_exactmetrics_tools' === $screen->id) {
		return $classes;
	}

	return "$classes insights_page_exactmetrics_tools ";
}

add_filter('admin_body_class', 'exactmetrics_add_admin_body_class_tools_page', 10, 1);

/**
 * Adds one or more classes to the body tag in the dashboard.
 *
 * @param String $classes Current body classes.
 *
 * @return String          Altered body classes.
 */
function exactmetrics_add_admin_body_class_addons_page($classes)
{
	$screen = function_exists('get_current_screen') ? get_current_screen() : false;
	if (empty($screen) || empty($screen->id) || strpos($screen->id, 'exactmetrics_addons') === false || 'insights_page_exactmetrics_addons' === $screen->id) {
		return $classes;
	}

	return "$classes insights_page_exactmetrics_addons ";
}

add_filter('admin_body_class', 'exactmetrics_add_admin_body_class_addons_page', 10, 1);

/**
 * Add a link to the settings page to the plugins list
 *
 * @param array $links array of links for the plugins, adapted when the current plugin is found.
 *
 * @return array $links
 */
function exactmetrics_add_action_links($links)
{
	$docs = '<a title="' . esc_attr__('ExactMetrics Knowledge Base', 'google-analytics-dashboard-for-wp') . '" href="' . exactmetrics_get_url('all-plugins', 'kb-link', "https://www.exactmetrics.com/docs/") . '">' . esc_html__('Documentation', 'google-analytics-dashboard-for-wp') . '</a>';
	array_unshift($links, $docs);

	// If Lite, support goes to forum. If pro, it goes to our website
	if (exactmetrics_is_pro_version()) {
		$support = '<a title="ExactMetrics Pro Support" href="' . exactmetrics_get_url('all-plugins', 'pro-support-link', "https://www.exactmetrics.com/my-account/support/") . '">' . esc_html__('Support', 'google-analytics-dashboard-for-wp') . '</a>';
		array_unshift($links, $support);
	} else {
		$support = '<a title="ExactMetrics Lite Support" href="' . exactmetrics_get_url('all-plugins', 'lite-support-link', "https://www.exactmetrics.com/lite-support/") . '">' . esc_html__('Support', 'google-analytics-dashboard-for-wp') . '</a>';
		array_unshift($links, $support);
	}

	if (is_network_admin()) {
		$settings_link = '<a href="' . esc_url(network_admin_url('admin.php?page=exactmetrics_network')) . '">' . esc_html__('Network Settings', 'google-analytics-dashboard-for-wp') . '</a>';
	} else {
		$settings_link = '<a href="' . esc_url(admin_url('admin.php?page=exactmetrics_settings')) . '">' . esc_html__('Settings', 'google-analytics-dashboard-for-wp') . '</a>';
	}

	array_unshift($links, $settings_link);

	// If lite, show a link where they can get pro from
	if (!exactmetrics_is_pro_version()) {
		$get_pro = '<a title="' . esc_attr__('Get ExactMetrics Pro', 'google-analytics-dashboard-for-wp') . '" target="_blank" rel="noopener" href="' . exactmetrics_get_upgrade_link('all-plugins', 'upgrade-link', "https://www.exactmetrics.com/lite/") . '" style="font-weight:700; color: #1da867;">' . esc_html__('Get ExactMetrics Pro', 'google-analytics-dashboard-for-wp') . '</a>';
		array_unshift($links, $get_pro);
	}

	return $links;
}

add_filter('plugin_action_links_' . plugin_basename(EXACTMETRICS_PLUGIN_FILE), 'exactmetrics_add_action_links');
add_filter('network_admin_plugin_action_links_' . plugin_basename(EXACTMETRICS_PLUGIN_FILE), 'exactmetrics_add_action_links');

/**
 * Loads a partial view for the Administration screen
 *
 * @access public
 *
 * @param string $template PHP file at includes/admin/partials, excluding file extension
 * @param array $data Any data to pass to the view
 *
 * @return  void
 * @since 6.0.0
 *
 */
function exactmetrics_load_admin_partial($template, $data = array())
{

	if (exactmetrics_is_pro_version()) {
		$dir = trailingslashit(plugin_dir_path(ExactMetrics()->file) . 'pro/includes/admin/partials');

		if (file_exists($dir . $template . '.php')) {
			require_once($dir . $template . '.php');

			return true;
		}
	} else {
		$dir = trailingslashit(plugin_dir_path(ExactMetrics()->file) . 'lite/includes/admin/partials');

		if (file_exists($dir . $template . '.php')) {
			require_once($dir . $template . '.php');

			return true;
		}
	}

	$dir = trailingslashit(plugin_dir_path(ExactMetrics()->file) . 'includes/admin/partials');

	if (file_exists($dir . $template . '.php')) {
		require_once($dir . $template . '.php');

		return true;
	}

	return false;
}

/**
 * When user is on a ExactMetrics related admin page, display footer text
 * that graciously asks them to rate us.
 *
 * @param string $text
 *
 * @return string
 * @since 6.0.0
 */
function exactmetrics_admin_footer($text)
{
	global $current_screen;
	if (
		! empty( $current_screen->id )
		&& strpos( $current_screen->id, 'exactmetrics' ) !== false
		&& ! exactmetrics_is_pro_version()
	) {
		$url = 'https://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp?filter=5';
		// Translators: Placeholders add a link to the wordpress.org repository.
		$text = sprintf(esc_html__('Please rate %1$sExactMetrics%2$s %3$s on %4$sWordPress.org%5$s to help us spread the word.', 'google-analytics-dashboard-for-wp'), '<strong>', '</strong>', '<a class="exactmetrics-no-text-decoration" href="' . $url . '" target="_blank" rel="noopener noreferrer"><i class="monstericon-star"></i><i class="monstericon-star"></i><i class="monstericon-star"></i><i class="monstericon-star"></i><i class="monstericon-star"></i></a>', '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">', '</a>');
	}

	return $text;
}

add_filter('admin_footer_text', 'exactmetrics_admin_footer', 1, 2);

function exactmetrics_admin_setup_notices()
{
	// Make sure they have the permissions to do something
	if (!current_user_can('exactmetrics_save_settings')) {
		return;
	}

	// Priority:
	// 0. UA sunset
	// 1. Google Analytics not authenticated
	// 2. License key not entered for pro
	// 3. License key not valid/okay for pro
	// 4. WordPress + PHP min versions
	// 5. (old) Optin setting not configured
	// 6. Manual UA code
	// 7. Automatic updates not configured
	// 8. Woo upsell
	// 9. EDD upsell

	//  0. UA sunset supported alert
	$profile = is_network_admin() ? ExactMetrics()->auth->get_network_analytics_profile() : ExactMetrics()->auth->get_analytics_profile();

	if ( !empty($profile['ua']) && empty($profile['v4']) && !exactmetrics_is_own_admin_page() ) {
		$title = __('Urgent: Your Website is Not Tracking Any Google Analytics Data!', 'google-analytics-dashboard-for-wp');
		$message = __('Google Analytics 3 (UA) and support was sunset on July 1, 2023. Your website is currently NOT tracking any analytics. </br>Create or connect a new Google Analytics 4 property immediately to start tracking.', 'google-analytics-dashboard-for-wp');

		$wizard_url     = admin_url('admin.php?page=exactmetrics-onboarding');

		echo '<div class="notice notice-error is-dismissible exactmetrics-notice" data-notice="exactmetrics_ua_sunset">';
		echo '<p><strong>' . esc_html($title) . '</strong></p>';
		echo '<p>' . wp_kses_post($message) . '</p>';
		echo '<p>';
		echo '<a href="https://www.exactmetrics.com/docs/connect-google-analytics/"
				   target="_blank" rel="noopener noreferrer">' .
			__( 'Learn How to Create a GA4 Property', 'google-analytics-dashboard-for-wp' ) . // phpcs:ignore
			'</a><br>';
		echo '<a href="' . esc_url($wizard_url) . '">' .
			__( 'Connect a Property', 'google-analytics-dashboard-for-wp' ) . // phpcs:ignore
			'</a><br>';
		echo '</p>';
		echo '</div>';

		return;
	}

	$is_plugins_page = 'plugins' === get_current_screen()->id;

	// 1. Google Analytics not authenticated
	if ( ! is_network_admin() && ! exactmetrics_get_v4_id() && ! defined( 'EXACTMETRICS_DISABLE_TRACKING' ) && ! exactmetrics_is_own_admin_page() ) {

		$submenu_base = is_network_admin() ? add_query_arg( 'page', 'exactmetrics_network', network_admin_url( 'admin.php' ) ) : add_query_arg( 'page', 'exactmetrics_settings', admin_url( 'admin.php' ) );
		$title        = esc_html__( 'Please Setup Website Analytics to See Audience Insights', 'google-analytics-dashboard-for-wp' );
		$primary      = esc_html__( 'Please Connect Your Website to ExactMetrics', 'google-analytics-dashboard-for-wp' );
		$urlone       = is_network_admin() ? network_admin_url( 'admin.php?page=exactmetrics-onboarding' ) : admin_url( 'admin.php?page=exactmetrics-onboarding' );
		$secondary    = esc_html__( 'Learn More', 'google-analytics-dashboard-for-wp' );
		$urltwo       = $submenu_base . '#/about/getting-started';
		$disclaimer   = __( 'Note: You will be transfered to ExactMetrics.com to complete the setup wizard.', 'google-analytics-dashboard-for-wp' );
		$message      = esc_html__( 'ExactMetrics, the WordPress Analytics plugin, helps you easily connect your website to Google Analytics, so that you can see how people find and use your website. Over 1 million smart website owners use ExactMetrics to grow faster.', 'google-analytics-dashboard-for-wp' );
		echo '<div class="notice notice-info"><p style="font-weight:700">' . $title . '</p><p>' . $message . '</p><p><a class="button-primary exactmetrics-setup-wizard-link">' . $primary . '</a>&nbsp;&nbsp;&nbsp;<a href="' . $urltwo . '" class="button-secondary">' . $secondary . '</a></p><p class="exactmetrics-disclaimer-note">' . $disclaimer . '</p></div>'; // phpcs:ignore -- All escaped above

		return;
	}

	// 2. License key not entered for pro
	$key = exactmetrics_is_pro_version() ? ExactMetrics()->license->get_license_key() : '';
	if (exactmetrics_is_pro_version() && empty($key)) {
		$page = is_network_admin() ? network_admin_url('admin.php?page=exactmetrics_network') : admin_url('admin.php?page=exactmetrics_settings');
		// Translators: Adds a link to retrieve the license.
		$message = sprintf(esc_html__('Warning: No valid license key has been entered for ExactMetrics. You are currently not getting updates, and are not able to view reports. %1$sPlease click here to enter your license key and begin receiving updates and reports.%2$s', 'google-analytics-dashboard-for-wp'), '<a href="' . esc_url($page) . '">', '</a>');
		echo '<div class="error"><p>' . $message . '</p></div>'; // phpcs:ignore

		return;
	}

	// 3. License key not valid/okay for pro
	if (exactmetrics_is_pro_version()) {
		$message = '';
		if (ExactMetrics()->license->get_site_license_key()) {
			if (ExactMetrics()->license->site_license_expired()) {
				// Translators: Adds a link to the license renewal.
				$message = sprintf(esc_html__('Your license key for ExactMetrics has expired. %1$sPlease click here to renew your license key.%2$s', 'google-analytics-dashboard-for-wp'), '<a href="' . exactmetrics_get_url('admin-notices', 'expired-license', "https://www.exactmetrics.com/login/") . '" target="_blank" rel="noopener noreferrer" referrer="no-referrer">', '</a>');
			} else if (ExactMetrics()->license->site_license_disabled()) {
				$message = esc_html__('Your license key for ExactMetrics has been disabled. Please use a different key.', 'google-analytics-dashboard-for-wp');
			} else if (ExactMetrics()->license->site_license_invalid()) {
				$message = esc_html__('Your license key for ExactMetrics is invalid. The key no longer exists or the user associated with the key has been deleted. Please use a different key.', 'google-analytics-dashboard-for-wp');
			}
		} else if (ExactMetrics()->license->get_network_license_key()) {
			if (ExactMetrics()->license->network_license_expired()) {
				// Translators: Adds a link to renew license.
				$message = sprintf(esc_html__('Your network license key for ExactMetrics has expired. %1$sPlease click here to renew your license key.%2$s', 'google-analytics-dashboard-for-wp'), '<a href="' . exactmetrics_get_url('admin-notices', 'expired-license', "https://www.exactmetrics.com/login/") . '" target="_blank" rel="noopener noreferrer" referrer="no-referrer">', '</a>');
			} else if (ExactMetrics()->license->network_license_disabled()) {
				$message = esc_html__('Your network license key for ExactMetrics has been disabled. Please use a different key.', 'google-analytics-dashboard-for-wp');
			} else if (ExactMetrics()->license->network_license_invalid()) {
				$message = esc_html__('Your network license key for ExactMetrics is invalid. The key no longer exists or the user associated with the key has been deleted. Please use a different key.', 'google-analytics-dashboard-for-wp');
			}
		}
		if (!empty($message)) {
			echo '<div class="error"><p>' . $message . '</p></div>'; // phpcs:ignore

			return;
		}
	}

	// 4. Notices for PHP/WP version deprecations
	if (current_user_can('update_core')) {
		global $wp_version;

		$compatible_php_version = apply_filters('exactmetrics_compatible_php_version', false);
		$compatible_wp_version  = apply_filters('exactmetrics_compatible_wp_version', false);

		$url = exactmetrics_get_url('global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-php/');

		$message = false;
		if (version_compare(phpversion(), $compatible_php_version['required'], '<')) {
			// Translators: Placeholders add the PHP version, a link to the ExactMetrics blog and a line break.
			$message = sprintf(esc_html__('Your site is running an outdated, insecure version of PHP (%1$s), which could be putting your site at risk for being hacked.%4$sWordPress stopped supporting your PHP version in April, 2019.%4$sUpdating PHP only takes a few minutes and will make your website significantly faster and more secure.%4$s%2$sLearn more about updating PHP%3$s', 'google-analytics-dashboard-for-wp'), phpversion(), '<a href="' . $url . '" target="_blank">', '</a>', '<br>');
		} else if (version_compare(phpversion(), $compatible_php_version['warning'], '<')) {
			// Translators: Placeholders add the PHP version, a link to the ExactMetrics blog and a line break.
			$message = sprintf(esc_html__('Your site is running an outdated, insecure version of PHP (%1$s), which could be putting your site at risk for being hacked.%4$sWordPress stopped supporting your PHP version in November, 2019.%4$sUpdating PHP only takes a few minutes and will make your website significantly faster and more secure.%4$s%2$sLearn more about updating PHP%3$s', 'google-analytics-dashboard-for-wp'), phpversion(), '<a href="' . $url . '" target="_blank">', '</a>', '<br>');
		} else if (version_compare(phpversion(), $compatible_php_version['recommended'], '<')) {
			// Translators: Placeholders add the PHP version, a link to the ExactMetrics blog and a line break.
			$message = sprintf(esc_html__('Your site is running an outdated, insecure version of PHP (%1$s), which could be putting your site at risk for being hacked.%4$sWordPress is working towards discontinuing support for your PHP version.%4$sUpdating PHP only takes a few minutes and will make your website significantly faster and more secure.%4$s%2$sLearn more about updating PHP%3$s', 'google-analytics-dashboard-for-wp'), phpversion(), '<a href="' . $url . '" target="_blank">', '</a>', '<br>');
		}

		if ($message) {
			echo '<div class="error"><p>' . wp_kses($message, [
				'br' => array(),
				'b' => array(),
				'strong' => array(),
				'i' => array(),
				'a' => array(
					'href' => array(),
					'target' => array(),
					'title' => array(),
				),
			]) . '</p></div>';
			return;
		}

		// WordPress 4.9
		/* else if ( version_compare( $wp_version, '5.0', '<' ) ) {
			$url = exactmetrics_get_url( 'global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-wordpress/' );
			// Translators: Placeholders add the current WordPress version and links to the ExactMetrics blog
			$message = sprintf( esc_html__( 'Your site is running an outdated version of WordPress (%1$s).%4$sExactMetrics will stop supporting WordPress versions lower than 5.0 in 2021.%4$sUpdating WordPress takes just a few minutes and will also solve many bugs that exist in your WordPress install.%4$s%2$sLearn more about updating WordPress%3$s', 'google-analytics-dashboard-for-wp' ), $wp_version, '<a href="' . $url . '" target="_blank">', '</a>', '<br>' );
			echo '<div class="error"><p>'. $message.'</p></div>';
			return;
		} */
		// PHP 5.4/5.5
		// else if ( version_compare( phpversion(), '5.6', '<' ) ) {
		//  $url = exactmetrics_get_url( 'global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-php/' );
		//  $message = sprintf( esc_html__( 'Your site is running an outdated, insecure version of PHP (%1$s), which could be putting your site at risk for being hacked.%4$sWordPress will stop supporting your PHP version in April, 2019.%4$sUpdating PHP only takes a few minutes and will make your website significantly faster and more secure.%4$s%2$sLearn more about updating PHP%3$s', 'google-analytics-dashboard-for-wp' ), phpversion(), '<a href="' . $url . '" target="_blank">', '</a>', '<br>' );
		//  echo '<div class="error"><p>'. $message.'</p></div>';
		//  return;
		// }
		// // WordPress 4.6 - 4.8
		// else if ( version_compare( $wp_version, '4.9', '<' ) ) {
		//  $url = exactmetrics_get_url( 'global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-wordpress/' );
		//  $message = sprintf( esc_html__( 'Your site is running an outdated version of WordPress (%1$s).%4$sExactMetrics will stop supporting WordPress versions lower than 4.9 in October, 2019.%4$sUpdating WordPress takes just a few minutes and will also solve many bugs that exist in your WordPress install.%4$s%2$sLearn more about updating WordPress%3$s', 'google-analytics-dashboard-for-wp' ), $wp_version, '<a href="' . $url . '" target="_blank">', '</a>', '<br>' );
		//  echo '<div class="error"><p>'. $message.'</p></div>';
		//  return;
		// }

	}

	// 5. Optin setting not configured
	// if ( ! is_network_admin() ) {
	//     if ( ! get_option( 'exactmetrics_tracking_notice' ) ) {
	//         if ( ! exactmetrics_get_option( 'anonymous_data', false ) ) {
	//             if ( ! exactmetrics_is_dev_url( network_site_url( '/' ) ) ) {
	//                 if ( exactmetrics_is_pro_version() ) {
	//                     exactmetrics_update_option( 'anonymous_data', 1 );
	//                     return;
	//                 }
	//                 $optin_url  = add_query_arg( 'mi_action', 'opt_into_tracking' );
	//                 $optout_url = add_query_arg( 'mi_action', 'opt_out_of_tracking' );
	//                 echo '<div class="updated"><p>';
	//                 echo esc_html__( 'Allow ExactMetrics to track plugin usage? Opt-in to tracking and our newsletter to stay informed of the latest changes to ExactMetrics and help us ensure compatibility.', 'google-analytics-dashboard-for-wp' );
	//                 echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . __( 'Allow', 'google-analytics-dashboard-for-wp' ) . '</a>';
	//                 echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'google-analytics-dashboard-for-wp' ) . '</a>';
	//                 echo '</p></div>';
	//                 return;
	//             } else {
	//                 // is testing site
	//                  update_option( 'exactmetrics_tracking_notice', '1' );
	//             }
	//         }
	//     }
	// }

	$notices = get_option('exactmetrics_notices');
	if (!is_array($notices)) {
		$notices = array();
	}

	// 6. Authenticate, not manual
	$authed  = ExactMetrics()->auth->is_authed() || ExactMetrics()->auth->is_network_authed();
	$url     = is_network_admin() ? network_admin_url('admin.php?page=exactmetrics_network') : admin_url('admin.php?page=exactmetrics_settings');
	$tracking_code = exactmetrics_get_v4_id_to_output();
	// Translators: Placeholders add links to the settings panel.
	$manual_text = sprintf(esc_html__('Important: You are currently using manual GA4 Measurement ID output. We highly recommend %1$sauthenticating with ExactMetrics%2$s so that you can access our new reporting area and take advantage of new ExactMetrics features.', 'google-analytics-dashboard-for-wp'), '<a href="' . $url . '">', '</a>');
	$migrated    = exactmetrics_get_option('gadwp_migrated', 0);
	if ($migrated > 0) {
		$url = admin_url('admin.php?page=exactmetrics-getting-started&exactmetrics-migration=1');
		// Translators: Placeholders add links to the settings panel.
		$text        = esc_html__('Click %1$shere%2$s to reauthenticate to be able to access reports. For more information why this is required, see our %3$sblog post%4$s.', 'google-analytics-dashboard-for-wp');
		$manual_text = sprintf($text, '<a href="' . esc_url($url) . '">', '</a>', '<a href="' . exactmetrics_get_url('notice', 'manual-ua', 'https://www.exactmetrics.com/why-did-we-implement-the-new-google-analytics-authentication-flow-challenges-explained/') . '" target="_blank">', '</a>');
	}

	if (empty($authed) && !isset($notices['exactmetrics_auth_not_manual']) && !empty($tracking_code)) {
		echo '<div class="notice notice-info is-dismissible exactmetrics-notice" data-notice="exactmetrics_auth_not_manual">';
		echo '<p>';
		echo $manual_text; // phpcs:ignore
		echo '</p>';
		echo '</div>';

		return;
	}

	// 7. Automatic updates not configured
	// if ( ! is_network_admin() ) {
	//     $updates   = exactmetrics_get_option( 'automatic_updates', false );
	//     $url       = admin_url( 'admin.php?page=exactmetrics_settings' );

	//     if ( empty( $updates) && ! isset( $notices['exactmetrics_automatic_updates' ] ) ) {
	//         echo '<div class="notice notice-info is-dismissible exactmetrics-notice" data-notice="exactmetrics_automatic_updates">';
	//             echo '<p>';
	//             echo sprintf( esc_html__( 'Important: Please %1$sconfigure the Automatic Updates Settings%2$s in ExactMetrics.', 'google-analytics-dashboard-for-wp' ), '<a href="' . $url .'">', '</a>' );
	//             echo '</p>';
	//         echo '</div>';
	//         return;
	//     }
	// }

	// 8. WooUpsell
	if (!exactmetrics_is_pro_version() && class_exists('WooCommerce') && $is_plugins_page) {
		if (!isset($notices['exactmetrics_woocommerce_tracking_available'])) {
			$woo_notice_template = '<div class="notice notice-success is-dismissible exactmetrics-notice exactmetrics-wooedd-upsell-row" data-notice="exactmetrics_woocommerce_tracking_available">
				%1$s
				<div class="exactmetrics-wooedd-upsell-left">
					<p><strong>%2$s</strong></p>
					<p>%3$s</p>
					<p>%4$s</p>
					<p>%5$s</p>
					<p>%6$s</p>
					%7$s
					%8$s
				</div>
			</div>';

			$woo_notice_button = sprintf(
				// Translators: Placeholders add a link to the ExactMetrics website.
				esc_html__('%1$sGet ExactMetrics Pro%2$s', 'google-analytics-dashboard-for-wp'),
				'<a class="button button-primary button-hero" target="_blank" href="' . esc_url(exactmetrics_get_upgrade_link('admin-notices', 'woocommerce-upgrade')) . '">',
				' &raquo;</a>'
			);

			$woo_notice_offer = sprintf(
				'<div class="exactmetrics-wooedd-upsell-offer">%1$s</div>',
				__('Save <span>50%</span> Off ExactMetrics Pro', 'google-analytics-dashboard-for-wp')
			);

			$woo_notice_bg = esc_url(trailingslashit(EXACTMETRICS_PLUGIN_URL)) . 'assets/images/upsell/exactmetrics-woo-edd-upsell.svg';
			$woo_notice_offer_icon = esc_url(trailingslashit(EXACTMETRICS_PLUGIN_URL)) . 'assets/images/upsell/woo-offer-icon.svg';
			$woo_notice_style = "<style>.exactmetrics-wooedd-upsell-left .button-hero,.exactmetrics-wooedd-upsell-offer{width:270px;margin-bottom:20px;text-align:center}.exactmetrics-wooedd-upsell-row{display:flex;background-image:url($woo_notice_bg);background-repeat:no-repeat;background-position:96% bottom}.exactmetrics-wooedd-upsell-left{margin-left:20px}.exactmetrics-wooedd-upsell-offer{background:#fafeb0;padding:6px 0;position:relative;font-weight:700;font-size:15px;line-height:28px}.exactmetrics-wooedd-upsell-offer span{color:#338eef}.exactmetrics-wooedd-upsell-offer:before{content:url('$woo_notice_offer_icon');position:absolute;left:-23px;bottom:-30px}@media (max-width:1300px){.exactmetrics-wooedd-upsell-row{background-size:60%}}@media (max-width:900px){.exactmetrics-wooedd-upsell-row{background-image:none}.exactmetrics-wooedd-upsell-left,.exactmetrics-wooedd-upsell-left .button-hero,.exactmetrics-wooedd-upsell-offer{width:100%}}</style>";

			// phpcs:disable
			echo sprintf(
				$woo_notice_template,
				$woo_notice_style,
				__('Add eCommerce Analytics to your WooCommerce Store', 'google-analytics-dashboard-for-wp'),
				__('Unlock all of our advanced eCommerce features specifically designed to help your store make more money..', 'google-analytics-dashboard-for-wp'),
				__('ExactMetrics Pro users instantly gain access to valuable insights such as average order value, conversion rates, as well as marketing performance with UTM tracking.', 'google-analytics-dashboard-for-wp'),
				__('And by upgrading, Pro users also get enhanced tracking for Forms, User Journeys, PPC Pixels, Custom UserID tracking, SEO Reports, and much more.', 'google-analytics-dashboard-for-wp'),
				__('Start making better data-driven decisions today!', 'google-analytics-dashboard-for-wp'),
				$woo_notice_offer,
				$woo_notice_button
			);
			return;
			// phpcs:enable
		}
	}

	// 9. EDDUpsell
	if (!exactmetrics_is_pro_version() && class_exists('Easy_Digital_Downloads') && $is_plugins_page) {
		if (!isset($notices['exactmetrics_edd_tracking_available'])) {
			echo '<div class="notice notice-success is-dismissible exactmetrics-notice exactmetrics-wooedd-upsell-row" data-notice="exactmetrics_edd_tracking_available">';
			echo '<div class="exactmetrics-wooedd-upsell-left">';
			echo '<p><strong>';
			echo esc_html('Enhanced Ecommerce Analytics for Your Easy Digital Downloads Store', 'google-analytics-dashboard-for-wp');
			echo '</strong></p>';
			echo '<img class="exactmetrics-wooedd-upsell-image exactmetrics-wooedd-upsell-image-small" src="' . esc_url(trailingslashit(EXACTMETRICS_PLUGIN_URL)) . 'assets/images/upsell/woo-edd-upsell.png">';
			echo '<p>';
			echo esc_html('ExactMetrics Pro gives you detailed stats and insights about your customers.', 'google-analytics-dashboard-for-wp');
			echo '</p>';
			echo '<p>';
			echo esc_html('This helps you make data-driven decisions about your content, and marketing strategy so you can increase your website traffic, leads, and sales.', 'google-analytics-dashboard-for-wp');
			echo '</p>';
			echo '<p>';
			echo esc_html('Pro customers also get Form Tracking, Custom Dimensions Tracking, UserID Tracking and much more.', 'google-analytics-dashboard-for-wp');
			echo '</p>';
			echo '<p>';
			echo esc_html('Start making data-driven decisions to grow your business.', 'google-analytics-dashboard-for-wp');
			echo '</p>';
			echo sprintf(esc_html__('%1$sGet ExactMetrics Pro%2$s', 'google-analytics-dashboard-for-wp'), '<a class="button button-primary button-hero" target="_blank" href="' . esc_url(exactmetrics_get_upgrade_link('admin-notices', 'edd-upgrade')) . '">', ' &raquo;</a>');
			echo '</p>';
			echo '</div><div class="exactmetrics-wooedd-upsell-right">';
			echo '<img class="exactmetrics-wooedd-upsell-image exactmetrics-wooedd-upsell-image-large" src="' . esc_url(trailingslashit(EXACTMETRICS_PLUGIN_URL)) . 'assets/images/upsell/woo-edd-upsell.png">';
			echo '</div>';
			echo '</div>';

			return;
		}
	}


	if (isset($notices['exactmetrics_cross_domains_extracted']) && false === $notices['exactmetrics_cross_domains_extracted']) {
		$page = is_network_admin() ? network_admin_url('admin.php?page=exactmetrics_network') : admin_url('admin.php?page=exactmetrics_settings');
		$page = $page . '#/advanced';
		// Translators: Adds a link to the settings panel.
		$message = sprintf(esc_html__('Warning: ExactMetrics found cross-domain settings in the custom code field and converted them to the new settings structure.  %1$sPlease click here to review and remove the code no longer needed.%2$s', 'google-analytics-dashboard-for-wp'), '<a href="' . esc_url($page) . '">', '</a>');
		echo '<div class="notice notice-success is-dismissible exactmetrics-notice" data-notice="exactmetrics_cross_domains_extracted"><p>' . $message . '</p></div>'; // phpcs:ignore

		return;
	}
}

add_action('admin_notices', 'exactmetrics_admin_setup_notices');
add_action('network_admin_notices', 'exactmetrics_admin_setup_notices');


// AM Notices
function exactmetrics_am_notice_optout($super_admin)
{
	if (exactmetrics_get_option('hide_am_notices', false) || exactmetrics_get_option('network_hide_am_notices', false)) {
		return false;
	}

	return $super_admin;
}

add_filter("am_notifications_display", 'exactmetrics_am_notice_optout', 10, 1);

/**
 * Inline critical css for the menu to prevent breaking the layout when our scripts get blocked by browsers.
 */
function exactmetrics_admin_menu_inline_styles()
{
?>
	<style>
		#toplevel_page_exactmetrics_reports .wp-menu-image img,
		#toplevel_page_exactmetrics_settings .wp-menu-image img,
		#toplevel_page_exactmetrics_network .wp-menu-image img {
			width: 18px;
			height: auto;
			padding-top: 7px;
		}

		#toplevel_page_exactmetrics_reports .wp-submenu li a {
			display: flex;
			align-items: center;
		}

		#toplevel_page_exactmetrics_reports .wp-submenu .exactmetrics-sidebar-icon {
			padding-right: 6px;
		}
	</style>
<?php
}

add_action('admin_head', 'exactmetrics_admin_menu_inline_styles', 300);

/**
 * Display notice in admin when measurement protocol is left blank
 */
function exactmetrics_empty_measurement_protocol_token()
{
	if (!class_exists('ExactMetrics_eCommerce') && !class_exists('ExactMetrics_Forms')) {
		return;
	}

	$page = is_network_admin()
		? network_admin_url('admin.php?page=exactmetrics_network')
		: admin_url('admin.php?page=exactmetrics_settings');

	$api_secret = is_network_admin()
		? ExactMetrics()->auth->get_network_measurement_protocol_secret()
		: ExactMetrics()->auth->get_measurement_protocol_secret();

	$current_code = exactmetrics_get_v4_id_to_output();

	if (empty($current_code) || !empty($api_secret)) {
		return;
	}

	$message = sprintf(
		/* translators: Placeholders add a link to an article. */
		esc_html__(
			'Your Measurement Protocol API Secret is currently left blank. To see more advanced analytics please enter a Measurement API Secret. %1$sLearn how to find your API Secret%2$s.',
			'google-analytics-dashboard-for-wp'
		),
		'<a target="_blank" href="' . exactmetrics_get_url('notice', 'empty-measurement-protocol-secret', 'https://www.exactmetrics.com/docs/how-to-create-your-measurement-protocol-api-secret-in-ga4/') . '">',
		'</a>'
	);
	echo '<div class="error"><p>' . $message . '</p></div>'; // phpcs:ignore
}

add_action( 'admin_notices', 'exactmetrics_empty_measurement_protocol_token' );
add_action( 'network_admin_notices', 'exactmetrics_admin_setup_notices' );

/**
 * Display notice in admin when ExactMetrics Ads addon is installed.
 */
function exactmetrics_ads_addon_installed_notice() {
	if ( ! class_exists( 'ExactMetrics_Ads' ) ) {
		return;
	}

	// Hide notice if dismissed in the last 30 days.
	if ( get_transient( 'exactmetrics_ads_addon_installed_notice_dismissed' ) ) {
		return;
	}

	if ( exactmetrics_is_pro_version() && ExactMetrics()->license->get_license_type() === 'pro' ) {
		$addons_url = admin_url() . '/admin.php?page=exactmetrics_settings#/addons?ads_addon_ppc_alert=1';
		$button_text = esc_html__( 'Install Now', 'google-analytics-dashboard-for-wp' );
		$button_target = '_self';
	} else {
		$addons_url = exactmetrics_get_upgrade_link('admin-notices', 'ads-addon-activated', 'https://www.exactmetrics.com/lite/');
		$button_text = esc_html__( 'Upgrade Now', 'google-analytics-dashboard-for-wp' );
		$button_target = '_blank';
	}

	$message = sprintf(
		/* translators: Placeholders link to addons page. */
		esc_html__(
			'We\'ve detected you have our %1$sExactMetrics Ads%2$s addon activated. Please activate our %3$sPPC Tracking%4$s addon for more advanced features and powerful integrations.',
			'google-analytics-dashboard-for-wp'
		),
		'<strong>',
		'</strong>',
		'<a href="' . $addons_url . '" target="' . $button_target . '">',
		'</a>'
	);

	// Output notice with an ID so the common admin JS can catch the dismiss and persist it for 30 days.
	echo '<div id="exactmetrics-ads-addon-notice" class="notice notice-info is-dismissible"><p>' . $message . '</p><p><a href="' . $addons_url . '" class="button button-primary" target="' . $button_target . '">' . $button_text . '</a></p></div>'; // phpcs:ignore
}

add_action( 'admin_notices', 'exactmetrics_ads_addon_installed_notice' );

/**
 * AJAX handler to persist dismissal of the Ads addon installed notice for 30 days.
 */
function exactmetrics_dismiss_ads_addon_notice_ajax() {
	check_ajax_referer( 'exactmetrics-dismiss-notice', 'nonce' );
	set_transient( 'exactmetrics_ads_addon_installed_notice_dismissed', 1, 30 * DAY_IN_SECONDS );
	wp_send_json_success();
}
add_action( 'wp_ajax_exactmetrics_dismiss_ads_addon_notice', 'exactmetrics_dismiss_ads_addon_notice_ajax' );

/**
 * Check if the plugin is MI Lite.
 *
 * @return bool
 */
function check_is_it_exactmetrics_lite() {
	return 'googleanalytics.php' == basename( EXACTMETRICS_PLUGIN_FILE );
}

/**
 * Add custom text and links to footer.
 */
function exactmetrics_in_admin_footer() {
	$screen = get_current_screen();
	// Check the current screen is ExactMetrics.
	if (empty($screen) || empty($screen->id) || strpos($screen->id, 'exactmetrics') === false) {
		return;
	}

	$is_pro = exactmetrics_is_pro_version();

	$links = [
		[
			'text' => __( 'Support', 'google-analytics-dashboard-for-wp' ),
			'link' => $is_pro ? exactmetrics_get_url('footer_link', 'made-with-love', 'https://www.exactmetrics.com/support/') : 'https://wordpress.org/support/plugin/google-analytics-dashboard-for-wp/',
			'target' => '_blank',
		],
		[
			'text' => __( 'Docs', 'google-analytics-dashboard-for-wp' ),
			'link' => exactmetrics_get_url('footer_link', 'made-with-love', 'https://www.exactmetrics.com/docs/'),
			'target' => '_blank',
		],
		[
			'text' => __( 'Free Plugins', 'google-analytics-dashboard-for-wp' ),
			'link' => admin_url('admin.php?page=exactmetrics_settings#/about'),
			'target' => '_self',
		],
	];

	echo '<div class="exactmetrics-footer-love">';
	echo sprintf(esc_html__('Made with %1$s by the ExactMetrics Team', 'google-analytics-dashboard-for-wp'), '<span class="exactmetrics-footer-love-icon">♥</span>');
	$links_output = [];
	foreach($links as $link){
		$links_output[] = '<a target="'.esc_attr($link['target']).'" href="'.esc_url($link['link']).'">' . esc_html($link['text']) . '</a>';
	}
	echo '<div>'. implode('<span class="flsep">/</span>', $links_output) .'</div>'; // phpcs:ignore
	echo '</div>';
}

add_action( 'in_admin_footer', 'exactmetrics_in_admin_footer' );

/**
 * Display notice in admin to install WPConsent.
 */
function exactmetrics_wpconsent_install_notice() {
	global $pagenow;

	// Dont show the notice on update page.
	if ( 'update-core.php' === $pagenow ) {
		return;
	}

	// If WPConsent plugin active.
	if ( function_exists( 'WPConsent' ) ) {
		return;
	}

	// If other plugin active
	if ( exactmetrics_wpconsent_is_cmp_plugin_active() ) {
		return;
	}

	// If notice has been dismissed
	if ( get_option( 'exactmetrics_wpconsent_notice_dismissed', false ) ) {
		return;
	}

	// If user not authenticated then do not show.
	$authed = ExactMetrics()->auth->is_authed() || ExactMetrics()->auth->is_network_authed();
	if ( empty( $authed ) ) {
		return;
	}

	$plugin_installed = false;
	if ( file_exists( WP_PLUGIN_DIR . '/wpconsent-cookies-banner-privacy-suite/wpconsent.php' ) ) {
		$plugin_installed = true;
	}
	?>
	<div class="exactmetrics-wpconsent-notice-box notice" id="exactmetrics-wpconsent-notice">
		<div class="exactmetrics-wpconsent-sidebar">
			<div class="exactmetrics-wpconsent-icon">
				<img src="<?php echo esc_url(trailingslashit(EXACTMETRICS_PLUGIN_URL)); ?>assets/images/exactmetrics-notice-box-logo.svg">
			</div>
		</div>
		<div class="exactmetrics-wpconsent-content">
			<button class="exactmetrics-wpconsent-close" id="exactmetrics-wpconsent-notice-close">&times;</button>
			<h3><?php esc_html_e('Make Your Website Analytics Compliant with Privacy Laws', 'google-analytics-dashboard-for-wp'); ?></h3>
			<p><?php esc_html_e('Privacy laws like GDPR, CCPA, and others require user consent before you can track visitors. Our sister plugin, WPConsent, helps you add a smart cookie consent banner for better privacy compliance with Google Analytics.', 'google-analytics-dashboard-for-wp'); ?></p>
			<div class="exactmetrics-wpconsent-buttons">
				<a class="exactmetrics-wpconsent-btn-primary" href="<?php echo esc_url( admin_url() ) ?>admin.php?page=exactmetrics_settings#/wpconsent?installnow=1">
					<?php
						if ( $plugin_installed ) {
							esc_html_e('Activate', 'google-analytics-dashboard-for-wp');
						} else {
							esc_html_e('Install Now', 'google-analytics-dashboard-for-wp');
						}
					 ?>
				</a>
				<a class="exactmetrics-wpconsent-btn-secondary" href="<?php echo esc_url( admin_url() ) ?>admin.php?page=exactmetrics_settings#/wpconsent"><?php esc_html_e('Learn More', 'google-analytics-dashboard-for-wp'); ?></a>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'admin_notices', 'exactmetrics_wpconsent_install_notice' );

/**
 * Add Overview report page (Vue 3 app)
 */
function exactmetrics_overview_report_page() {
	do_action( 'exactmetrics_head' );
	// Hide WordPress admin notices on this page - Vue app handles its own notifications
	echo '<style>.exactmetrics_page .notice:not(.exactmetrics-notice),.exactmetrics_page .error:not(.exactmetrics-notice),.exactmetrics_page .updated:not(.exactmetrics-notice){display:none !important;}</style>';
	echo '<div id="exactmetrics-overview-report-app">';
	echo '<div class="mi-app-loading"><span class="dashicons dashicons-update mi-spin"></span></div>';
	echo '<style>.mi-app-loading{display:flex;align-items:center;justify-content:center;min-height:400px;}.mi-spin{animation:mi-spin 1s linear infinite;font-size:40px;width:40px;height:40px;color:#338eef;}@keyframes mi-spin{to{transform:rotate(360deg);}}</style>';
	echo '</div>';
}

/**
 * Add EEA Compliance file.
 */
require_once __DIR__ . '/eea-compliance.php';

/**
 * Add translations functionality.
 */
require_once __DIR__ . '/translations.php';

/**
 * Report filter CRUD ajax handler.
 */
require_once __DIR__ . '/reports/filter-ajax.php';
