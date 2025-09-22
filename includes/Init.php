<?php

/**
 * @package Optin_Buddy
 */

namespace Inc;

require_once dirname(__FILE__) . '/../constants.php';

use Inc\Admin\AdminController;
use Inc\Frontend\EmailFormController;

// Exit if accessed directly.
defined('ABSPATH') || exit;

final class Init
{
	/**
	 * get_services
	 * The services to be registered with WordPress
	 *
	 * @return array
	 */
	public static function get_services()
	{
		// Only allow admin to edit this plugin
		if (!current_user_can('manage_options')) return [EmailFormController::class, CronManager::class];

		return [
			AdminController::class,
			EmailFormController::class,
			CronManager::class
		];
	}

	/**
	 * register_services
	 * Loop through the classes, initialize them,
	 * and call the register() method if it exists
	 * 
	 * @return
	 */
	public static function register_services()
	{
		foreach (self::get_services() as $class) {
			$service = self::instantiate($class);
			if (method_exists($service, 'register')) {
				$service->register();
			}
		}
	}

	/**
	 * instantiate
	 * Initialize the specified class
	 * 
	 * @param class $class 		class from the services array
	 * @return class instance 	new instance of the class
	 */
	private static function instantiate($class)
	{
		$service = new $class();
		return $service;
	}
}
