<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes\Providers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * BrevoProvider
 */
class BrevoProvider extends Provider
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
		$this->name 	= 'Brevo';
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
		$settings = $this->get_settings();
		$this->account_connected = !empty($settings[$this->prefix . 'api_key']);

		return [
			[
				'key' 		=> $this->prefix . '_api_key',
				'name' 		=> 'API Key',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'api_key']) ? $settings[$this->prefix . 'api_key'] : ''
			],
			[
				'key' 		=> $this->prefix . '_list_id',
				'name' 		=> 'List ID',
				'desc' 		=> '',
				'type' 		=> 'number',
				'required' 	=> true,
				'meta' 		=> true,
				'value' 	=> isset($settings[$this->prefix . 'list_id']) ? $settings[$this->prefix . 'list_id'] : ''
			]
		];
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
	 * @return void
	 */
	public function send_email($email, $name, $post_meta, $overrides = []){}
}
