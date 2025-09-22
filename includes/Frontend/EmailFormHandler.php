<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Frontend;

use Inc\Classes\EmailProvider;
use Inc\Classes\Form;
use Inc\Classes\FormSubmission;
use Inc\Classes\FormStats;
use Inc\Utils;
use Inc\Exceptions\EmailSendingException;

use \Exception;
use \Throwable;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * EmailFormHandler
 * Handles all ajax requests for processing each form
 */
class EmailFormHandler
{

	public function __construct(){}

	/**
	 * handle_new_nonce_request
	 * Generates a new nonce for the email form
	 *
	 * @return void
	 */
	public function handle_new_nonce_request()
	{
		$nonce = wp_create_nonce(OPTIN_BUDDY_PREFIX . 'email_form_nonce');
		wp_send_json(['success' => true, 'nonce' => $nonce]);
		wp_die();
	}

	/**
	 * handle_form_impression_request
	 * Handles all form impressions (loads, views, submissions)
	 *
	 * @return void
	 */
	public function handle_form_impression_request()
	{
		if (!wp_verify_nonce($_POST['nonce'], OPTIN_BUDDY_PREFIX . 'email_form_nonce')) {
			wp_send_json(['success' => false, 'message' => 'invalid nonce']);
			wp_die();
		}

		$post_id 	= !empty($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$form_id 	= !empty($_POST['form_id']) ? sanitize_text_field($_POST['form_id']) : '';
		$type 		= !empty($_POST['type']) ? sanitize_text_field($_POST['type']) : '';

		// Validation
		if (empty($form_id) || $post_id === 0 || !in_array($type, ['loads', 'views', 'submissions'])) {
			wp_send_json(['success' => false, 'message' => 'invalid request']);
			wp_die();
		}

		// Turn into an array and remove non-integer values
		$parts = explode(',', $form_id);
		// Filter out non-integer values and empty strings
		$form_ids = array_filter($parts, function ($item) {
			return is_numeric(trim($item)) && !is_float($item + 0);
		});
		// Convert the values to integers
		$form_ids = array_map('intval', $form_ids);

		// Exit if form_ids is empty
		if (empty($form_ids)) {
			wp_send_json(['success' => false, 'message' => 'invalid request']);
			wp_die();
		}

		$loads 			= $type === 'loads' ? 1 : 0;
		$views 			= $type === 'views' ? 1 : 0;
		$submissions 	= $type === 'submissions' ? 1 : 0;

		FormStats::create_from_ids($post_id, $form_ids, $type, $loads, $views, $submissions);
		wp_send_json(['success' => true]);
		wp_die();
	}

	/**
	 * handle_form_submission_request
	 * Handles all form submissions by capturing the email and meta data and sending it to the email provider
	 * This also stores the email and meta data in the WP database as a backup
	 *
	 * @return void
	 */
	public function handle_form_submission_request()
	{
		global $wpdb;

		if (!wp_verify_nonce($_POST['nonce'], OPTIN_BUDDY_PREFIX . 'email_form_nonce')) {
			//echo "Invalid Nonce";
			wp_send_json(['success' => false, 'message' => 'invalid nonce']);
			wp_die();
		}

		// Get POST data
		$email 				= sanitize_email($_POST['email']);
		$name 				= isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
		$page_title 		= isset($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : '';
		$page_url 			= isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';

		// The only metadata that we pass is page title and page url. We have mapped them to the following:
		$post_meta 			= ['page_title' => $page_title, 'page_url' => $page_url, 'recipe' => $page_url, 'PAGE_TITLE' => $page_title, 'PAGE_URL' => $page_url, 'RECIPE' => $page_url];

		$post_id 			= !empty($_POST['post_id']) ? intval($_POST['post_id']) : 0;	// ID of the post, page, or category the form was submitted on
		$form_id 			= !empty($_POST['form_id']) ? intval($_POST['form_id']) : 0;	// ID of the form 
		$target_categories 	= isset($_POST['target_categories']) ? maybe_unserialize($_POST['target_categories']) : [];

		$error 			= 'Unable to send, please try again'; 	// Default error message
		$success 		= false;								// Set to true if form successfully posted
		$provider 		= '';
		$provider_name 	= '';
		$response 		= ['success' => false, 'message' => ''];
		$send_error 	= '';	// Set when the send_email fails so we can store it in the database

		// Wrap the send method in a try catch so that we can still insert a record in the database if failed
		try {
			$settings 		= get_option(OPTIN_BUDDY_SETTINGS, []);
			$provider 		= $settings['provider'];

			// Each From may contain Provider specific meta data associated with it
			// If so, we must update the Provider associated properties with our meta data
			$form = new Form();
			$form->from_id($form_id);

			// Now we must get the users provider and send the email and meta data (title, url) to their provider
			$EmailProvider 	= new EmailProvider($provider);
			$Provider 		= $EmailProvider->get_provider();
			$provider_name 	= $Provider->name;
			$success 		= $Provider->send_email($email, $name, $post_meta, $form->meta);
			$response 		= ['success' => $success, 'message' => $form->success];

			if (!$success) {
				$response['message'] 	= $error;
				$send_error 			= 'Failed to send user email to ' . $provider_name . '.';

				// Capture email since the send_email failed
				$Provider->save_email = true;
			}
		} catch (EmailSendingException $e) {
			$send_error = $e->getMessage();

		} catch (Exception $e) {
			error_log($e->getMessage());
			$response['message'] 	= $error;
			$send_error 			= 'Failed to send user email (' . $_POST['email'] . ') to ' . (!empty($provider_name) ? $provider_name : 'Provider') . '.';
		}

		// Now store the email and meta data in the WP database
		// Try/Catch is outside send_email functionality so that we ALWAYS capture the email unless the DB fails
		try {
			// Just in case there was an error in the first try/catch block
			if (empty($form)) {
				$form = new Form();
				$form->from_id($form_id);
			}

			// Create the hash
			$user_identifier = $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $email;
			$user_hash 		= md5($user_identifier);

			// create the meta data
			$meta = maybe_serialize([
				'provider' 				=> $provider_name,
				'type_id' 				=> $form->type_id,
				'template_id' 			=> $form->template_id,
				'page_type' 			=> $form->page_type,
				'page_location' 		=> $form->page_location,
				'page_location_value' 	=> $form->page_location_value,
				'form_location' 		=> $form->form_location,
				'target_categories' 	=> $target_categories
			]);

			$formSubmission = new FormSubmission(0, $form_id, $post_id, $user_hash, $email, $meta, $send_error);
			$formSubmission->create();
			// --------------------------------------------------------------

			// Now check if the form wants to send a custom email to the subscriber
			if ($form->send_email == 1 && !empty($form->send_email_subject) && !empty($form->send_email_message)) {
				$message = $form->send_email_message;
				$message = str_replace('{{page_title}}', $page_title, $message);
				$message = str_replace('{{page_url}}', $page_url, $message);
				$message = str_replace('{{recipe}}', $page_url, $message);
				$message = str_replace("\'", "'", $message);
				$message = wpautop($message);

				// Load the css and add it to the message
				$css = Utils::get_file_contents(OPTIN_BUDDY_DIR . 'templates/admin/parts/css-wp-editor.html');
				$message = '<html><head>' . $css . '</head><body>' . $message . '</body></html>';

				Utils::send_mail($email, $form->send_email_subject, $message);
			}
		} catch (Exception $e) {
			error_log($e->getMessage());

		} catch (Throwable $t) {
			error_log($t->getMessage());
		}

		wp_send_json($response);
		wp_die();
	}
}
