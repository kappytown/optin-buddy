<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Admin\BaseAdmin;
use Inc\Utils;

use PHPMailer\PHPMailer;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * ContactUsManager
 */
class ContactUsManager extends BaseAdmin
{
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
		add_action('admin_notices', [$this, 'handle_admin_notice']);
		add_action('phpmailer_init', [$this, 'customize_phpmailer']);
		add_action('admin_post_contact_us_form_action', [$this, 'handle_contact_us_form_request']);
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
		if (str_contains($hook_suffix, OPTIN_BUDDY_PREFIX . 'contact_us')) {
			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'backend-settings-contact-us', OPTIN_BUDDY_URL . 'assets/css/admin-contact-us.css', [], OPTIN_BUDDY_VERSION);
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
		$current_user = wp_get_current_user();
		$admin_email = $current_user->user_email;
		require_once OPTIN_BUDDY_DIR . 'templates/admin/contact-us.php';
	}

	/**
	 * handle_admin_notice
	 *
	 * @return void
	 */
	public function handle_admin_notice()
	{
		$notice = get_transient(OPTIN_BUDDY_PLUGIN_NAME . '_admin_notices_contact_us');
		if (!empty($notice)) {
			if ($notice['type'] === 'success') {
				echo '<div class="notice notice-success is-dismissible">';
				echo '<p>Message sent successfully! We\'ll get back to you as soon as possible.</p>';
				echo '</div>';
			} else if ($notice['type'] === 'error') {
				echo '<div class="notice notice-error is-dismissible">';
				echo '<p>Unable to send your message. Please contact us at ' . OPTIN_BUDDY_EMAIL . '</p>';
				echo '</div>';
			} else if ($notice['type'] === 'email_not_found') {
				echo '<div class="notice notice-error is-dismissible">';
				echo '<p>The administrator email could not be found. To send us an email from WordPress, please make sure that the Administrator Email Address and SMTP settings for your WordPress site have been correctly setup.</p>';
				echo '</div>';
			}
		}
		delete_transient(OPTIN_BUDDY_PLUGIN_NAME . '_admin_notices_contact_us');
	}


	/**
	 * handle_contact_submission
	 *
	 * @return void
	 */
	public function handle_contact_us_form_request()
	{
		// Check for nonce for security
		check_admin_referer('contact_us_form_action', OPTIN_BUDDY_PREFIX . 'contact_nonce');

		// Process form data
		$to 		= get_option('admin_email');
		$name 		= sanitize_text_field($_POST['name']);
		$email 		= sanitize_email($_POST['email']);
		$subject 	= sanitize_text_field($_POST['subject']);
		$message 	= nl2br(sanitize_textarea_field($_POST['message']));
		$message 	.= "<p>From: $name</p>";

		// Check if the admin email is set
		if (empty($to)) {
			set_transient(OPTIN_BUDDY_PLUGIN_NAME . '_admin_notices_contact_us', ['type' => 'email_not_found'], HOUR_IN_SECONDS);
			wp_redirect(admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'contact_us'));
			exit;
		}

		// wp_mail does NOT work on localhost
		$success = Utils::send_mail(OPTIN_BUDDY_EMAIL, $subject, $message, ["Reply-To" => "$name <$email>"]);

		if ($success) {
			set_transient(OPTIN_BUDDY_PLUGIN_NAME . '_admin_notices_contact_us', ['type' => 'success'], HOUR_IN_SECONDS);
		} else {
			set_transient(OPTIN_BUDDY_PLUGIN_NAME . '_admin_notices_contact_us', ['type' => 'error'], HOUR_IN_SECONDS);
		}

		// Redirect back to the form page or display a message
		wp_redirect(admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'contact_us'));
		exit;
	}

	/**
	 * customize_phpmailer
	 *
	 * @param  mixed $phpmailer
	 * @return void
	 */
	public function customize_phpmailer($phpmailer)
	{
		// Check if the PHPMailer class exists
		if (!($phpmailer instanceof PHPMailer\PHPMailer)) {
			error_log('PHPMailer is not able to be customized.');
			return;
		}

		$phpmailer->isHTML(true);
	}
}
