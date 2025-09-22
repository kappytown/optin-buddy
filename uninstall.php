<?php

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

// If the user does NOT want to clear their data
if (!get_option('plugin_do_uninstall', false)) exit;

require_once plugin_dir_path(__FILE__) . 'constants.php';

// Global $wpdb is needed to query the database
global $wpdb;

// Remove plugin options
delete_option(OPTIN_BUDDY_SETTINGS);
delete_option(OPTIN_BUDDY_PREFIX . 'session');
delete_option(OPTIN_BUDDY_PREFIX . 'provider_settings');

// Remove plugin update options
delete_option('external_updates-' . OPTIN_BUDDY_PLUGIN_NAME);

// Remove plugin transients
delete_transient(OPTIN_BUDDY_PREFIX . 'transient');

// Remove plugin tables
$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_submissions');
$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_stats');
$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms');

// Remove plugin cron events
wp_clear_scheduled_hook(OPTIN_BUDDY_PREFIX . 'refresh_token_hook');