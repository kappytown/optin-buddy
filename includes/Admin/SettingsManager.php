<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Admin\BaseAdmin;
use Inc\Admin\SettingsFormHandler;
use Inc\Classes\Settings;
use Inc\Classes\Form;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * SettingsManager
 */
class SettingsManager extends BaseAdmin
{
	/**
	 * vendors
	 * List of supported email vendors
	 * 
	 * @var array
	 */
	private $vendors = [];

	/**
	 * form_handler
	 *
	 * @var class SettingsFormHandler
	 */
	private $form_handler;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		error_log(trim($_SERVER['REQUEST_URI'], '/'));
		$this->form_handler = new SettingsFormHandler();

		$this->vendors = [
			[
				'is_enabled' => true,
				'name' => 'ActiveCampaign',
				'key' => 'activecampaign'
			],
			[
				'is_enabled' => false,
				'name' => 'AWeber',
				'key' => 'aweber'
			],
			[
				'is_enabled' => false,
				'name' => 'Brevo (Sendinblue)',
				'key' => 'brevo'
			],
			[
				'is_enabled' => true,
				'name' => 'Constant Contact',
				'key' => 'constant_contact'
			],
			[
				'is_enabled' => true,
				'name' => 'ConvertKit',
				'key' => 'convertkit'
			],
			[
				'is_enabled' => false,
				'name' => 'Drip',
				'key' => 'drip'
			],
			[
				'is_enabled' => true,
				'name' => 'flodesk',
				'key' => 'flodesk'
			],
			[
				'is_enabled' => false,
				'name' => 'GetResponse',
				'key' => 'getresponse'
			],
			[
				'is_enabled' => false,
				'name' => 'Keap',
				'key' => 'keap'
			],
			[
				'is_enabled' => true,
				'name' => 'MailChimp',
				'key' => 'mailchimp'
			],
			[
				'is_enabled' => true,
				'name' => 'MailerLite',
				'key' => 'mailerlite'
			],
			[
				'is_enabled' => false,
				'name' => 'SendGrid',
				'key' => 'sendgrid'
			],
			[
				'is_enabled' => true,
				'name' => 'WordPress',
				'key' => 'wordpress'
			]
		];

		// Only add to admin area
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

		// Handles the ajax request. Be sure to post the action handle_form_submission_request in the ajax request
		add_action('wp_ajax_handle_settings_select_provider', [$this->form_handler, 'handle_settings_select_provider_request']);
		add_action('wp_ajax_handle_settings_disconnect_provider', [$this->form_handler, 'handle_settings_disconnect_provider_request']);
		add_action('wp_ajax_handle_settings_refresh_list', [$this->form_handler, 'handle_settings_refresh_list_request']);
		add_action('wp_ajax_handle_settings_list_forms', [$this->form_handler, 'handle_settings_list_forms_request']);
		add_action('wp_ajax_handle_settings_new_nonce', [$this->form_handler, 'handle_settings_new_nonce_request']);
		add_action('admin_post_save_settings_form_action', [$this->form_handler, 'handle_save_settings_request']);

		parent::__contstruct();
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
		if (str_contains($hook_suffix, OPTIN_BUDDY_PREFIX . 'main_settings')) {
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-settings', OPTIN_BUDDY_URL . 'assets/js/admin-settings.js', ['jquery'], OPTIN_BUDDY_VERSION);

			// To handle AJAX requests
			wp_localize_script(OPTIN_BUDDY_PREFIX . 'backend-settings', 'AdminSetupAjax', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce(OPTIN_BUDDY_PREFIX . 'setup_nonce')
			]);

			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-settings', OPTIN_BUDDY_URL . 'assets/css/admin-settings.css', [], OPTIN_BUDDY_VERSION);
		}
	}


	/**
	 * render_page
	 *
	 * Renders the associated page
	 * 
	 * @return void
	 */
	public function render_page()
	{
		$settings = new Settings();
		$provider = isset($settings->provider) ? $settings->provider : '';
		
		require_once OPTIN_BUDDY_DIR . 'templates/admin/settings-page.php';
	}

	/**
	 * render_providers
	 *
	 * @param  mixed $provider
	 * @return void
	 */
	public function render_providers($provider = '')
	{
		require_once OPTIN_BUDDY_DIR . 'templates/admin/setup-providers.php';
	}

	/**
	 * get_forms
	 *
	 * @return void
	 */
	public function get_forms()
	{
		return Form::read_forms(20);
	}
}
