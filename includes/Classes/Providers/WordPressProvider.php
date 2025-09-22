<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes\Providers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * WordPressProvider
 */
class WordPressProvider extends Provider
{
	/**
	 * apiKey
	 *
	 * @var mixed
	 */
	private $api_key;

	/**
	 * listId
	 *
	 * @var mixed
	 */
	private $list_id;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name 		= 'WordPress';
		$this->details 		= 'All <strong>' . OPTIN_BUDDY_PLUGIN_FULL_NAME . '</strong> form data will be stored in your WordPress database.';
		$this->url 			= '';
		$this->save_email 	= true;

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
	 * Note: This is first called in the parent constructor and when set_settings_fields is called
	 * 
	 * @return void
	 */
	protected function update_props()
	{
		// Update class properties
		$vars = $this->get_vars();
		foreach ($vars as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * send_email
	 *
	 * @param  string $email
	 * @param  string $name
	 * @param  array $post_meta
	 * @param  array $overrides
	 * @return bool
	 */
	public function send_email($email, $name, $post_meta, $overrides = [])
	{
		return true;
	}
}
