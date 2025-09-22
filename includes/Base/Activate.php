<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Base;

use Inc\Base\Cleanup;
use Inc\Classes\Settings;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Activate
 */
class Activate
{
	/**
	 * do_activation
	 *
	 * @return void
	 */
	public static function do_activation()
	{
		flush_rewrite_rules();

		self::setup();
	}

	/**
	 * setup
	 *
	 * @return void
	 */
	private static function setup()
	{
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// Sets the default settings on first activation
		$settings 			= new Settings();
		$is_first_install 	= !$settings->settings_found;
		$table_forms		= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$table_submissions 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_submissions';
		$table_form_stats 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_stats';
		$charset_collate 	= $wpdb->get_charset_collate();

		// Create table forms
		$sql = "CREATE TABLE IF NOT EXISTS $table_forms (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			type_id TINYINT(3) NOT NULL DEFAULT 0,
			template_id TINYINT(3) NOT NULL DEFAULT 0,
			image_id BIGINT(20) NOT NULL DEFAULT 0,
			header VARCHAR(255) NOT NULL,
			body VARCHAR(255) NOT NULL,
			button VARCHAR(30) NOT NULL,
			disclaimer VARCHAR(255) NOT NULL,
			success VARCHAR(255) NOT NULL,
			has_name_field TINYINT(1) NOT NULL DEFAULT 0,
			send_email TINYINT(1) NOT NULL DEFAULT 0,
			send_email_subject VARCHAR(150) NOT NULL,
			send_email_message TEXT NOT NULL,
			inview TINYINT(1) NOT NULL DEFAULT 0,
			page_type VARCHAR(100) NOT NULL,
			page_location VARCHAR(50) NOT NULL,
			page_location_value VARCHAR(100) NOT NULL,
			form_location VARCHAR(100) NOT NULL,
			page_timing VARCHAR(20) NOT NULL,
			page_timing_value TINYINT(3) NOT NULL,
			target_categories TEXT NOT NULL,
			exclusion_list TEXT NOT NULL,
			custom_css TEXT NOT NULL,
			meta TEXT NOT NULL,
			deactivate TINYINT(1) NOT NULL DEFAULT 0,
			deleted TINYINT(1) NOT NULL DEFAULT 0,
			time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta($sql);

		$error = $wpdb->last_error;

		// Create table form submissions
		if (empty($error)) {
			$sql = "CREATE TABLE IF NOT EXISTS $table_submissions (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(9) NOT NULL,
				post_id MEDIUMINT(9) NOT NULL,
				user_hash VARCHAR(32) NOT NULL,
				email VARCHAR(255) NOT NULL,
				meta TEXT NOT NULL,
				error VARCHAR(255) NOT NULL DEFAULT '',
				time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				FOREIGN KEY  (form_id) REFERENCES $table_forms(id) ON DELETE CASCADE, 
				UNIQUE KEY post_user  (form_id, post_id, user_hash) 
			) $charset_collate;";


			dbDelta($sql);

			$error = $wpdb->last_error;
		}

		// Create table form stats
		if (empty($error)) {
			$sql = "CREATE TABLE IF NOT EXISTS $table_form_stats (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(20) NOT NULL,
				post_id MEDIUMINT(9) NOT NULL,
				loads BIGINT(20) NOT NULL,
				views BIGINT(20) NOT NULL,
				submissions BIGINT(2) NOT NULL,
				occurred_at DATE NOT NULL,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id), 
				FOREIGN KEY  (form_id) REFERENCES $table_forms(id) ON DELETE CASCADE, 
				UNIQUE KEY form_date_unique  (form_id, post_id, occurred_at) 
			) $charset_collate;";

			dbDelta($sql);

			$error = $wpdb->last_error;
		}

		// If an error occurred, lets stop the activation and cleanup
		if (!empty($error)) {
			error_log($error);
			add_action('admin_notices', 'Inc\\Base\\Activate::show_admin_notice');
			do_action('admin_notices');

			// Run cleanup if this is the first time the plugin has been activated
			// We don't want to run it after and lose the users settings
			if ($is_first_install) {
				$settings->delete();
				//Cleanup::run();
			}

			exit;
		}
	}

	/**
	 * show_admin_notice
	 *
	 * @return void
	 */
	public static function show_admin_notice()
	{
?>
		<div class="notice error is-dismissible">
			<p>An error occurred while activating the <strong><?php echo OPTIN_BUDDY_PLUGIN_NAME; ?></strong> plugin. Pleast try again.</p>
		</div>
<?php
	}
}
