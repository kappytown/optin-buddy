<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes\Providers;

use Inc\Exceptions\EmailSendingException;

use \Exception;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * ConvertKitProvider
 */
class ConvertKitProvider extends Provider
{
	/**
	 * apiKey
	 *
	 * @var mixed
	 */
	private $api_key;

	/**
	 * formId
	 *
	 * @var mixed
	 */
	private $form_id;

	/**
	 * form_id_options
	 *
	 * @var array
	 */
	private $form_id_options = [];

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name 		= 'ConvertKit';
		$this->instructions = 'Please add your API key';
		$this->details 		= '';
		$this->url 			= 'https://api.convertkit.com/v3';

		parent::__construct();
	}

	/**
	 * get_setting_fields
	 *
	 * @return void
	 */
	public function get_setting_fields()
	{
		$settings = $this->get_settings();
		$this->account_connected = !empty($settings[$this->prefix . 'api_key']);

		return [
			[
				'key' 		=> $this->prefix . 'api_key',
				'name' 		=> 'API Key',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'api_key']) ? $settings[$this->prefix . 'api_key'] : ''
			],
			[
				'key' 		=> $this->prefix . 'form_id',
				'name' 		=> 'Form',
				'desc' 		=> 'The form you want the user to subscribe to',
				'type' 		=> 'select',
				'required' 	=> true,
				'meta' 		=> true,
				'value' 	=> isset($settings[$this->prefix . 'form_id']) ? $settings[$this->prefix . 'form_id'] : '',
				'options' 	=> isset($settings[$this->prefix . 'form_id_options']) ? maybe_unserialize($settings[$this->prefix . 'form_id_options']) : [['name' => 'Enter your API Key and click Refresh List', 'value' => '', 'selected' => 0]],
				'handler' 	=> 'get_options'
			]
		];
	}

	/**
	 * update_props
	 *
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
	 * get_options
	 *
	 * @return array
	 */
	public function get_options()
	{
		$forms = $this->get_forms();
		$options = [];

		foreach ($forms as $form) {
			$form = (array)$form;
			$selected = $this->form_id == $form['id'] ? 1 : 0;
			array_push($options, ['name' => $form['name'], 'value' => $form['id'], 'selected' => $selected]);

			// Update settings
			$settings = $this->get_settings();
			$settings[$this->prefix . 'form_id_options'] = maybe_serialize($options);
			$this->set_settings($settings);
		}
		return $options;
	}

	/**
	 * get_forms
	 *
	 * @return array
	 */
	public function get_forms()
	{
		try {
			$url 		= $this->get_url() . '/forms?api_key=' . $this->api_key;
			$response	= wp_remote_request($url, [
				'method' 	=> 'GET',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' => [
					'Content-Type' => 'application/json; charset=utf-8'
				]
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return false;
			}

			$responseBody 	= wp_remote_retrieve_body($response);
			$responseCode 	= wp_remote_retrieve_response_code($response);

			return in_array($responseCode, [200, 201]) ? json_decode($responseBody)->forms : [];
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return [];
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
		$this->overrides = $overrides;
		$this->update_props();

		try {
			$url = "{$this->get_url()}/forms/{$this->form_id}/subscribe";

			$body = [
				'api_key' 	=> $this->api_key,
				'email' 	=> $email,
				'fields' 	 => $post_meta
			];

			if (!empty($name)) {
				$body['first_name'] = $name;
			}

			$response = wp_remote_request($url, [
				'method' 	=> 'POST',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' => [
					'Content-Type' => 'application/json; charset=utf-8'
				],
				'body' => json_encode($body)
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return false;
			}

			$responseBody 	= wp_remote_retrieve_body($response);
			$responseCode 	= wp_remote_retrieve_response_code($response);
			$body 			= json_decode($responseBody);

			if ($responseCode == 200) {
				return true;
			} else {
				error_log($responseBody);
			}
		} catch (Exception $e) {
			throw new EmailSendingException("Failed to send user email to {$this->name}.");
		}
		return false;
	}
}
