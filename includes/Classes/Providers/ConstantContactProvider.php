<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes\Providers;

use \Exception;
use \DateTime;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * ConstantContactProvider
 */
class ConstantContactProvider extends Provider
{
	/**
	 * listId
	 *
	 * @var mixed
	 */
	private $list_id;

	/**
	 * list_id_options
	 *
	 * @var array
	 */
	private $list_id_options = [];

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name 				= 'Constant Contact';
		$this->details 				= '';
		$this->url 					= 'https://api.cc.email/v3';
		$this->requires_oauth 		= true;

		// Required for oauth (Oauth.php)
		$this->auth_uri 			= "https://authz.constantcontact.com/oauth2/default/v1/authorize?client_id={{client_id}}&redirect_uri={{redirect_uri}}&response_type=code&scope=contact_data account_read account_update offline_access&state=ssweeeqew";
		$this->token_url 			= "https://authz.constantcontact.com/oauth2/default/v1/token?code={{code}}&redirect_uri={{redirect_uri}}&grant_type=authorization_code";
		$this->refresh_token_url 	= "https://authz.constantcontact.com/oauth2/default/v1/token?refresh_token={{refresh_token}}&grant_type=refresh_token";

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
		$this->account_connected = !empty($settings[$this->prefix . 'client_id']);

		return [
			[
				'key' 		=> $this->prefix . 'client_id',
				'name' 		=> 'API Key (Client Id)',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'client_id']) ? $settings[$this->prefix . 'client_id'] : ''
			],
			[
				'key' 		=> $this->prefix . 'client_secret',
				'name' 		=> 'Client Secret',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'client_secret']) ? $settings[$this->prefix . 'client_secret'] : ''
			],
			[
				'key' 		=> $this->prefix . 'list_id',
				'name' 		=> 'List ID',
				'desc' 		=> '',
				'type' 		=> 'select',
				'required' 	=> true,
				'meta' 		=> true,
				'value' 	=> isset($settings[$this->prefix . 'list_id']) ? $settings[$this->prefix . 'list_id'] : '',
				'options' 	=> isset($settings[$this->prefix . 'list_id_options']) ? maybe_unserialize($settings[$this->prefix . 'list_id_options']) : [['name' => 'Connect account to get list', 'value' => '', 'selected' => 0]],
				'handler' 	=> 'get_options'
			],
			[
				'key' 		=> $this->prefix . 'access_token',
				'name' 		=> 'Access Token',
				'desc' 		=> '',
				'type' 		=> 'hidden',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'access_token']) ? $settings[$this->prefix . 'access_token'] : ''
			],
			[
				'key' 		=> $this->prefix . 'refresh_token',
				'name' 		=> 'Refresh Token',
				'desc' 		=> '',
				'type' 		=> 'hidden',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'refresh_token']) ? $settings[$this->prefix . 'refresh_token'] : ''
			],
			[
				'key' 		=> $this->prefix . 'expires_in',
				'name' 		=> 'Expires In',
				'desc' 		=> '',
				'type' 		=> 'hidden',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'expires_in']) ? $settings[$this->prefix . 'expires_in'] : ''
			],
			[
				'key' 		=> $this->prefix . 'expiration_date',
				'name' 		=> 'Expiration Date',
				'desc' 		=> '',
				'type' 		=> 'hidden',
				'required' 	=> true,
				'meta' 		=> false,
				'value' 	=> isset($settings[$this->prefix . 'expiration_date']) ? $settings[$this->prefix . 'expiration_date'] : ''
			]
		];
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
	 * get_options
	 *
	 * @return array
	 */
	public function get_options()
	{
		$lists = $this->get_lists();
		$options = [];

		foreach ($lists as $list) {
			$list = (array)$list;
			$selected = $this->list_id == $list['list_id'] ? 1 : 0;
			array_push($options, ['name' => $list['name'], 'value' => $list['list_id'], 'selected' => $selected]);

			// Update settings
			$settings = $this->get_settings();
			$settings[$this->prefix . 'list_id_options'] = maybe_serialize($options);
			$this->set_settings($settings);
		}
		return $options;
	}

	/**
	 * get_lists
	 *
	 * @return array
	 */
	public function get_lists()
	{
		try {
			$url = $this->get_url() . '/contact_lists?limit=100&status=active';
			$response = wp_remote_request($url, [
				'method' => 'GET',
				'timeout' => 30,
				'sslverify' => false,
				'headers' => [
					'Authorization' => "Bearer {$this->access_token}",
					'Content-Type' => 'application/json'
				]
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return [];
			}

			$responseBody = wp_remote_retrieve_body($response);
			$responseCode = wp_remote_retrieve_response_code($response);
			
			return in_array($responseCode, [200, 201]) ? json_decode($responseBody)->lists : [];
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return [];
	}

	/**
	 * get_contact
	 *
	 * @param  string $email
	 * @return object
	 */
	private function get_contact($email)
	{
		try {
			$url = $this->get_url() . '/contacts?email=' . $email . '&status=all&include=custom_fields,list_memberships&limit=1';
			$response = wp_remote_request($url, [
				'method' => 'GET',
				'timeout' => 30,
				'sslverify' => false,
				'headers' => [
					'Authorization' => "Bearer {$this->access_token}",
					'Content-Type' => 'application/json'
				]
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return [];
			}

			$responseBody = wp_remote_retrieve_body($response);
			$responseCode = wp_remote_retrieve_response_code($response);
			
			$body = json_decode($responseBody);
			$contact = count($body->contacts) > 0 ? $body->contacts[0] : [];

			return in_array($responseCode, [200, 201]) ? $contact : [];
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return [];
	}

	/**
	 * map_custom_fields
	 * Maps our custom fields to constantContacts custom fields since they require their ID and NOT label
	 * 
	 * @param  mixed $custom_fields
	 * @return array
	 */
	private function map_custom_fields($custom_fields)
	{
		try {
			//$this->client_id = 996da34e-612e-42d3-a696-7edef84af05d
			$url = $this->get_url() . '/contact_custom_fields?lmit=100';
			$response = wp_remote_request($url, [
				'method' => 'GET',
				'timeout' => 30,
				'sslverify' => false,
				'headers' => [
					'Authorization' => "Bearer {$this->access_token}",
					'Content-Type' => 'application/json'
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
				$fields = json_decode($responseBody)->custom_fields;
				foreach ($custom_fields as $key => $value) {
					$id = '';

					foreach ($fields as $field) {
						if ($key === $field->name) {
							$id = $field->custom_field_id;
							break;
						}
					}
					if (!empty($id)) {
						array_push($updated_custom_fields, ['custom_field_id' => $id, 'value' => str_replace("\'", "'", $value)]);
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
	 * update_contact
	 *
	 * @param  mixed $contact
	 * @param  array $custom_fields
	 * @return bool
	 */
	private function update_contact($contact, $custom_fields)
	{
		try {
			// Add the list id
			array_push($contact->list_memberships, $this->list_id);

			// Add the custom fields
			if (!empty($custom_fields)) {
				if (!empty($contact->custom_fields)) {
					//error_log('Updating custom fields...');
					foreach ($custom_fields as $field) {
						$found = false;
						foreach ($contact->custom_fields as $field2) {
							if ($field['custom_field_id'] === $field2->custom_field_id) {
								$found = true;
								break;
							}
						}
						if (!$found) {
							array_push($contact->custom_fields, (object)$field);
						}
					}
				} else {
					$contact->custom_fields = $custom_fields;
				}
			}

			$contact->update_source = 'Contact';

			$url = $this->get_url() . '/contacts/' . $contact->contact_id;
			$response = wp_remote_request($url, [
				'method' => 'PUT',
				'timeout' => 30,
				'sslverify' => false,
				'headers' => [
					'Authorization' => "Bearer {$this->access_token}",
					'Content-Type' => 'application/json'
				],
				'body' => json_encode($contact)
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return false;
			}

			$responseBody = wp_remote_retrieve_body($response);
			$responseCode = wp_remote_retrieve_response_code($response);
			
			if (in_array($responseCode, [200, 201])) {
				return true;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return false;
	}

	/**
	 * create_contact
	 *
	 * @param  string $email
	 * @param  string $name
	 * @param  array $custom_fields
	 * @return bool
	 */
	private function create_contact($email, $name, $custom_fields)
	{
		try {
			$url = $this->get_url() . '/contacts';
			$contact = [
				'email_address' => ['address' => $email, 'permission_to_send' => 'implicit'],
				'first_name' => $name,
				'create_source' => 'Contact',
				'list_memberships' => [$this->list_id],
				'custom_fields' => $custom_fields
			];

			$response = wp_remote_request($url, [
				'method' => 'POST',
				'timeout' => 30,
				'sslverify' => false,
				'headers' => [
					'Authorization' => "Bearer {$this->access_token}",
					'Content-Type' => 'application/json'
				],
				'body' => json_encode($contact)
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return false;
			}

			$responseBody = wp_remote_retrieve_body($response);
			$responseCode = wp_remote_retrieve_response_code($response);
			
			if (in_array($responseCode, [200, 201])) {
				return true;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return false;
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

		// Has our token expired?
		if ($this->has_token_expired()) {
			$this->refresh_token();
		}

		// Map our custom fields to ConstantContacts custom fields as we need to replace name with custom_field_id
		$custom_fields = $this->map_custom_fields($post_meta);

		// docs: https://constantcontact.mashery.com/io-docs
		$contact = $this->get_contact($email);
		if (!empty($contact)) {
			try {
				// Is this contact already in our list?
				$in_list = false;
				foreach ($contact->list_memberships as $list) {
					if ($list == $this->list_id) {
						$in_list = true;
						break;
					}
				}
				if (!empty($name)) {
					$contact->first_name = $name;
				}

				if (!$in_list) {
					// Add contact to list otherwise fail as the user is where he/she/...? needs to be
					return $this->update_contact($contact, $custom_fields);
				} else {
					return true;
				}
			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}
		} else {
			return $this->create_contact($email, $name, $custom_fields);
		}

		return false;
	}
}
