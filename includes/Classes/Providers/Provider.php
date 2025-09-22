<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes\Providers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Provider
 */
abstract class Provider extends Oauth
{
	/**
	 * PROVIDER_SETTINGS
	 *
	 * @var string
	 */
	const PROVIDER_SETTINGS = OPTIN_BUDDY_PREFIX . 'provider_settings';

	public $account_connected = false;
	public $requires_oauth = false;

	/**
	 * settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * name
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * url
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * prefix
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * instructions
	 *
	 * @var string
	 */
	protected $instructions = '';

	/**
	 * details
	 *
	 * @var string
	 */
	public $details = '';

	/**
	 * save_email
	 * Set to true if we want to store the users email address in the wordpress database
	 * 
	 * @var bool
	 */
	public $save_email = false;

	/**
	 * overrides
	 *
	 * This is used to override the settings
	 * 
	 * @var array
	 */
	protected $overrides = [];

	/**
	 * __construct
	 * Constructor used to set the prefix for each setting
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->prefix = strtolower(str_replace(' ', '_', $this->name)) . '_';
		$this->redirect_uri = admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'oauth_callback');
		$this->update_props();

		parent::__construct($this->prefix, $this->name);
	}

	/**
	 * update_props
	 * Used to update the class properties with the values from the stored settings object
	 *
	 * @return void
	 */
	abstract protected function update_props();

	/**
	 * get_setting_fields
	 *
	 * @return array
	 */
	abstract public function get_setting_fields();

	/**
	 * send_email
	 *
	 * @param  string $email
	 * @param  string $name
	 * @param  array $post_meta
	 * @param  array $overrides
	 * @return void
	 */
	abstract public function send_email($email, $name, $post_meta, $overrides = []);

	//abstract public function authenticate($code);

	/**
	 * get_url
	 *  This will replace any key/value pair in the url with the values set by the admin and return the updated url
	 * 
	 * @return string
	 */
	public function get_url()
	{
		$newUrl = $this->url;
		$vars 	= $this->get_vars();

		// Loop over all the key/value pairs associated with this provider and 
		// replace any that may exist in the url (API)
		foreach ($vars as $key => $value) {
			$newUrl = str_replace('{{' . $key . '}}', $value, $newUrl);
		}

		return $newUrl;
	}

	/**
	 * set_settings_fields
	 * This will loop over the fields array and update the settings key/value pairs
	 * 
	 * @param  mixed $fields
	 * @return void
	 */
	public function set_settings_fields($fields)
	{
		$settings = $this->get_settings();

		if ($fields && is_array($fields)) {
			foreach ($fields as &$field) {
				$settings[$field['key']] = $field['value'];
			}
			$this->set_settings($settings);
		}

		$this->update_props();
	}

	/**
	 * remove_settings
	 * This will remove all associated options settings for this provider
	 * This is typically used when disconnecting a provider
	 *
	 * @return array
	 */
	public function remove_settings()
	{
		$settings = $this->get_settings();

		foreach ($settings as $key => $value) {
			if (str_starts_with($key, $this->prefix)) {
				unset($settings[$key]);
			}
		}
		$this->set_settings($settings);
		return $this->get_setting_fields();
	}

	/**
	 * get_meta_fields
	 *
	 * @return array
	 */
	public function get_meta_fields()
	{
		$fields = $this->get_setting_fields();
		return array_filter($fields, function ($item) {
			return !empty($item['meta']);
		});
	}

	/**
	 * get_meta_field
	 * Returns the array value for the specified key
	 * 
	 * @param  string $key
	 * @return array
	 */
	public function get_meta_field($key = '')
	{
		$fields = $this->get_setting_fields();
		foreach ($fields as $field) {
			if ($field['key'] === $key) {
				return $field;
			}
		}
		return [];
	}

	/**
	 * get_settings
	 * This will return the associated stored settings
	 * 
	 * @return array
	 */
	protected function get_settings()
	{
		if (!isset($this->settings)) {
			$this->settings = get_option(self::PROVIDER_SETTINGS, []);
		}
		return $this->settings;
	}

	/**
	 * set_settings
	 * This will update the associated stored setting and save it to WP
	 * 
	 * @param  array $settings
	 * @return void
	 */
	protected function set_settings($settings)
	{
		$this->settings = $settings;
		update_option(self::PROVIDER_SETTINGS, $this->settings);
	}

	/**
	 * get_instructions
	 *
	 * @return string
	 */
	public function get_instructions()
	{
		return $this->instructions;
	}

	/**
	 * get_details
	 *
	 * @return string
	 */
	public function get_details()
	{
		return $this->details;
	}

	/**
	 * get_vars
	 *
	 * @return array
	 */
	protected function get_vars()
	{
		$fields = $this->get_setting_fields();
		$response = [];

		// Loop over all the fields and get the setting key and value and return them in an array
		foreach ($fields as $field) {
			$key = str_replace($this->prefix, '', $field['key']);
			$value = $field['value'];
			$response[$key] = $value;
		}

		// Override meta data
		if (is_array($this->overrides)) {
			foreach ($this->overrides as $key => $value) {
				$key = str_replace($this->prefix, '', $key);
				$response[$key] = $value;
			}
		}

		return $response;
	}


	/** ===== OAUTH OVERRIDES ===== */
	/**
	 * refresh_token
	 *
	 * @return void
	 */
	public function refresh_token()
	{
		// Ensure we have the latest values to authenticate with
		$this->update_props();

		// Get the refresh token response
		$body = parent::refresh_token();

		// Maybe the token hasn't expired yet
		if (empty($body['expiration_date'])) return;

		// Update the values from the response and save them
		$fields = [];
		foreach ($body as $key => $value) {
			array_push($fields, ['key' => $this->prefix . $key, 'value' => $body[$key]]);
		}
		$this->set_settings_fields($fields);
	}
}
