<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * FormTemplates
 */
class FormTemplates
{
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * get_templates
	 *
	 * @return array
	 */
	public static function get_templates()
	{
		return [
			[
				'id' => 1,
				'name' => 'No Image',
				'has-image' => false,
				'files' => [
					'assets/css/forms/form-1.css',
					'templates/frontend/forms/form-1.php'
				]
			],
			[
				'id' => 2,
				'name' => 'Image Top',
				'has-image' => true,
				'files' => [
					'assets/css/forms/form-2.css',
					'templates/frontend/forms/form-2.php'
				]
			],
			[
				'id' => 3,
				'name' => 'Image Left',
				'has-image' => true,
				'files' => [
					'assets/css/forms/form-3.css',
					'templates/frontend/forms/form-3.php'
				]
			],
			[
				'id' => 4,
				'name' => 'Image Right',
				'has-image' => true,
				'files' => [
					'assets/css/forms/form-4.css',
					'templates/frontend/forms/form-4.php'
				]
			],
			[
				'id' => 5,
				'name' => 'Image in Back',
				'has-image' => true,
				'files' => [
					'assets/css/forms/form-5.css',
					'templates/frontend/forms/form-5.php'
				]
			],
			[
				'id' => 6,
				'name' => 'Fixed Top Bar',
				'has-image' => false,
				'files' => [
					'assets/css/forms/form-6.css',
					'templates/frontend/forms/form-6.php'
				]
			]
		];
	}

	/**
	 * get_template
	 * Returnes a single template by ID
	 *
	 * @param  int $template_id
	 * @return array
	 */
	public static function get_template($template_id = 0)
	{
		$templates = self::get_templates();

		foreach ($templates as $template) {
			if ($template['id'] == $template_id) {
				return $template;
			}
		}
		return null;
	}

	/**
	 * get_template_data
	 * Returns the associated field value for the specified key in the template
	 *
	 * @param  int $template_id
	 * @param  string $key
	 * @return mixed
	 */
	public static function get_template_field_value($template_id = 0, $key = '')
	{
		$template = self::get_template($template_id);

		if ($template) {
			return $template[$key];
		}
		return '';
	}

	/**
	 * has_image
	 * Returns true if the specified template has an image
	 *
	 * @param  int $template_id
	 * @return bool
	 */
	public static function has_image($template_id = 0)
	{
		$template = self::get_template($template_id);

		if ($template) {
			return $template['has-image'];
		}

		return false;
	}

	/**
	 * load_preview
	 * Loads the specified template for preview in the admin area
	 *
	 * @param  array $post
	 * @return void
	 */
	public static function load_preview($post)
	{
		// Loop over the templates to get the list of files to include
		self::include_files($post);
	}

	/**
	 * include_files
	 * Includes all the files required for the specified template for rendering
	 *
	 * @param  mixed $atts
	 * @return void
	 */
	public static function include_files($atts)
	{
		// type cast object to array
		if (is_object($atts)) $atts = (array) $atts;

		$has_base_styles 	= false;
		$form_id 			= isset($atts['form_id']) ? intval($atts['form_id']) : 0;

		// If form_id is passed, lets get the data from the form otherwise get it from our default settings
		$Form = (new Form())->from_id($form_id);
		if (is_admin()) $Form->template_id = !empty($atts['template_id']) ? $atts['template_id'] : $Form->template_id;

		$type_id 		= $Form->type_id;
		$template_id 	= $Form->template_id;
		$header 		= $Form->header;
		$body 			= $Form->body;
		$button 		= $Form->button;
		$has_name 		= $Form->has_name_field;
		$disclaimer		= $Form->disclaimer;
		$success 		= $Form->success;
		$inview 		= $Form->inview;

		$image_id 		= isset($Form->image_id) ? intval($Form->image_id) : 0;
		$image_url 		= $image_id > 0 ? wp_get_attachment_url($image_id) : '';
		$image_alt 		= $image_id > 0 ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
		$image_title 	= $image_id > 0 ? get_the_title($image_id) : '';

		$files 			 = [];
		$template 		= self::get_template($template_id);

		if ($template) {
			$files = $template['files'];
		}

		// Loop over all the files and include them
		foreach ($files as $file) {
			$path = OPTIN_BUDDY_DIR . $file;
			if (file_exists($path)) {
				if (str_contains($file, 'css')) {
					// If we are NOT in the admin section then lets enqueue the file rather than echo out the css
					// Not required but less overhead
					if (!is_admin()) {
						if (!$has_base_styles) {
							wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'frontend-form-base', OPTIN_BUDDY_URL . 'assets/css/forms/form-base.css', [], OPTIN_BUDDY_VERSION);
						}
						wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'frontend-form-template-' . $template_id, OPTIN_BUDDY_URL . $file, [], OPTIN_BUDDY_VERSION);

						// Add custom styles
						if (!empty($Form->custom_css)) {
							echo '<style id="frontend-form-' . $form_id . '-custom-css" type="text/css">' . $Form->get_custom_css('.form-' . $template_id . '[data-id="' . $form_id . '"]') . '</style>';
						}
						
					} else {
						echo '<style id="backend-form-' . $form_id . '-custom-css" type="text/css">';
						if (!$has_base_styles) {
							include OPTIN_BUDDY_DIR . 'assets/css/forms/form-base.css';
						}
						include $path;

						// Add custom styles
						echo $Form->get_custom_css();
						echo "</style>";
					}
				} else {
					include $path;
				}
			}
		}
	}
}
