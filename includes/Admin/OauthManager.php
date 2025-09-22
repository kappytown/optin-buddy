<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

require_once dirname(__FILE__) . '/../../constants.php';

use Inc\Admin\BaseAdmin;
use Inc\Classes\EmailProvider;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * OauthManager
 */
class OauthManager extends BaseAdmin
{
	private $session;
	private $provider;
	private $provider_prefix;
	private $provider_name;
	private $client_id = '';
	private $client_secret = '';
	private $access_token = '';
	private $refresh_token = '';
	private $expires_in = '';
	private $expiration_date = '';
	private $close_page = false;
	private $has_invalid_credentials = false;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Exit if executed when page is not loaded
		$page = isset($_GET['page']) ? $_GET['page'] : '';
		if ($page !== OPTIN_BUDDY_PREFIX . 'oauth_callback') return;

		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
		add_action('wp_loaded', [$this, 'on_page_loaded']);

		$this->session = get_option(OPTIN_BUDDY_PREFIX . 'session');
		if (!$this->session) {
			$this->session = ['oauth' => ['provider' => '', 'credentials' => []]];
			add_option(OPTIN_BUDDY_PREFIX . 'session', $this->session);
		}
		$provider = isset($_GET['provider']) ? $_GET['provider'] : $this->session['oauth']['provider'];

		if (!empty($provider)) {
			// Get the Provider Class
			$EmailProvider = new EmailProvider($provider);
			$this->provider = $EmailProvider->get_provider();
			$this->provider_name = $this->provider->name;
			$this->provider_prefix = $this->provider->prefix;
		}
	}

	/**
	 * enqueue_admin_scripts
	 *
	 * @param  mixed $hook_suffix
	 * @return void
	 */
	public function enqueue_admin_scripts($hook_suffix)
	{
		// Only load the setup scripts if on our settings page
		if (str_contains($hook_suffix, OPTIN_BUDDY_PREFIX . 'oauth_callback')) {
			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-settings-oauth', OPTIN_BUDDY_URL . 'assets/css/admin-oauth.css', [], OPTIN_BUDDY_VERSION);
		}
	}

	/**
	 * oauth_redirect
	 * This gets called before headers are sent
	 * 
	 * Redirects to the providers url for authentication
	 * This gets envoked once the client_id has been posted
	 *
	 * @return void
	 */
	public function on_page_loaded()
	{
		$redirect_uri = admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'oauth_callback');

		// The provider is in the GET request when first loading this page
		if (isset($_GET['provider'])) {
			$provider = sanitize_text_field($_GET['provider']);
			$client_id = sanitize_text_field($_GET['client_id']);
			$client_secret = sanitize_text_field($_GET['client_secret']);

			$EmailProvider = new EmailProvider($provider);
			$this->provider = $EmailProvider->get_provider();
			$auth_uri = $this->provider->get_auth_uri();

			// Store the selected provider so that we can get it once we get the code
			// Does our option exist?
			$this->session['oauth'] = [
				'provider' => $provider,
				'credentials' => [
					'client_id' => $client_id,
					'client_secret' => $client_secret
				]
			];
			update_option(OPTIN_BUDDY_PREFIX . 'session', $this->session);
			// Redirect to authenticate via oauth

			$auth_uri = str_replace('{{redirect_uri}}', urlencode($redirect_uri), $auth_uri);
			$auth_uri = str_replace('{{client_id}}', $client_id, $auth_uri);
			$auth_uri = sanitize_url($auth_uri);

			wp_redirect($auth_uri);
			exit;
		}

		if (isset($_GET['code'])) {
			$code = sanitize_text_field($_GET['code']);
			$this->client_id = isset($this->session['oauth']['credentials']['client_id']) ? $this->session['oauth']['credentials']['client_id'] : '';
			$this->client_secret = isset($this->session['oauth']['credentials']['client_secret']) ? $this->session['oauth']['credentials']['client_secret'] : '';

			// Gets the access and refresh tokens
			$auth = $this->provider->get_token($code, $this->client_id, $this->client_secret);

			if (!empty($auth)) {
				if ($auth['response_code'] !== 200) {
					if ($auth['response_code'] === 401) {
						error_log('The API Key and or Client Secret are incorrect.');
						$this->has_invalid_credentials = true;
					} else {
						// There must have been an error attempting to authenticate
						// This can happen if the user refreshes and we use an expired code
					}
				} else {
					$this->access_token = $auth['access_token'];
					$this->refresh_token = $auth['refresh_token'];
					$this->expires_in = intval($auth['expires_in']);
					$this->expiration_date = date("Y-m-d H:i:s", strtotime("+{$auth['expires_in']} sec"));

					$this->provider->set_settings_fields([
						['key' => $this->provider_prefix . 'access_token', 'value' => $this->access_token],
						['key' => $this->provider_prefix . 'refresh_token', 'value' => $this->refresh_token],
						['key' => $this->provider_prefix . 'expires_in', 'value' => $this->expires_in],
						['key' => $this->expiration_date . 'access_token', 'value' => $this->expiration_date]
					]);
				}
			}
			$this->close_page = true;
		}
	}

	/**
	 * render_page
	 * Renders the associated page
	 *
	 * @return void
	 */
	public function render_page()
	{
		require_once OPTIN_BUDDY_DIR . 'templates/admin/oauth-callback.php';
	}
}
