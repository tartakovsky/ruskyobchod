<?php
/**
 * Database System Loader
 *
 * Loads all database framework classes in the correct order.
 * This file should be required early in the plugin initialization.
 *
 * @since 9.11.0
 * @package ExactMetrics
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load core framework classes
require_once EXACTMETRICS_PLUGIN_DIR . 'includes/database/class-db-base.php';
require_once EXACTMETRICS_PLUGIN_DIR . 'includes/database/class-migration.php';
require_once EXACTMETRICS_PLUGIN_DIR . 'includes/database/class-migration-runner.php';
require_once EXACTMETRICS_PLUGIN_DIR . 'includes/database/class-db-schema.php';

// Load table classes
require_once EXACTMETRICS_PLUGIN_DIR . 'includes/database/tables/class-cache-table.php';

// Load migration classes
require_once EXACTMETRICS_PLUGIN_DIR . 'includes/database/migrations/class-migration-9110-cache-table.php';
require_once EXACTMETRICS_PLUGIN_DIR . 'includes/database/migrations/class-migration-9110-remove-per-user-cache.php';

/**
 * Initialize and run database migrations.
 *
 * This function is called during plugin installation/upgrade to run any
 * pending database migrations.
 *
 * @since 9.11.0
 * @return array Migration results.
 */
function exactmetrics_run_database_migrations() {
	// Initialize migration runner
	$runner = new ExactMetrics_Migration_Runner();

	// Register all migrations
	$migrations = array(
		'ExactMetrics_Migration_9110_Cache_Table',
		'ExactMetrics_Migration_9110_Remove_Per_User_Cache',
	);

	$runner->register_migrations( $migrations );

	// Run pending migrations
	$results = $runner->run_pending_migrations();

	// Update schema version if all migrations successful
	if ( $results['success'] ) {
		$schema = new ExactMetrics_DB_Schema();
		$old_version = $schema->get_current_version();
		$new_version = $schema->get_target_version();

		$schema->update_version( $new_version );
		$schema->record_upgrade( $old_version, $new_version );
	}

	return $results;
}

/**
 * Get the cache table instance.
 *
 * Helper function to get a singleton instance of the cache table.
 *
 * @since 9.11.0
 * @return ExactMetrics_Cache_Table Cache table instance.
 */
function exactmetrics_get_cache_table() {
	static $instance = null;

	if ( $instance === null ) {
		$instance = new ExactMetrics_Cache_Table();
	}

	return $instance;
}

/**
 * Get the database schema manager instance.
 *
 * Helper function to get a singleton instance of the schema manager.
 *
 * @since 9.11.0
 * @return ExactMetrics_DB_Schema Schema manager instance.
 */
function exactmetrics_get_db_schema() {
	static $instance = null;

	if ( $instance === null ) {
		$instance = new ExactMetrics_DB_Schema();
		// Register cache table
		$instance->register_table( exactmetrics_get_cache_table() );
	}

	return $instance;
}
