<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Admin\BaseAdmin;
use Inc\Admin\ReportsFormHandler;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * ReportsManager
 */
class ReportsManager extends BaseAdmin
{
	/**
	 * form_handler
	 *
	 * @var class ReportsFormHandler
	 */
	private $form_handler;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->form_handler = new ReportsFormHandler();

		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

		add_action('wp_ajax_handle_dashboard_count', [$this->form_handler, 'handle_dashboard_count_request']);
		add_action('wp_ajax_handle_dashboard_chart_monthly', [$this->form_handler, 'handle_dashboard_chart_monthly_request']);
		add_action('wp_ajax_handle_dashboard_chart_daily', [$this->form_handler, 'handle_dashboard_chart_daily_request']);
		add_action('wp_ajax_handle_dashboard_forms', [$this->form_handler, 'handle_dashboard_forms_request']);


		add_action('wp_ajax_handle_reports_submissions', [$this->form_handler, 'handle_reports_submissions_request']);
		add_action('wp_ajax_handle_reports_list_submissions', [$this->form_handler, 'handle_reports_list_submissions_request']);
		add_action('wp_ajax_handle_reports_top_5_forms', [$this->form_handler, 'handle_reports_top_5_forms_request']);
		add_action('wp_ajax_handle_reports_top_5_pages', [$this->form_handler, 'handle_reports_top_5_pages_request']);
		add_action('wp_ajax_handle_reports_list_forms', [$this->form_handler, 'handle_reports_list_forms_request']);
		add_action('wp_ajax_handle_reports_list_emails', [$this->form_handler, 'handle_reports_list_emails_request']);

		add_action('wp_ajax_handle_reports_new_nonce', [$this->form_handler, 'handle_reports_new_nonce_request']);
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
		if (str_contains($hook_suffix, OPTIN_BUDDY_PREFIX . 'reports')) {
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-chartjs', OPTIN_BUDDY_URL . 'assets/js/utils/chart.min.js', [], OPTIN_BUDDY_VERSION);
			// wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-utils', OPTIN_BUDDY_URL . 'assets/js/utils/utils.js', [], OPTIN_BUDDY_VERSION);
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'backend-settings-reports', OPTIN_BUDDY_URL . 'assets/js/admin-reports.js', ['jquery'], OPTIN_BUDDY_VERSION);

			// To handle AJAX requests
			wp_localize_script(OPTIN_BUDDY_PREFIX . 'backend-settings-reports', 'AdminReportsAjax', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce(OPTIN_BUDDY_PREFIX . 'reports_nonce')
			]);

			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-settings-reports', OPTIN_BUDDY_URL . 'assets/css/admin-reports.css', [], OPTIN_BUDDY_VERSION);
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
		require_once OPTIN_BUDDY_DIR . 'templates/admin/reports.php';
	}
}
