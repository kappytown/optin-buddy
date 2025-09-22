<?php

namespace Inc;

use Inc\Classes\EmailProvider;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * CronManager
 * To add cron: wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', 'opt_bud_refresh_token_hook');
 * To remove cron: wp_clear_scheduled_hook('opt_bud_refresh_token_hook');
 * To manually run crons: wp cron event run refresh_token
 */
final class CronManager
{
	/**
	 * __construct
	 * Sets up the cron jobs
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action(OPTIN_BUDDY_PREFIX . 'refresh_token_hook', [$this, 'refresh_token']);
	}

	/**
	 * refresh_token
	 * Refreshes the Constant Contact access token
	 *
	 * @return void
	 */
	public function refresh_token()
	{
		// Make sure a provider is set before trying to refresh the token
		$settings = get_option(OPTIN_BUDDY_SETTINGS);
		if (!empty($settings['provider'])) {
			$providerName = $settings['provider'];

			if (!empty($providerName)) {
				$EmailProvider = new EmailProvider($providerName);
				$Provider = $EmailProvider->get_provider();
				$Provider->refresh_token();
			}
		} else {
			error_log('OptinBuddy CronManager: No email provider set, cannot refresh token.');
		}
	}
}
