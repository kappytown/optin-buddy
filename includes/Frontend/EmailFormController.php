<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Frontend;

use Inc\Frontend\BaseController;
use Inc\Frontend\EmailFormHandler;
use Inc\Classes\Settings;
use Inc\Classes\Form;
use Inc\Classes\FormTemplates;

use \Exception;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * EmailFormController
 * This class handles all frontend form rendering and processing
 * 
 * $form_handler (EmailFormHandler class) handles all ajax requests for processing each form
 */
class EmailFormController extends BaseController
{
	/**
	 * settings
	 *
	 * @var class Settings
	 */
	private $settings;

	/**
	 * form_handler
	 * Handles all ajax requests for processing each form
	 *
	 * @var class EmailFormHandler
	 */
	private $form_handler;

	/**
	 * post_id
	 *
	 * @var int
	 */
	private $post_id = 0;

	/**
	 * page_type
	 *
	 * @var string
	 */
	private $page_type = 'page';

	/**
	 * page_title
	 *
	 * @var string
	 */
	private $page_title = '';

	/**
	 * page_url
	 *
	 * @var string
	 */
	private $page_url = '';

	/**
	 * target_categories
	 *
	 * List of matched categories if any
	 * 
	 * @var array
	 */
	private $target_categories = [];

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->settings = new Settings();

		// Exit if no provider has been selected
		if (empty($this->settings->provider)) return;

		$this->form_handler = new EmailFormHandler();

		// Handles page/post content
		add_filter('the_content', [$this, 'on_page_content']);

		// Handles category landing content
		add_filter('term_description', [$this, 'on_category_content'], 10, 4);

		// Used to render our form
		add_shortcode('optin_buddy', [$this, 'render_shortcode']);

		// Hook used to get the page id, title, and url
		add_action('template_redirect', [$this, 'get_page_type']);

		// Enqueue required files and disable autosave
		add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

		// Handles the ajax request. Be sure to post the action handle_form_submission_request in the ajax request
		add_action('wp_ajax_handle_form_submission_request', [$this->form_handler, 'handle_form_submission_request']);
		add_action('wp_ajax_nopriv_handle_form_submission_request', [$this->form_handler, 'handle_form_submission_request']);

		add_action('wp_ajax_handle_form_impression_request', [$this->form_handler, 'handle_form_impression_request']);
		add_action('wp_ajax_nopriv_handle_form_impression_request', [$this->form_handler, 'handle_form_impression_request']);

		add_action('wp_ajax_handle_new_nonce_request', [$this->form_handler, 'handle_new_nonce_request']);
		add_action('wp_ajax_nopriv_handle_new_nonce_request', [$this->form_handler, 'handle_new_nonce_request']);
	}

	/**
	 * get_page_type
	 * Determines the current page type (page, post, category) as well as the page id, title, and url
	 * These values are used when rendering the form and also passed as hidden fields
	 *
	 * @return void
	 */
	public function get_page_type()
	{
		global $wp, $post;

		if (is_category()) $this->page_type = 'category';
		if (is_page()) $this->page_type = 'page';
		if (is_single()) $this->page_type = 'post';

		$this->post_id 		= isset($post) ? $post->ID : 0;
		$this->page_title 	= is_category('', false) ? single_cat_title('', false) : (isset($post->post_title) ? $post->post_title : '');
		$this->page_url 	= home_url(add_query_arg(array(), $wp->request));
	}

	/**
	 * enqueue_frontend_scripts
	 * Enqueues the required frontend scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_frontend_scripts()
	{
		// Do NOT load in admin pages
		if (!is_admin()) {
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'frontend-inview-script', OPTIN_BUDDY_URL . 'assets/js/utils/inview.js', [], OPTIN_BUDDY_VERSION);
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'frontend-modal-script', OPTIN_BUDDY_URL . 'assets/js/utils/modal.js', [], OPTIN_BUDDY_VERSION);
			wp_enqueue_script(OPTIN_BUDDY_PREFIX . 'frontend-form-script', OPTIN_BUDDY_URL . 'assets/js/forms/form.js', [OPTIN_BUDDY_PREFIX . 'frontend-inview-script', OPTIN_BUDDY_PREFIX . 'frontend-modal-script'], OPTIN_BUDDY_VERSION);

			wp_localize_script(OPTIN_BUDDY_PREFIX . 'frontend-form-script', 'EmailFormAjax', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce(OPTIN_BUDDY_PREFIX . 'email_form_nonce')
			]);

			wp_enqueue_style(OPTIN_BUDDY_PREFIX . 'frontend-form-modal', OPTIN_BUDDY_URL . 'assets/css/modal.css', [], OPTIN_BUDDY_VERSION);
		}
	}

	/**
	 * render_shortcode
	 * Renders the form via the shortcode [optin_buddy hash='' form_id='' template_id='' inview='']
	 *
	 * @param  mixed $atts
	 * @param  mixed $content
	 * @param  mixed $tag
	 * @return string
	 */
	public function render_shortcode($atts = [], $content = null, $tag = '')
	{
		global $post;

		try {
			ob_start();

			$inview 			= isset($atts['inview']) ? intval($atts['inview']) : 0;
			$atts['post_id'] 	= isset($post) ? $post->ID : 0;
			$atts['page_title'] = $this->page_title;
			$atts['page_url'] 	= $this->page_url;

			// Includes the required css and js files as well as renders the form
			FormTemplates::include_files($atts);

			// Get the content from the buffer and add the fields
			$var = ob_get_clean();
			$var = str_replace('<!--{{fields}}-->', $this->getHiddenFields($atts), $var);

			// Replace the form_id - this is required to add custom styling in FormTemplates->include_files
			$var = str_replace('{{form_id}}', $atts['form_id'], $var);

			// Set display none and the javascript will show them
			return "<div class='opt-bud form-" . $atts['template_id'] . "' data-hash='" . $atts['hash'] . "' " . ($inview ?  'data-inview="1"' : '') . "style='display:none;'>$var</div>";
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		return "";
	}

	/**
	 * getHiddenFields
	 * Generates the hidden fields used in the form
	 *
	 * @param  mixed $atts
	 * @return string
	 */
	private function getHiddenFields($atts = [])
	{
		$hiddenFields = '';

		if (!isset($atts['form_id'])) return '';

		$fields = [
			'post_id' 				=> $this->post_id,
			'page_title' 			=> $this->page_title,
			'page_url' 				=> $this->page_url,
			'form_id' 				=> $atts['form_id'],
			'target_categories[]' 	=> maybe_serialize($this->target_categories)
		];
		foreach ($fields as $key => $value) {
			$hiddenFields .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />";
		}
		return $hiddenFields;
	}

	/**
	 * on_page_content
	 * Inserts the shortcode into the page/post content
	 *
	 * @param  mixed $content
	 * @return void
	 */
	public function on_page_content($content)
	{
		return $this->insert_shortcode_into_content($content);
	}

	/**
	 * on_category_content
	 * Inserts the shortcode into the category description
	 *
	 * @param  mixed $description
	 * @param  mixed $term
	 * @param  mixed $taxonomy
	 * @param  mixed $context
	 * @return string
	 */
	public function on_category_content($description, $term, $taxonomy, $context)
	{
		// Check if it's a category description
		if ('category' === $taxonomy) {
			return $this->insert_shortcode_into_content($description);
		}
		return $description;
	}

	/**
	 * insert_shortcode_into_content
	 * Inserts the shortcode into the content based on the form settings
	 *
	 * @param  mixed $content
	 * @return void
	 */
	private function insert_shortcode_into_content($content)
	{
		$original_content 	= $content;
		$new_content 		= '';

		try {
			$query_results = Form::read_forms();
			$results = $query_results['results'];

			// If there are any forms to process...
			if (!empty($results)) {
				// Split the content by paragraphs
				$paragraphs 	= explode("</p>", $content);

				foreach ($results as $result) {
					$Form = (new Form())->from_object($result);

					// Do not handle deactivated forms
					if ($Form->deactivate == 1) continue;

					$form_id 				= $Form->id;
					$type_id 				= $Form->type_id;
					$template_id 			= $Form->template_id;
					$page_type 				= $Form->page_type;
					$page_location 			= $Form->page_location;
					$page_location_value 	= $Form->page_location_value;
					$form_location 			= $Form->form_location;
					$page_timing 			= $Form->page_timing;
					$page_timing_value 		= $Form->page_timing_value;
					$inview 				= intval($Form->inview);

					// The page type MUST match the specified page type in order to be shown
					if ($page_type !== 'all' && $page_type !== $this->page_type) {
						continue;
					}

					// Is this page in the forms URL exclusion list?
					if (!empty($Form->exclusion_list)) {
						$list = $Form->get_exclusion_list_array();

						// Just in case there is a trailing / from the page_url, let's remove it
						$url = preg_replace('/\/$/', '', $this->page_url);

						// Ignore if URL is in the list
						if (in_array($url, $list)) {
							continue;
						}
					}

					// If this form contains a list of categories to filter by...
					if (!empty($Form->target_categories)) {
						// Category not found so do NOT present this form
						if (!$this->hasCategory($Form->target_categories)) {
							continue;
						}
					}

					// This will allow us to identify duplicates and hide them if desired
					$location_hash = '';
					// Prevent overlapping...
					if ($type_id == 1 && $this->settings->prevent_overlapping == 1) {
						$location_hash = md5($page_location . $page_location_value);
					}

					// Apply the inview effect to inline_forms only
					$hasInview = $type_id == 1 && $inview === 1 ? 1 : 0;

					// Returns the shortcode content from $this->render_shortcode
					$shortcode_content = do_shortcode("[optin_buddy hash='{$location_hash}' form_id='{$form_id}' template_id='{$template_id}' inview='{$hasInview}']");

					// If inline_form and inserting before or after the specified paragraph number...
					if ($type_id == 1 && in_array($page_location, ['before_paragraph', 'after_paragraph'])) {
						if ('before_paragraph' === $page_location) {
							$page_location_value = intval($page_location_value);
							// Insert before the specified paragraph
							if (isset($paragraphs[$page_location_value - 1])) {
								$paragraphs[$page_location_value - 1] = $shortcode_content . $paragraphs[$page_location_value - 1];
							}

						} else if ('after_paragraph' === $page_location) {
							$page_location_value = intval($page_location_value);
							// Insert after the specified paragraph
							if (isset($paragraphs[$page_location_value - 1])) {
								$paragraphs[$page_location_value - 1] .= $shortcode_content;
							}
						}
					} else {
						$type = $page_location;
						$value = $page_location_value;

						if ($type_id == 1) {
							//
						} else if ($type_id == 2) {
							$type = 'floating_box';
							$value = $form_location;

						} else if ($type_id == 3) {
							$type = 'modal_popup';
							$value = '';

						} else if ($type_id == 4) {
							$type = 'fixed_top';
							$value = '';

						} else if ($type_id == 5) {
							$type = 'exit_intent';
							$value = '';
						}

						// Here we insert the form before the first paragraph so that we can move them using javascript
						$paragraphs[0] = ("<div class='opt-bud-hidden hidden' data-type='{$type}' data-value='{$value}' data-timing='{$page_timing}' data-timing-value='{$page_timing_value}'>" . $shortcode_content . "</div>") . $paragraphs[0];
					}
				}
				
				// Reconstruct the content
				$new_content = implode("</>", $paragraphs);
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		return (!empty($new_content)) ? $new_content : $original_content;
	}

	/**
	 * hasCategory
	 * Checks to see if the current page contains any of the specified catetogries
	 *
	 * @param  array $categoryList - can be an array of names, slugs, or ids
	 * @return bool
	 */
	private function hasCategory($categoryList = [])
	{
		global $post;

		if (!$post) return false;

		$match = false;

		$this->target_categories = [];

		try {
			// Fetch the categories from the current post
			$categories = get_the_category($post->ID);

			// Check if any of the current post's categories match the target categories
			foreach ($categories as $category) {
				if (in_array($category->name, $categoryList) || in_array($category->slug, $categoryList) || in_array($category->term_id, $categoryList)) {
					$match = true;
					array_push($this->target_categories, $category->slug);
				}
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		return $match;
	}
}
