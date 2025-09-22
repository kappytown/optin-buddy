<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes\Providers;

use \DateTime;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Oauth
 * 
 * This class is for providers that require authentication using oauth
 */
class Oauth
{
	/**
	 * redirect_uri
	 *
	 * @var string
	 */
	protected $redirect_uri = '';

	/**
	 * auth_uri
	 *
	 * @var string
	 */
	protected $auth_uri = '';

	/**
	 * token_url
	 *
	 * @var string
	 */
	protected $token_url = '';

	/**
	 * refresh_token_url
	 *
	 * @var string
	 */
	protected $refresh_token_url = '';

	/**
	 * access_token
	 *
	 * @var mixed
	 */
	protected $access_token;

	/**
	 * refresh_token
	 *
	 * @var mixed
	 */
	protected $refresh_token;

	/**
	 * expires_in
	 *
	 * @var mixed
	 */
	protected $expires_in;

	/**
	 * expires_in
	 *
	 * @var mixed
	 */
	protected $expiration_date;

	/**
	 * client_id
	 *
	 * @var mixed
	 */
	protected $client_id;

	/**
	 * client_secret
	 *
	 * @var mixed
	 */
	protected $client_secret;

	/**
	 * hook
	 * This is the name of the hook used for the refresh token cron event
	 * 
	 * @var string
	 */
	private $hook = 'opt_bud_refresh_token_hook';

	/**
	 * name
	 * 
	 * Name of the provider used in the render_oauth method
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * prefix
	 * The provider prefix since all provider values are stored in the same settings option
	 *
	 * @var string
	 */
	private $prefix = '';

	/**
	 * __construct
	 *
	 * @param  mixed $prefix
	 * @param  mixed $name
	 * @return void
	 */
	public function __construct($prefix, $name)
	{
		$this->prefix = $prefix;
		$this->name = $name;
		//$this->hook = $prefix . 'refresh_token_hook';
	}

	/**
	 * get_auth_uri
	 *
	 * @return string
	 */
	public function get_auth_uri()
	{
		return $this->auth_uri;
	}

	/**
	 * on_authenticated
	 * Called once the user saves the provider settings
	 * Since they may decide to delay the save functionality, 
	 * we can't use expires_in as the offset.
	 * 
	 * @return void
	 */
	public function on_authenticated()
	{
		// Schedule the event when you get the token
		if (!wp_next_scheduled($this->hook)) {
			$remaining_seconds  = $this->get_token_remaining_time();
			$offset_seconds = $remaining_seconds < $this->expires_in ? $remaining_seconds : $this->expires_in;
			$time = time();

			wp_schedule_event($time + $offset_seconds - DAY_IN_SECONDS, 'daily', $this->hook);
		}
	}

	/**
	 * on_unauthenticated
	 *
	 * Removes the scheduled cron job
	 * 
	 * @return void
	 */
	public function on_unauthenticated()
	{
		wp_clear_scheduled_hook($this->hook);
	}

	/**
	 * has_token_expired
	 * Returns true if the token has less than 60 minutes before expiration
	 *
	 * @return bool
	 */
	protected function has_token_expired()
	{
		if (!empty($this->expiration_date)) {
			// Get the remaining time in seconds
			$remaining_seconds = $this->get_token_remaining_time();

			// Get the remaining time in minutes
			$remaining_minutes = floor($remaining_seconds / 60);

			// Return true if token has less than 1 hour to expire
			return $remaining_minutes < 60;
		}
		return true;
	}

	/**
	 * get_token_remaining_time
	 * Returns the amount of seconds before the token expires
	 *
	 * @return int
	 */
	private function get_token_remaining_time()
	{
		if (!empty($this->expiration_date)) {
			$start = new DateTime();
			$end = new DateTime($this->expiration_date);
			return $end->getTimestamp() - $start->getTimestamp();
		}
		return 0;
	}

	/**
	 * get_token
	 *
	 * @param  string $code
	 * @param  string $client_id
	 * @param  string $client_secret
	 * @return array
	 */
	public function get_token($code, $client_id = '', $client_secret = '')
	{
		$client_id = !empty($client_id) ? $client_id : $this->client_id;
		$client_secret = !empty($client_secret) ? $client_secret : $this->client_secret;
		$token_url = str_replace('{{code}}', $code, $this->token_url);
		$token_url = str_replace('{{redirect_uri}}', urlencode($this->redirect_uri), $token_url);

		$credentials = base64_encode("{$client_id}:{$client_secret}");

		$response = wp_remote_post($token_url, array(
			'headers' => [
				'Authorization' => 'Basic ' . $credentials,
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept' => 'application/json'
			]
		));

		$responseBody = wp_remote_retrieve_body($response);
		$responseCode = wp_remote_retrieve_response_code($response);

		if (is_wp_error($response)) {
			// Handle error
			error_log($response->get_error_message());
			return [];
		}

		$body = json_decode($responseBody, true);
		$body['response_code'] = $responseCode;

		if ($responseCode === 200) {
			$this->access_token = $body['access_token'];
			$this->refresh_token = $body['refresh_token'];
			$this->expires_in = $body['expires_in'];
			$this->expiration_date = date("Y-m-d H:i:s", strtotime("+{$body['expires_in']} sec"));
			return $body;
		} else {
			error_log("$responseCode: $responseBody");
		}
		return $body;
	}

	/**
	 * refresh_token
	 *
	 * @return array
	 */
	public function refresh_token()
	{
		// do we need to refresh our token?
		//if (!$this->has_token_expired()) return [];

		$refresh_token_url = str_replace('{{refresh_token}}', $this->refresh_token, $this->refresh_token_url);
		$credentials = base64_encode("{$this->client_id}:{$this->client_secret}");

		// There is no need to refresh the token if we don't have a refresh token url
		if (empty($refresh_token_url)) {
			return [];
		}

		$response = wp_remote_post($refresh_token_url, array(
			'headers' => [
				'Authorization' => 'Basic ' . $credentials,
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept' => 'application/json'
			]
		));

		if (is_wp_error($response)) {
			// Handle error
			error_log($response->get_error_message());
			return;
		}

		$responseBody = wp_remote_retrieve_body($response);
		$responseCode = wp_remote_retrieve_response_code($response);

		if ($responseCode === 200) {
			$body = json_decode($responseBody, true);
			$body['expiration_date'] = date("Y-m-d H:i:s", strtotime("+{$body['expires_in']} sec"));
			return $body;

			// Save settings
		} else {
			/*
				TODO: Handle 401 response code. Customer needs to be notified immediately as all emails will fail
			*/
			error_log($responseBody);
		}
		return [];
	}
}
