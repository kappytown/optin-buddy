<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Base;

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . '../../constants.php';

/**
 * Deactivate
 */
class Deactivate
{
	/**
	 * do_deactivation
	 *
	 * @return void
	 */
	public static function do_deactivation()
	{
		global $wpdb;

		$settings = get_option(OPTIN_BUDDY_SETTINGS);

		// Delete all options
		delete_option(OPTIN_BUDDY_SETTINGS);
		delete_option(OPTIN_BUDDY_PREFIX . 'session');
		delete_option(OPTIN_BUDDY_PREFIX . 'provider_settings');

		// Remove cron jobs
		wp_clear_scheduled_hook(OPTIN_BUDDY_PREFIX . 'refresh_token_hook');
	}
}
