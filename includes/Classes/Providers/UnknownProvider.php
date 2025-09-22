<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes\Providers;

use Inc\Exceptions\EmailSendingException;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * UnknownProvider
 */
class UnknownProvider extends Provider
{
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name 	= 'Unknown Provider';
		$this->details 	= '';
		$this->url 		= '';

		parent::__construct();
	}

	/**
	 * get_setting_fields
	 *
	 * @return array
	 */
	public function get_setting_fields()
	{
		return [];
	}

	/**
	 * update_props
	 * This is used to update the class properties with the values from the stored settings object
	 * If any overrides exist, they will override the setting values in the get_vars method
	 * 
	 * Note: This is first called in the parent constructor
	 * 
	 * @return void
	 */
	protected function update_props(){}

	/**
	 * send_email
	 *
	 * @param  string $email
	 * @param  string $name
	 * @param  array $post_meta
	 * @param  array $overrides
	 * @return void
	 */
	public function send_email($email, $name, $post_meta, $overrides = [])
	{
		throw new EmailSendingException("Failed to send user email to {$this->name}.");
	}
}
