<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Admin\BaseAdmin;
use Inc\Admin\FormsFormHandler;
use Inc\Classes\Settings;
use Inc\Classes\Form;

use Inc\Classes\EmailProvider;
use Inc\Classes\FormTemplates;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * FormsManager
 */
class FormsManager extends BaseAdmin
{
	/**
	 * form_handler
	 *
	 * @var class FormsFormHandler
	 */
	private $form_handler;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->form_handler = new FormsFormHandler();

		// Only add to admin area
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

		// Handles all form posts for saving settings and forms
		add_action('wp_ajax_handle_load_template', [$this->form_handler, 'handle_load_template_request']);
		add_action('wp_ajax_handle_meta_refresh_list', [$this->form_handler, 'handle_meta_refresh_list_request']);
		add_action('wp_ajax_handle_load_content', [$this->form_handler, 'handle_load_content_request']);
		add_action('wp_ajax_handle_send_test_email', [$this->form_handler, 'handle_send_test_email_request']);
		add_action('wp_ajax_handle_add_form_new_nonce', [$this->form_handler, 'handle_add_form_new_nonce_request']);
		add_action('admin_post_delete_form_action', [$this->form_handler, 'handle_delete_form_request']);
		add_action('admin_post_save_add_form_action', [$this->form_handler, 'handle_save_form_request']);

		// to add a custom button to the wp_editor
		add_filter('mce_buttons', [$this, 'register_buttons']);
		add_filter('mce_external_plugins', [$this, 'register_tinymce_javascript']);

		parent::__contstruct();
	}

	/**
	 * register_buttons
	 * Used to register a custom button with the identifier: addbutton
	 *
	 * @param  mixed $buttons
	 * @return void
	 */
	public function register_buttons($buttons)
	{
		array_push($buttons, '|', 'addbutton');

		return $buttons;
	}

	/**
	 * register_tinymce_javascript
	 * The javascript to register the custom button
	 *
	 * @param  mixed $plugin_array
	 * @return void
	 */
	public function register_tinymce_javascript($plugin_array)
	{
		$plugin_array[OPTIN_BUDDY_PREFIX . 'editor_plugin'] = OPTIN_BUDDY_URL . 'assets/js/tinymce-plugin.js';

		return $plugin_array;
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
		if (str_contains($hook_suffix, OPTIN_BUDDY_PREFIX . 'add_new')) {
			wp_enqueue_media(); // This will enqueue the Media Uploader script
			wp_enqueue_editor();
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-media-upload', OPTIN_BUDDY_URL . 'assets/js/admin-image-selector.js', ['jquery'], OPTIN_BUDDY_VERSION);
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-wp-editor-utils', OPTIN_BUDDY_URL . 'assets/js/admin-wp-editor-utils.js', ['jquery'], OPTIN_BUDDY_VERSION);
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-settings-multi-select', OPTIN_BUDDY_URL . 'assets//js/utils/multi-select.js', ['jquery'], OPTIN_BUDDY_VERSION);
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-settings-add-form', OPTIN_BUDDY_URL . 'assets/js/admin-add-form.js', ['jquery', OPTIN_BUDDY_PREFIX . 'backend-settings-multi-select'], OPTIN_BUDDY_VERSION);

			// To handle AJAX requests
			wp_localize_script(OPTIN_BUDDY_PREFIX . 'backend-settings-add-form', 'AdminAddFormAjax', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce(OPTIN_BUDDY_PREFIX . 'add_form_nonce')
			]);

			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-settings-multi-select', OPTIN_BUDDY_URL . 'assets/css/multi-select.css', [], OPTIN_BUDDY_VERSION);
			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-settings-add-form', OPTIN_BUDDY_URL . 'assets/css/admin-add-form.css', [OPTIN_BUDDY_PREFIX . 'backend-settings-multi-select'], OPTIN_BUDDY_VERSION);
		}
	}

	/**
	 * render_add_new_page_before
	 *
	 * @return void
	 */
	public function render_page_before()
	{
		if (!$this->hasSetupCompleted()) {
			set_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-error', true, 60);
			// Redirect back to the main settings page to display the error
			wp_safe_redirect(menu_page_url(OPTIN_BUDDY_PREFIX . 'main_settings', false));
			exit;
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
		wp_nonce_field('your_custom_nonce_action', 'your_custom_nonce_name');
		$settings = new Settings();

		$form = new Form();
		$form->from_object($settings);

		$categories = get_categories([
			'orderby' => 'name',
			'order' => 'ASC'
		]);

		// Get the provider
		$provider = $settings->provider;
		$EmailProvider = new EmailProvider($provider);
		$Provider = $EmailProvider->get_provider();

		// Get data from the save form request
		$form_data = get_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-data');
		if ($form_data) {
			$form->from_object($form_data);
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-data');
		}

		// Get data from the edit form request
		$form_data = get_transient(OPTIN_BUDDY_PREFIX . 'admin-edit-form-data');
		if ($form_data) {
			$form->from_object($form_data);
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-edit-form-data');
		}

		$id = $form->id === 0 ? '' : $form->id;
		$has_image = FormTemplates::has_image($form->template_id);

		require_once OPTIN_BUDDY_DIR . 'templates/admin/add-form.php';
	}
}
