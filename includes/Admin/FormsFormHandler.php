<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Utils;
use Inc\Admin\BaseAdmin;
use Inc\Classes\Form;
use Inc\Classes\FormTemplates;

use \Exception;
use Inc\Classes\EmailProvider;
use Inc\Classes\Settings;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * FormsFormHandler
 */
class FormsFormHandler extends BaseAdmin
{
	private $nonce = OPTIN_BUDDY_PREFIX . 'add_form_nonce';

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Handle edit request
		if (isset($_GET['id'])) {
			$this->handle_edit_form_request();
		}
	}

	/**
	 * handle_add_form_new_nonce_request
	 * Returns the new nonce
	 *
	 * @return void
	 */
	public function handle_add_form_new_nonce_request()
	{
		$nonce = wp_create_nonce($this->nonce);
		wp_send_json(['success' => true, 'nonce' => $nonce]);
	}

	/**
	 * handle_contact_submission
	 *
	 * @return void
	 */
	public function handle_load_template_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['template_id'])) {
			$response = ['success' => false, 'error' => 'Invalid Form ID'];
			wp_send_json($response);
			die;
		}

		// Prevent extra slashes when single quotes are added to the database
		$_POST = array_map('stripslashes_deep', $_POST);

		FormTemplates::load_preview($_POST);

		die();
	}

	/**
	 * handle_meta_refresh_list_request
	 * This will load the providers select options list specified by the handler variable
	 *
	 * @return void
	 */
	public function handle_meta_refresh_list_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['handler'])) {
			$response = ['suceess' => false, 'error' => 'Invalid Request'];
			wp_send_json($response);
			die;
		}

		$settings = new Settings();
		$provider = $settings->provider;
		$handler = sanitize_text_field($_POST['handler']);

		try {
			// Get the provider details
			$EmailProvider 	= new EmailProvider($provider);
			$Provider 		= $EmailProvider->get_provider();

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
	 * handle_load_content_request
	 * This will load the contents of the specified template
	 * 
	 * @return void
	 */
	public function handle_load_content_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);
		$content = '';

		if (isset($_POST['template'])) {
			$template = sanitize_text_field($_POST['template']);

			$path = OPTIN_BUDDY_DIR . 'templates/admin/parts/' . $template . '.html';
			$content = Utils::get_file_contents($path);
		}
		echo empty($content) ? 'not found' : $content;

		wp_die();
	}

	/**
	 * handle_send_test_email_request
	 * This will send a test email to the specified email to verify that the SMTP settins in WordPress are set
	 * 
	 * @return void
	 */
	public function handle_send_test_email_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['email'])) {
			$response = ['suceess' => false, 'error' => 'Invalid Request'];
			wp_send_json($response);
			die;
		}

		$email = sanitize_email($_POST['email']);
		$title = 'Test email sent';
		$content = !empty($_POST['content']) ? wp_kses_post($_POST['content']) : '';
		if (!empty($content)) {
			$content = str_replace('{{page_title}}', 'This is an example title', $content);
			$content = str_replace('{{page_url}}', 'http://yourdomain.com/the-page-title/this-is-an-example-url', $content);
			$content = str_replace("\'", "'", $content);
			$content = wpautop($content);

			// Load the css and add it to the message
			$css = Utils::get_file_contents(OPTIN_BUDDY_DIR . 'templates/admin/parts/css-wp-editor.html');
			$message = '<html><head>' . $css . '</head><body>' . $content . '</body></html>';
		} else {
			$message = 'Congratulations, your WordPress SMTP settings are correct!';
		}

		Utils::send_mail($email, $title, $message);

		wp_send_json(['success' => true]);
		wp_die();
	}

	/**
	 * handle_edit_form_request
	 *
	 * @return void
	 */
	public function handle_edit_form_request()
	{
		try {
			$result = Form::read_form($_GET['id']);
			//$result->locations = maybe_unserialize($result->locations);

			set_transient(OPTIN_BUDDY_PREFIX . 'admin-edit-form-data', $result, 60);
		} catch (Exception $e) {
			error_log($e->getMessage());
			set_transient(OPTIN_BUDDY_PREFIX . 'admin-edit-form-error', true, 60);
			wp_safe_redirect(admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'main_settings'));
		}
	}

	/**
	 * handle_delete_form_request
	 *
	 * @return void
	 */
	public function handle_delete_form_request()
	{
		if (isset($_POST['delete_form_nonce']) && wp_verify_nonce($_POST['delete_form_nonce'], 'delete_form_action')) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

			try {
				Form::delete_form($id);

				set_transient(OPTIN_BUDDY_PREFIX . 'admin-delete-form-success', true, 60);
			} catch (Exception $e) {
				error_log($e->getMessage());
				set_transient(OPTIN_BUDDY_PREFIX . 'admin-delete-form-error', true, 60);
			}
			wp_safe_redirect(admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'main_settings'));
		}
	}

	/**
	 * handle_save_form_request
	 *
	 * @return void
	 */
	public function handle_save_form_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		// Prevent extra slashes when single quotes are added to the database
		$_POST = array_map('stripslashes_deep', $_POST);
		$hasError = false;

		try {
			$form = new Form();
			$form->from_object($_POST);
			
			if ($form->id === 0) {
				$form->create();
			} else {
				$form->update();
			}

		} catch (Exception $e) {
			error_log($e->getMessage());
			$hasError = true;
		}

		if (!$hasError) {
			set_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-success', true, 60);
			wp_safe_redirect(admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'main_settings'));
		} else {
			set_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-error', true, 60);
			set_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-data', $_POST, 60);
			wp_safe_redirect(admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'add_new'));
		}
	}
}
