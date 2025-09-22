<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Admin\BaseAdmin;
use Inc\Classes\EmailProvider;
use Inc\Classes\Settings;
use Inc\Classes\Form;

use \Exception;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * SettingsFormHandler
 */
class SettingsFormHandler extends BaseAdmin
{
	private $nonce = OPTIN_BUDDY_PREFIX . 'setup_nonce';

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * handle_settings_new_nonce_request
	 * 
	 * Returns the new nonce
	 *
	 * @return void
	 */
	public function handle_settings_new_nonce_request()
	{
		$nonce = wp_create_nonce($this->nonce);
		wp_send_json(['success' => true, 'nonce' => $nonce]);
	}

	/**
	 * handle_settings_select_provider_request
	 *
	 * @return void
	 */
	public function handle_settings_select_provider_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['provider'])) {
			$response = ['suceess' => false, 'error' => 'Invalid Provider'];
			wp_send_json($response);
			die;
		}

		$provider = sanitize_text_field($_POST['provider']);

		try {
			// Get the provider details
			$EmailProvider 	= new EmailProvider($provider);
			$Provider 		= $EmailProvider->get_provider();

			$response = [
				'success' 			=> true,
				'fields' 			=> $Provider->get_setting_fields(),
				'oauth' 			=> $Provider->requires_oauth,
				'cron_disabled' 	=> OPTIN_BUDDY_CRON_DISABLED ? 1 : 0,
				'account_connected' => $Provider->account_connected,
				'details' 			=> $Provider->details
			];
		} catch (Exception $e) {
			error_log($e);
			$response = [
				'success' 	=> false,
				'error' 	=> 'Email Provider not found!'
			];
		}

		wp_send_json($response);
		wp_die();
	}

	/**
	 * handle_settings_disconnect_provider_request
	 *
	 * @return void
	 */
	public function handle_settings_disconnect_provider_request()
	{
		global $wpdb;

		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['provider'])) {
			$response = ['suceess' => false, 'error' => 'Invalid Provider'];
			wp_send_json($response);
			die;
		}

		$provider = sanitize_text_field($_POST['provider']);
		try {
			// Get the provider details
			$EmailProvider 		= new EmailProvider($provider);
			$Provider 			= $EmailProvider->get_provider();
			$provider_fields 	= $Provider->remove_settings();

			$settings = new Settings();
			if ($settings->provider === $provider) {
				$settings->provider = '';
				$settings->update();

				// For oauth providers...
				if (method_exists($Provider, 'on_unauthenticated')) {
					$Provider->on_unauthenticated();
				}
			}

			// Remove provider specifiic meta data tied to each form
			$query_results = Form::read_forms(1000);
			$results = $query_results['results'];

			if (is_array($results)) {
				foreach ($results as &$result) {
					$result = (array)$result;

					$updated = false;
					$newMeta = maybe_unserialize($result['meta']);
					foreach ($newMeta as $key => $value) {
						foreach ($provider_fields as $field) {
							if ($key === $field['key']) {
								$updated = true;
								unset($newMeta[$key]);
							}
						}
					}
					$result['meta'] = maybe_serialize($newMeta);
					if ($updated) {
						Form::update_form($result);
					}

					if (!empty($meta)) {
					}
				}
			}
		} catch (Exception $e) {
			error_log($e);
			$response = [
				'success' => false,
				'error' => 'Unable to disconnect provider.'
			];
			wp_send_json($response);
			die;
		}
	}

	/**
	 * handle_settings_refresh_list_request
	 * 
	 * This will load the providers select options list specified by the handler variable
	 *
	 * @return void
	 */
	public function handle_settings_refresh_list_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['provider']) || !isset($_POST['handler'])) {
			$response = ['suceess' => false, 'error' => 'Invalid Provider'];
			wp_send_json($response);
			die;
		}

		$provider = sanitize_text_field($_POST['provider']);
		$handler = sanitize_text_field($_POST['handler']);

		try {
			// Get the provider details
			$EmailProvider 	= new EmailProvider($provider);
			$Provider 		= $EmailProvider->get_provider();

			// Set the settings fields if needed
			$data = [];
			foreach ($_POST as $key => $value) {
				array_push($data, ['key' => $key, 'value' => $value]);
			}
			$Provider->set_settings_fields($data);

			$response = [
				'success' 	=> true,
				'options' 	=> $Provider->$handler()
			];
		} catch (Exception $e) {
			error_log($e);
			$response = [
				'success' 	=> false,
				'error' 	=> 'Unable to refresh list at this time, please try again.'
			];
		}

		wp_send_json($response);
		wp_die();
	}

	/**
	 * handle_settings_list_forms_request
	 *
	 * @return void
	 */
	public function handle_settings_list_forms_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		$results = [];
		$pagination = [];
		try {
			//date_default_timezone_set('America/Los_Angeles');

			// Get a list of all my forms
			$query_results 	= Form::read_forms();
			$results 		= $query_results['results'];
			$pagination 	= $query_results['pagination'];

			if (is_array($results)) {
				foreach ($results as &$result) {
					$result = (array)$result;

					$result['header'] 	= empty($result['header']) ? 'No Title' : $result['header'];
					$type_name 			= $this->get_form_type_name($result['type_id']);
					$template_name 		= $this->get_form_template_name($result['template_id']);

					$result['type_name'] = $type_name;
					$result['template_name'] = $template_name;
				}
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		$response = ['success' => true, 'results' => $results, 'pagination' => $pagination];
		wp_send_json($response);
		die();
	}

	/**
	 * handle_save_settings_request
	 *
	 * @return void
	 */
	public function handle_save_settings_request()
	{
		// Verify that the form post is indeed our form
		if (isset($_POST['save_settings_nonce']) && wp_verify_nonce($_POST['save_settings_nonce'], 'save_settings_form_action')) {
			$settings = new Settings();

			// has a provider been selected?
			if (isset($_POST['provider']) && !empty($_POST['provider'])) {
				// Save data if form has been submitted

				// Prevent extra slashes when single quotes are added to the database
				$_POST = array_map('stripslashes_deep', $_POST);

				$settings->from_object($_POST);

				try {
					$EmailProvider 		= new EmailProvider($settings->provider);
					$Provider 			= $EmailProvider->get_provider();
					$provider_fields 	= $Provider->get_setting_fields();

					$values = [];
					// Get the values
					foreach ($provider_fields as &$field) {
						$field['value'] = isset($_POST[$field['key']]) ? $_POST[$field['key']] : '';
						$values[] 		= ['key' => $field['key'], 'value' => $field['value']];
					}

					// Update the provider
					$Provider->set_settings_fields($values);
					$settings->update();

					// For oauth providers...
					if (method_exists($Provider, 'on_authenticated')) {
						$Provider->on_authenticated();
					}

					set_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-success', true, 60);
				} catch (Exception $e) {
					error_log($e);
					// Remove the provider setting
					$settings->provider = '';
					$settings->update();

					set_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-error', true, 60);
				}
			} else {
				$settings->provider = '';
				$settings->update();

				set_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-error', true, 60);
			}

			wp_safe_redirect(admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'main_settings'));
			exit;
		} else {
			wp_die('Unauthorized access');
		}
	}
}
