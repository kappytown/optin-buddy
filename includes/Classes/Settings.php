<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Settings
 */
class Settings
{
	/**
	 * settings_found
	 *
	 * Set to true if the settings option was previously created (found)
	 * If false, then we can assume this is the first install (not activation)
	 * 
	 * @var bool
	 */
	public $settings_found = false;

	/**
	 * provider
	 * 
	 * @var string id of the selected email provider
	 */
	public $provider = '';

	/**
	 * capture_submission
	 * This is currently a hard-coded setting not in the admin UI
	 * 
	 * TODO: Add to admin UI
	 *
	 * @var int If 1, we will capture every form submission with the form->id and page url
	 */
	public $capture_submission = 1;

	/**
	 * prevent_overlapping
	 * This is currently a hard-coded setting not in the admin UI
	 * 
	 * TODO: Add to admin UI
	 *
	 * @var int If 1, this will prevent multiple forms from displyaing on top of each other
	 */
	public $prevent_overlapping = 1;

	/**
	 * name
	 *
	 * @var string Name of the settings key
	 */
	public $name = '';

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name = OPTIN_BUDDY_SETTINGS;
		$settings 	= get_option($this->name);

		if (!$settings) {
			$this->create();
		} else {
			$this->settings_found = true;
			$this->from_object($settings);
		}

		return $this;
	}

	/**
	 * from_post
	 *
	 * @param  mixed $data
	 * @return object
	 */
	public function from_object($data)
	{
		// Cast data into an array
		if (is_object($data)) $data = (array) $data;

		$this->provider 			= isset($data['provider']) ? sanitize_text_field($data['provider']) : $this->provider;
		$this->capture_submission 	= isset($data['capture_submission']) ? intval($data['capture_submission']) : $this->capture_submission;
		$this->prevent_overlapping 	= isset($data['prevent_overlapping']) ? intval($data['prevent_overlapping']) : $this->prevent_overlapping;

		return $this;
	}

	/**
	 * update
	 *
	 * @return void
	 */
	public function update()
	{
		// Update option with class properties
		update_option($this->name, [
			'provider' 				=> $this->provider,
			'capture_submission' 	=> $this->capture_submission,
			'prevent_overlapping' 	=> $this->prevent_overlapping
		]);
	}

	/**
	 * create
	 *
	 * @return void
	 */
	public function create()
	{
		// Create option with class properties
		add_option($this->name, [
			'provider' 				=> $this->provider,
			'capture_submission' 	=> $this->capture_submission,
			'prevent_overlapping' 	=> $this->prevent_overlapping
		]);
	}

	/**
	 * delete
	 *
	 * @return void
	 */
	public function delete()
	{
		delete_option($this->name);
	}
}
