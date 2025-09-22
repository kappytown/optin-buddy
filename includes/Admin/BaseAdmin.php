<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Classes\Settings;
use Inc\Classes\FormTemplates;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * BaseAdmin
 */
class BaseAdmin
{
	public function __contstruct()
	{
	}

	/**
	 * getHasSetupCompleted
	 *
	 * @return bool
	 */
	public function hasSetupCompleted()
	{
		$settings = new Settings();
		return isset($settings->provider) && !empty($settings->provider);
	}

	/**
	 * get_area_pretty_name
	 *
	 * @param  mixed $area
	 * @return string
	 */
	public function get_area_pretty_name($area)
	{
		switch ($area) {
			case 'all':
				return 'All Pages';
			case 'post':
				return 'Only Post Pages';
			case 'category':
				return 'Only Category Pages';
			default:
				return $area;
		}
	}

	/**
	 * get_type_pretty_name
	 *
	 * @param  mixed $type
	 * @return void
	 */
	public function get_type_pretty_name($type)
	{
		switch ($type) {
			case 'before_paragraph':
				return 'Before Paragraph';
			case 'after_paragraph':
				return 'After Paragraph';
			case 'before_element':
				return 'Before Element';
			case 'after_element':
				return 'After Element';
			case 'popup_time_delay':
				return 'Popup Time-Delayed';
			case 'popup_scroll_delay':
				return 'Popup Scroll-Delayed';
			case 'popup_exit_intent':
				return 'Popup Exit-Intent';
			default:
				return $type;
		}
	}

	/**
	 * get_type_name
	 *
	 * @param  int $type_id
	 * @return string
	 */
	public function get_form_type_name($type_id = 0)
	{
		switch (intval($type_id)) {
			case 1:
				return 'Inline Form';
			case 2:
				return 'Floating Box';
			case 3:
				return 'Modal Popup';
			case 4:
				return 'Floating Box';
			case 5:
				return 'Exit Intent';
			default:
				return 'Unknown';
		}
	}

	/**
	 * get_template_name
	 *
	 * @param  int $template_id
	 * @return string
	 */
	public function get_form_template_name($template_id = 0)
	{
		return FormTemplates::get_template_field_value($template_id, 'name');
	}

	/**
	 * verify_nonce
	 * Validates the nonce and returns a json response if invalid.
	 * This is a helper method for AJAX requests to reduce redundancy
	 *
	 * @param  string $nonce
	 * @param  string $key
	 * @return void
	 */
	protected function verify_nonce($nonce, $key)
	{
		if (!wp_verify_nonce($nonce, $key)) {
			//error_log('invalid nonce for key: ' . $key);
			$response = ['success' => false, 'error' => 'Your session has expired, Please refresh the page and try again.'];
			wp_send_json($response);
			wp_die();
		}
	}
}
