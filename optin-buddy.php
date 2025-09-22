<?php

/**
 * Plugin Name: 		OptinBuddy
 * Plugin URI: 			https://optinbuddy.com/wp-plugin
 * Description: 		OptinBuddy is a powerful email subscription plugin that integrates with ActiveCampaign, Contact Contact, ConvertKit, MailChimp, MailerLite, and WordPress.
 * Version: 			1.1.04
 * Requires at least: 	5.8
 * Requires PHP: 		7.1.21
 * Author: 				Trevor Nielson
 * Author URI: 			https://optinbuddy.com/
 * License: 			GPL 2.0 or later
 * License URI: 		https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: 		optin-buddy
 * 
 * @package Optin_Buddy
 * 
 */

// For security, exit if the file is accessed directly
defined('ABSPATH') || exit;

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
	require_once dirname(__FILE__) . '/vendor/autoload.php';
}

require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/third_party/plugin-update-checker/plugin-update-checker.php';

register_activation_hook(__FILE__, 'plugin_activation');
register_deactivation_hook(__FILE__, 'plugin_deactivation');
add_action('init', 'initialize_plugin');

use Inc\Init;
use Inc\Base\Activate;
use Inc\Base\Deactivate;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * activation_hook
 * Initializes the plugin and sets default options
 *
 * @return void
 */
function plugin_activation()
{
	Activate::do_activation();
}

/**
 * deactivation_hook
 * Removes all plugin data
 *
 * @return void
 */
function plugin_deactivation()
{
	Deactivate::do_deactivation();
}

/**
 * Initializes the plugin updater as well as instantiates all required plugin classes
 * 
 * @return void
 */
function initialize_plugin()
{
	// Check for updates
	$updateChecker = PucFactory::buildUpdateChecker(
		'https://optinbuddy.com/update-info.json',
		__FILE__,
		OPTIN_BUDDY_PLUGIN_NAME
	);


	// Initialize the plugin
	if (class_exists('Inc\\Init')) {
		Init::register_services();
	}
}
