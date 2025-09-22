<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Admin\BaseAdmin;
use Inc\Admin\SettingsManager;
use Inc\Admin\FormsManager;
use Inc\Admin\ReportsManager;
use Inc\Admin\ContactUsManager;
use Inc\Admin\OauthManager;
use Inc\Classes\EmailProvider;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * AdminController
 */
class AdminController extends BaseAdmin
{
	/**
	 * Google font
	 */
	private $google_fonts_url = 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined';

	/**
	 * settings_manager
	 *
	 * @var class
	 */
	public $settings_manager;

	/**
	 * forms_manager
	 *
	 * @var mixed
	 */
	public $forms_manager;

	/**
	 * reports_manager
	 *
	 * @var mixed
	 */
	public $reports_manager;

	/**
	 * contact_us_manager
	 *
	 * @var mixed
	 */
	public $contact_us_manager;

	/**
	 * oauth_manager
	 *
	 * @var mixed
	 */
	public $oauth_manager;

	/**
	 * menu_parent
	 *
	 * @var string slug
	 */
	private $menu_parent;

	/**
	 * menu_settings
	 *
	 * @var string slug
	 */
	private $menu_settings;

	/**
	 * menu_add_new
	 *
	 * @var string slug
	 */
	private $menu_add_new;

	/**
	 * menu_reports
	 *
	 * @var mixed
	 */
	private $menu_reports;

	/**
	 * menu_contact_us
	 *
	 * @var string slug
	 */
	private $menu_contact_us;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		// The managers "manage" each menu page
		$this->settings_manager 	= new SettingsManager();
		$this->forms_manager 		= new FormsManager();
		$this->reports_manager 		= new ReportsManager();
		$this->contact_us_manager 	= new ContactUsManager();
		$this->oauth_manager 		= new OauthManager();

		// Loads all the base javascript and css files used on every page
		// Loading them here prevents duplication
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

		// Add the admin menu and sub menus
		add_action('admin_menu', [$this, 'add_admin_menu']);

		// Displays an error if setup is not complete and the Add New button was clicked
		add_action('admin_notices', [$this, 'admin_show_notices']);

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
		// Loads all global styles and scripts if in our admin pages
		if (str_contains($hook_suffix, OPTIN_BUDDY_PREFIX . '')) {
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-shared', OPTIN_BUDDY_URL . 'assets/js/utils/utils.js', ['jquery'], OPTIN_BUDDY_VERSION);
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-modal', OPTIN_BUDDY_URL . 'assets/js/utils/modal.js', [], OPTIN_BUDDY_VERSION);

			wp_register_style('google-fonts', $this->google_fonts_url);
			wp_enqueue_style('google-fonts');

			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-modal', OPTIN_BUDDY_URL . 'assets/css/modal.css', [], OPTIN_BUDDY_VERSION);
			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-base', OPTIN_BUDDY_URL . 'assets/css/admin-base.css', [], OPTIN_BUDDY_VERSION);
		}
	}

	/**
	 * add_admin_menu
	 *
	 * @return void
	 */
	public function add_admin_menu()
	{
		$this->menu_parent = add_menu_page(
			'OptinBuddy', // page title
			'OptinBuddy', // menu title
			'manage_options', // capability
			OPTIN_BUDDY_PREFIX . 'main_settings', // menu slug
			[$this->settings_manager, 'render_page'], // callback (click handler)
			'dashicons-email-alt2', // menu icon using wordpress dashicons
			60 // position (right after Appearance)
		);

		$this->menu_settings = add_submenu_page(
			OPTIN_BUDDY_PREFIX . 'main_settings',
			'Dashboard',
			'Dashboard',
			'manage_options',
			OPTIN_BUDDY_PREFIX . 'main_settings',
			[$this->settings_manager, 'render_page']
		);

		$this->menu_add_new = add_submenu_page(
			OPTIN_BUDDY_PREFIX . 'main_settings',
			'Add New',
			'Add New',
			'manage_options',
			OPTIN_BUDDY_PREFIX . 'add_new',
			[$this->forms_manager, 'render_page']
		);

		$this->menu_reports = add_submenu_page(
			OPTIN_BUDDY_PREFIX . 'main_settings',
			'Reports',
			'Reports',
			'manage_options',
			OPTIN_BUDDY_PREFIX . 'reports',
			[$this->reports_manager, 'render_page']
		);

		$this->menu_contact_us = add_submenu_page(
			OPTIN_BUDDY_PREFIX . 'main_settings',
			'Contact Us',
			'Contact Us',
			'manage_options',
			OPTIN_BUDDY_PREFIX . 'contact_us',
			[$this->contact_us_manager, 'render_page']
		);

		// hidden submenu page for oauth popup
		add_submenu_page(
			null,	// parent slug - null means hidden
			'Oauth Callback',	// Page title
			'',	// Menu title is empty since it is hidden
			'manage_options',	// Capability
			OPTIN_BUDDY_PREFIX . 'oauth_callback',	// menu slug
			[$this->oauth_manager, 'render_page']	// callback
		);

		// Checks if the setup process has completed before navigating to the menu page
		add_action("load-" . $this->menu_add_new, [$this->forms_manager, 'render_page_before']);
	}

	/**
	 * admin_show_setup_error
	 * Displays an admin-error notice if the transient has been set because setup has not been conpleted
	 *
	 * @return void
	 */
	public function admin_show_notices()
	{
		// CRON DISABLED!!!!
		// You will need to add the following new cron job to your crontab file: * * * * * /usr/bin/php {OPTIN_BUDDY_DIR}script.php
		// This will ensure that your email providers oauth token gets refreshed preventing authentication errors
		if (get_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-error')) {
			echo '<div class="notice notice-error is-dismissible"><p>You must first select your email provider from the list below.</p></div>';

			// cleanup
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-error');
		}

		if (get_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-success')) {
			echo '<div class="notice notice-success is-dismissible"><p>Form updated successfully!</p></div>';

			// cleanup
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-setup-success');
		}

		if (get_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-success')) {
			echo '<div class="notice notice-success is-dismissible"><p>Successfully added your new form.</p></div>';

			// cleanup
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-success');
		}

		if (get_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-error')) {
			echo '<div class="notice notice-error is-dismissible"><p>Unable to save your form, please try again.</p></div>';

			// cleanup
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-add-form-error');
		}

		if (get_transient(OPTIN_BUDDY_PREFIX . 'admin-delete-form-success')) {
			echo '<div class="notice notice-success is-dismissible"><p>Form successfully deleted.</p></div>';

			// cleanup
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-delete-form-success');
		}

		if (get_transient(OPTIN_BUDDY_PREFIX . 'admin-delete-form-error')) {
			echo '<div class="notice notice-error is-dismissible"><p>Unable to delete this form, please try again.</p></div>';

			// cleanup
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-delete-form-error');
		}

		if (get_transient(OPTIN_BUDDY_PREFIX . 'admin-edit-form-error')) {
			echo '<div class="notice notice-error is-dismissible"><p>Unable to edit this form, please try again.</p></div>';

			// cleanup
			delete_transient(OPTIN_BUDDY_PREFIX . 'admin-edit-form-error');
		}
	}
}
