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
 * FlodeskProvider
 */
class FlodeskProvider extends Provider
{
	/**
	 * apiKey
	 *
	 * @var mixed
	 */
	private $api_key;

	/**
	 * segmentId
	 *
	 * @var mixed
	 */
	private $segment_id;

	/**
	 * segment_id_options
	 *
	 * @var array
	 */
	private $segment_id_options = [];

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name 		= 'flodesk';
		$this->instructions = 'Please add your API key';
		$this->details 		= '';
		$this->url 			= 'https://api.flodesk.com/v1';

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
				'key' 		=> $this->prefix . 'segment_id',
				'name' 		=> 'Segment',
				'desc' 		=> 'The segment you want the user to subscribe to',
				'type' 		=> 'select',
				'required' 	=> true,
				'meta' 		=> true,
				'value' 	=> isset($settings[$this->prefix . 'segment_id']) ? $settings[$this->prefix . 'segment_id'] : '',
				'options' 	=> isset($settings[$this->prefix . 'segment_id_options']) ? maybe_unserialize($settings[$this->prefix . 'segment_id_options']) : [['name' => 'Enter your API Key and click Refresh List', 'value' => '', 'selected' => 0]],
				'handler' 	=> 'get_options'
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
	 * get_options
	 *
	 * @return array
	 */
	public function get_options()
	{
		$segments = $this->get_segments();
		$options = [];

		foreach ($segments as $segment) {
			$segment = (array)$segment;
			$selected = $this->segment_id == $segment['id'] ? 1 : 0;
			array_push($options, ['name' => $segment['name'], 'value' => $segment['id'], 'selected' => $selected]);

			// Update settings
			$settings = $this->get_settings();
			$settings[$this->prefix . 'segment_id_options'] = maybe_serialize($options);
			$this->set_settings($settings);
		}
		return $options;
	}

	/**
	 * get_segments
	 * Returns a list of segments
	 * 
	 * @return array
	 */
	public function get_segments()
	{
		try {
			$url 		= $this->get_url() . '/segments?per_page=100';
			$response	= wp_remote_request($url, [
				'method' 	=> 'GET',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' 	=> [
					'Content-Type' 	=> 'application/json; charset=utf-8',
					'User-Agent' 	=> 'OptinBuddy (www.optinbuddy.com)',
					'Authorization' => 'Basic ' . $this->api_key
				]
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return false;
			}

			$responseBody 	= wp_remote_retrieve_body($response);
			$responseCode 	= wp_remote_retrieve_response_code($response);

			return in_array($responseCode, [200, 201]) ? json_decode($responseBody)->data : [];
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return [];
	}

	/**
	 * subsribe_to_segment
	 * Subscribes the email to the specified segment
	 *
	 * @param  string $email
	 * @param  array $segments
	 * @return bool
	 */
	public function subsribe_to_segment($email, $segments = [])
	{
		try {
			$url = "{$this->get_url()}/subscribers/{$email}/segments";

			$body = [
				'segment_ids' => $segments
			];

			$response = wp_remote_request($url, [
				'method' 	=> 'POST',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' 	=> [
					'Content-Type' 	=> 'application/json; charset=utf-8',
					'User-Agent' 	=> 'OptinBuddy (www.optinbuddy.com)',
					'Authorization' => 'Basic ' . $this->api_key
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

			if (in_array($responseCode, [200, 201])) {
				return true;
			} else {
				error_log($responseBody);
			}
		} catch (Exception $e) {
			throw new EmailSendingException("Failed to add email to flodesk segment ({$this->segment_id}).");
		}

		return false;
	}

	/**
	 * map_custom_fields
	 * Maps our custom fields to flodesk custom fields since they require their key and NOT label
	 * 
	 * @param  mixed $custom_fields
	 * @return array
	 */
	private function map_custom_fields($custom_fields)
	{
		try {
			$url = "{$this->get_url()}/custom-fields?page_page=100";
			$response = wp_remote_request($url, [
				'method' 	=> 'GET',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' 	=> [
					'Content-Type' 	=> 'application/json; charset=utf-8',
					'User-Agent' 	=> 'OptinBuddy (www.optinbuddy.com)',
					'Authorization' => 'Basic ' . $this->api_key
				]
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return [];
			}

			$responseBody = wp_remote_retrieve_body($response);
			$responseCode = wp_remote_retrieve_response_code($response);

			$updated_custom_fields = [];
			if (in_array($responseCode, [200, 201])) {
				$fields = json_decode($responseBody)->data;
				// Loop over our custom fields array
				foreach ($custom_fields as $key => $value) {
					$id = '';

					// Loop over flodesk custom fields to see if it contains our custom field
					foreach ($fields as $field) {
						// Custom field match!
						if ($key === $field->label) {
							$id = $field->key;
							break;
						}
					}

					// Match found so update our new custom fields array
					if (!empty($id)) {
						$updated_custom_fields[$id] = str_replace("\'", "'", $value);
					}
				}
			} else {
				error_log($responseBody);
			}

			return $updated_custom_fields;
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
			$url = "{$this->get_url()}/subscribers";

			$body = [
				'email' => $email,
				'custom_fields' => $this->map_custom_fields($post_meta)
			];

			if (!empty($name)) {
				$body['first_name'] = $name;
			}

			$response = wp_remote_request($url, [
				'method' 	=> 'POST',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' 	=> [
					'Content-Type' 	=> 'application/json; charset=utf-8',
					'User-Agent' 	=> 'OptinBuddy (www.optinbuddy.com)',
					'Authorization' => 'Basic ' . $this->api_key
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

			if (in_array($responseCode, [200, 201])) {
				// Add the new subscriber to the specified segment
				return $this->subsribe_to_segment($email, [$this->segment_id]);
			} else {
				error_log($responseBody);
			}
		} catch (Exception $e) {
			throw new EmailSendingException("Failed to send user email to {$this->name}.");
		}
		return false;
	}
}
