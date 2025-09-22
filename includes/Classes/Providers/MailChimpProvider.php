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
 * MailChimpProvider
 */
class MailChimpProvider extends Provider
{
	/**
	 * apiKey
	 * 
	 * @var mixed
	 */
	private $api_key;

	/**
	 * audience_id
	 * 
	 * audience id = Audience -> All Contacts -> Settings -> Audience Name and Campaign Defaults -> Audience ID
	 *
	 * @var mixed
	 */
	private $audience_id;

	/**
	 * double_opt_in
	 *
	 * If set, we set the status to pending which will require the new subscriber to opt-in via email
	 * 
	 * @var mixed
	 */
	private $double_opt_in;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name 		= 'MailChimp';
		$this->details 		= '';
		$this->url 			= 'https://{{data_center}}.api.mailchimp.com/3.0/lists/{{audience_id}}/members';

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
				'key' 		=> $this->prefix . 'audience_id',
				'name' 		=> 'Audience ID',
				'desc' 		=> '',
				'type' 		=> 'text',
				'required' 	=> true,
				'meta' 		=> true,
				'value' 	=> isset($settings[$this->prefix . 'audience_id']) ? $settings[$this->prefix . 'audience_id'] : ''
			],
			[
				'key' 		=> $this->prefix . 'double_opt_in',
				'name' 		=> 'Double Opt-In',
				'desc' 		=> 'This will require the subscriber to opt-in via an email sent by MailChimp.',
				'type' 		=> 'checkbox',
				'required' 	=> false,
				'meta' 		=> true,
				'value' 	=> isset($settings[$this->prefix . 'double_opt_in']) ? $settings[$this->prefix . 'double_opt_in'] : ''
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
	 * @return bool
	 */
	public function send_email($email, $name, $post_meta, $overrides = [])
	{
		// We need to set the value for the double opt in since it will not be present if unchecked
		$doi = $this->prefix . 'double_opt_in';
		$overrides[$doi] = isset($overrides[$doi]) ? $overrides[$doi] : '';
		$this->overrides = $overrides;
		$this->update_props();

		$url 			= $this->get_url();
		$data_center 	= SUBSTR($this->api_key, STRPOS($this->api_key, '-') + 1);
		$email_hash 	= md5(strtolower($email));
		$url 			= str_replace('{{data_center}}', $data_center, $url) . '/' . $email_hash . '?skip_merge_validation=true';
		$status_if_new 	= empty($this->double_opt_in) ? 'subscribed' : 'pending';

		// Add the name field to the merge_fields if not empty
		if (!empty($name)) {
			$post_meta['FNAME'] = $name;
		}

		try {
			$response = wp_remote_request($url, [
				'method' => 'PUT',
				'timeout' => 30,
				'sslverify' => false,
				'headers' => [
					'Authorization' => "apikey {$this->api_key}",
					'Content-Type' => 'application/json'
				],
				'body' => json_encode([
					'email_address' => $email,
					'status' => 'subscribed',
					'status_if_new' => $status_if_new,
					'merge_fields' => $post_meta
				])
			]);

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return false;
			}

			$responseBody 	= wp_remote_retrieve_body($response);
			$responseCode 	= wp_remote_retrieve_response_code($response);
			$body 			= json_decode($responseBody);

			if ($responseCode == 200) {
				// Successfully subscribed or updated
				return true;
			} else if ($responseCode == 400) {
				// Bad Request - possibly because the email was permanently deleted
				if (isset($body->title) && $body->title == 'Forgotten Email Not Subscribed') {
					//return false;
					// Deleting contacts is a permanent action and cannot be undone. 
					// Once a contact has been deleted they cannot be re-added to the same audience. 
					// They would need to rejoin the audience by submitting a Mailchimp signup form. 
					// The hosted Mailchimp form can be found by following the steps in this guide: https://eepurl.com/dyimL9
				} else {
					// Handle other types of Bad Request errors
				}
			} else {
				// 403 Forbidden 			- caused by incorrect api_key most likely 		"The API key provided is linked ... "
				// 404 Resource Not Found 	- caused by incorrect audience_id most likely	"The requested resource could not be found..."
				// Handle API errors
				// Log or display error data
			}
			error_log($responseBody);
			if (!empty($body->title)) {
				throw new EmailSendingException("($responseCode) {$body->title}: {$body->detail}");
			}
		} catch (EmailSendingException $e) {
			throw $e;
		} catch (Exception $e) {
			throw new EmailSendingException("Failed to send user email to {$this->name}.");
		}

		return false;
	}
}
