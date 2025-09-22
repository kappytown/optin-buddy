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
 * ActiveCampaignProvider
 */
class ActiveCampaignProvider extends Provider
{
	/**
	 * ccount_name
	 *
	 * @var mixed
	 */
	private $account_name;

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
		$this->name 	= 'ActiveCampaign';
		$this->details 	= '';
		$this->url 		= 'https://{{account_name}}.api-us1.com/api/3/contacts';

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
				'key' 		=> $this->prefix . 'account_name',
				'name' 		=> 'Account Name',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'account_name']) ? $settings[$this->prefix . 'account_name'] : ''
			],
			[
				'key' 		=> $this->prefix . 'api_key',
				'name' 		=> 'API Key',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'api_key']) ? $settings[$this->prefix . 'api_key'] : ''
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
	 * map_custom_fields
	 * 
	 * Maps our custom fields to activecampaign custom fields since they require their id and NOT label
	 *
	 * @param  mixed $custom_fields
	 * @return void
	 */
	private function map_custom_fields($custom_fields)
	{
		try {
			$url = 'https://' . $this->account_name . '.api-us1.com/api/3/fields?limit=100';

			$response = wp_remote_request($url, [
				'method' 	=> 'GET',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' => [
					'Api-Token' => $this->api_key,
					'Accept' => 'application/json'
				]
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return [];
			}

			$responseBody 		= wp_remote_retrieve_body($response);
			$responseCode 		= wp_remote_retrieve_response_code($response);
			$responseMessage 	= wp_remote_retrieve_response_message($response);

			$updated_custom_fields = [];
			if ($responseCode === 200) {
				$fields = json_decode($responseBody)->fields;
				foreach ($custom_fields as $key => $value) {
					$id = '';
					foreach ($fields as $field) {
						if ($key === $field->perstag) {
							$id = $field->id;
							break;
						}
					}
					if (!empty($id)) {
						array_push($updated_custom_fields, ['field' => $id, 'value' => $value]);
					}
				}
			} else {
				error_log("($responseCode) body: $responseBody, message: $responseMessage");
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
	 * @return void
	 */
	public function send_email($email, $name, $post_meta, $overrides = [])
	{
		$this->overrides = $overrides;
		$this->update_props();

		try {
			$body = ['contact' => [
				'email' 		=> $email,
				'fieldValues' 	 => $this->map_custom_fields($post_meta)
			]];

			if (!empty($name)) {
				$body['contact']['firstName'] = $name;
			}

			$response = wp_remote_request($this->get_url(), [
				'method' 	=> 'POST',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' => [
					'Api-Token' => $this->api_key,
					'Content-Type' => 'application/json; charset=utf-8',
					'Accept' => 'application/json'
				],
				'body' => json_encode($body)
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return false;
			}

			$responseBody 		= wp_remote_retrieve_body($response);
			$responseCode 		= wp_remote_retrieve_response_code($response);
			$responseMessage 	= wp_remote_retrieve_response_message($response);
			$body 				= json_decode($responseBody);

			if ($responseCode === 200 || $responseCode === 201) {
				return true;
			} else {
				if (!empty($body)) {
					if (!empty($body->errors) && !empty($body->errors[0]->title)) {
						throw new EmailSendingException("($responseCode) {$body->errors[0]->title}: {$body->errors[0]->detail}");
					}
				} else {
					throw new EmailSendingException("($responseCode) $responseMessage");
				}
			}
		} catch (EmailSendingException $e) {
			throw $e;
		} catch (Exception $e) {
			throw new EmailSendingException("Failed to send user email to {$this->name}.");
		}
		return false;
	}
}
