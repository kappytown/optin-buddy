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
 * MailerLiteProvider
 */
class MailerLiteProvider extends Provider
{
	/**
	 * apiKey
	 *
	 * @var mixed
	 */
	private $api_key;

	/**
	 * groupId
	 *
	 * @var mixed
	 */
	private $group_id;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name 	= 'MailerLite';
		$this->details 	= '';
		$this->url 		= 'https://connect.mailerlite.com/api/subscribers';

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
				'key' 		=> $this->prefix . 'api_key',
				'name' 		=> 'API Key',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'api_key']) ? $settings[$this->prefix . 'api_key'] : ''
			],
			[
				'key' 		=> $this->prefix . 'group_id',
				'name' 		=> 'Group ID',
				'desc' 		=> '',
				'type' 		=> 'number',
				'required' 	=> false,
				'meta' 		=> true,
				'value' 	=> isset($settings[$this->prefix . 'group_id']) ? $settings[$this->prefix . 'group_id'] : ''
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
	public function send_email($email, $name, $post_meta, $overrides = [])
	{
		$this->overrides = $overrides;
		$this->update_props();

		try {
			$body = [
				'email' 	=> $email,
				'fields' 	 => $post_meta
			];

			if (!empty($this->group_id)) {
				$body['groups'] = [$this->group_id];
			}

			if (!empty($name)) {
				$body['fields']['name'] = $name;
			}

			$response = wp_remote_request($this->get_url(), [
				'method' 	=> 'POST',
				'timeout' 	=> 30,
				'sslverify' => false,
				'headers' => [
					'Authorization' => "Bearer {$this->api_key}",
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

			//[04-Dec-2023 01:54:06 UTC] {"data":{"id":"106591072008799436","email":"kappytown@yahoo.com","status":"active","source":"manual","sent":0,"opens_count":0,"clicks_count":0,"open_rate":0,"click_rate":0,"ip_address":null,"subscribed_at":"2023-12-04 01:26:07","unsubscribed_at":null,"created_at":"2023-12-04 01:26:07","updated_at":"2023-12-04 01:54:06","fields":{"name":"Trevor Nielson","last_name":null,"company":null,"country":null,"city":null,"phone":null,"state":null,"z_i_p":null},"groups":[{"id":"106592185511576735","name":"Default FOM Group","active_count":0,"sent_count":0,"opens_count":0,"open_rate":{"float":0,"string":"0%"},"clicks_count":0,"click_rate":{"float":0,"string":"0%"},"unsubscribed_count":0,"unconfirmed_count":0,"bounced_count":0,"junk_count":0,"created_at":"2023-12-04 01:43:49"}],"location":null,"opted_in_at":null,"optin_ip":null}}

			if ($responseCode == 200) {
				return true;
			} else {
				error_log("$responseBody");
				if (!empty($body->message)) {
					throw new EmailSendingException("($responseCode) {$body->message}");
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
