<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes;

use Inc\Exceptions\FormCreateException;
use Inc\Exceptions\FormReadException;
use Inc\Exceptions\FormUpdateException;
use Inc\Exceptions\FormDeleteException;

use \Exception;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Form
 */
class Form
{
	/**
	 * id
	 *
	 * @var int
	 */
	public $id = 0;

	/**
	 * type_id
	 *
	 * The id of the display type (inline, popupk)
	 * 
	 * @var int
	 */
	public $type_id = 0;

	/**
	 * template_id
	 *
	 * @var int The id of the selected form template to use for every form
	 */
	public $template_id = 0;

	/**
	 * image_id
	 *
	 * We do not save this prop
	 * 
	 * @var int
	 */
	public $image_id = 0;

	/**
	 * header
	 *
	 * @var string Default text for every form
	 */
	public $header = 'Subscribe to my email list';

	/**
	 * body
	 *
	 * @var string Default text for every form
	 */
	public $body = 'Get healthy recipes, meal plans, kitchen tips, & more delivered to your inbox every week! Plus, you\'ll get my 7-day meal plan and real food guide.';

	/**
	 * button
	 *
	 * @var string Default button text for every form
	 */
	public $button = 'Subscribe';

	/**
	 * disclaimer
	 *
	 * @var string
	 */
	public $disclaimer = "We respect your privacy. Unsubscribe at any time.";

	/**
	 * success
	 *
	 * @var string
	 */
	public $success = 'Success! Now check your email to confirm your subscription.';

	/**
	 * has_name_field
	 *
	 * Set to 1 if the form has the name field
	 * 
	 * @var int
	 */
	public $has_name_field = 0;

	/**
	 * send_email
	 * 
	 * Set to 1 if the user want to send a custom email to every subscriber of this form. 
	 * send_email_message must contain a value in order to send.
	 *
	 * @var int
	 */
	public $send_email = 0;

	/**
	 * send_email_subject
	 * 
	 * Subject for the custom message
	 *
	 * @var string
	 */
	public $send_email_subject = '';

	/**
	 * send_email_message
	 *
	 * The custom message to send to every subsriber of this form as long as send_email is set to 1
	 * 
	 * @var string
	 */
	public $send_email_message = '';

	/**
	 * inview
	 * 
	 * 0 = off and 1 = on
	 * Stored as an int if we later decide to specify which location has the effect
	 * 
	 * @var int
	 */
	public $inview = 0;

	/**
	 * page_type
	 *
	 * Type of page this form will appear on.
	 * Possible Values: all, post, category
	 * 
	 * @var string
	 */
	public $page_type = '';

	/**
	 * page_location
	 * 
	 * The location on the page where this form will be inserted into.
	 * This only applies to the inline form
	 * Possible values: before_paragraph, after_paragraph, before_element, after_element
	 *
	 * @var string
	 */
	public $page_location = '';

	/**
	 * page_location_value
	 *
	 * The value associated with the selected page location.
	 * If paragraph is selected then this should contain an integer otherwise string
	 * This only applies to the inline form
	 * 
	 * @var string
	 */
	public $page_location_value = '';

	/**
	 * form_location
	 *
	 * Location from where the form will appear from
	 * This only applies to the floating_box type
	 * Possible values: bottom_left, bottom_right
	 * 
	 * @var string
	 */
	public $form_location = '';

	/**
	 * page_timing
	 *
	 * Type if timing delay such as scroll or time delay
	 * This applies to: floating_box, modal_popup, fixed_bar, exit_intent
	 * 
	 * @var string
	 */
	public $page_timing = '';

	/**
	 * page_timing_value
	 *
	 * The amount of seconds or scroll percent to wait before showing this form.
	 * This applies to: floating_box, modal_popup, fixed_bar, exit_intent
	 * 
	 * @var int
	 */
	public $page_timing_value = 0;

	/**
	 * target_categories
	 *
	 * @var array
	 */
	public $target_categories = [];	// list of targeted categories

	/**
	 * exclusion_list
	 *
	 * @var string
	 */
	public $exclusion_list = '';	// Comma delimited list of urls to exclude this form from

	/**
	 * custom_css
	 *
	 * This will contain any custom CSS styles
	 * 
	 * @var array
	 */
	public $custom_css = '';

	/**
	 * meta
	 * 
	 * Array of meta data used to send to the provider.
	 * [key => 'form_id', name => 'Form ID', value => '233ewed']
	 *
	 * @var mixed
	 */
	public $meta = [];

	/**
	 * deactivate
	 *
	 * Set to 1 to deactivate 
	 * 
	 * @var int
	 */
	public $deactivate = 0;

	/**
	 * deleted
	 *
	 * We do not removed deleted forms from the database
	 * 
	 * @var int
	 */
	public $deleted = 0;

	/**
	 * table_name
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * __construct
	 *
	 * @return object
	 */
	public function __construct()
	{
		global $wpdb;

		$this->table_name 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';

		return $this;
	}

	/**
	 * from_object
	 *
	 * @param object $data
	 * @return object
	 */
	public function from_object($data)
	{
		// Cast data into an array
		if (is_object($data)) $data = (array) $data;

		$this->id 					= isset($data['id']) ? intval($data['id']) : 0;
		$this->type_id 				= isset($data['type_id']) ? intval($data['type_id']) : $this->type_id;
		$this->template_id 			= isset($data['template_id']) ? intval($data['template_id']) : $this->template_id;
		$this->image_id 			= isset($data['image_id']) ? intval($data['image_id']) : $this->image_id;
		$this->header 				= isset($data['header']) ? sanitize_text_field($data['header']) : $this->header;
		$this->body 				= isset($data['body']) ? sanitize_textarea_field($data['body']) : $this->body;
		$this->button 				= isset($data['button']) ? sanitize_text_field($data['button']) : $this->button;
		$this->disclaimer 			= isset($data['disclaimer']) ? sanitize_text_field($data['disclaimer']) : $this->disclaimer;
		$this->success 				= isset($data['success']) ? sanitize_textarea_field($data['success']) : $this->success;
		$this->has_name_field 		= isset($data['has_name_field']) ? intval($data['has_name_field']) : $this->has_name_field;
		$this->send_email 			= isset($data['send_email']) ? intval($data['send_email']) : $this->send_email;
		$this->send_email_subject 	= isset($data['send_email_subject']) ? sanitize_text_field($data['send_email_subject']) : $this->send_email_subject;
		$this->send_email_message 	= isset($data['send_email_message']) ? wp_kses_post($data['send_email_message']) : $this->send_email_message;
		$this->inview 				= isset($data['inview']) ? intval($data['inview']) : $this->inview;
		$this->page_type 			= isset($data['page_type']) ? sanitize_text_field($data['page_type']) : $this->page_type;
		$this->page_location 		= isset($data['page_location']) ? sanitize_text_field($data['page_location']) : $this->page_location;
		$this->page_location_value 	= isset($data['page_location_value']) ? sanitize_text_field($data['page_location_value']) : $this->page_location_value;
		$this->form_location 		= isset($data['form_location']) ? sanitize_text_field($data['form_location']) : $this->form_location;
		$this->page_timing 			= isset($data['page_timing']) ? sanitize_text_field($data['page_timing']) : $this->page_timing;
		$this->page_timing_value 	= isset($data['page_timing_value']) ? intval($data['page_timing_value']) : $this->page_timing_value;
		$this->target_categories 	= !empty($data['target_categories']) ? explode(',', $data['target_categories']) : [];
		$this->exclusion_list 		= isset($data['exclusion_list']) ? sanitize_textarea_field($data['exclusion_list']) : $this->exclusion_list;
		$this->custom_css 			= isset($data['custom_css']) ? sanitize_textarea_field($data['custom_css']) : $this->custom_css;
		$this->meta 				= isset($data['meta']) ? maybe_unserialize($data['meta']) : [];
		$this->deactivate 			= isset($data['deactivate']) ? intval($data['deactivate']) : $this->deactivate;
		$this->deleted 				= isset($data['deleted']) ? intval($data['deleted']) : $this->deleted;

		return $this;
	}

	/**
	 * from_id
	 *
	 * @param  int $id
	 * @return object
	 */
	public function from_id($id)
	{
		$this->id = $id;
		$results = $this->read();

		if ($results) {
			$this->id 							= $results->id;
			$this->type_id 						= $results->type_id;
			$this->template_id 					= $results->template_id;
			$this->image_id 					= $results->image_id;
			$this->header 						= $results->header;
			$this->body 						= $results->body;
			$this->button 						= $results->button;
			$this->disclaimer					= $results->disclaimer;
			$this->success 						= $results->success;
			$this->has_name_field 				= $results->has_name_field;
			$this->send_email 					= $results->send_email;
			$this->send_email_subject 			= $results->send_email_subject;
			$this->send_email_message 			= $results->send_email_message;
			$this->inview 						= $results->inview;
			$this->page_type 					= $results->page_type;
			$this->page_location 				= $results->page_location;
			$this->page_location_value 			= $results->page_location_value;
			$this->form_location 				= $results->form_location;
			$this->page_timing 					= $results->page_timing;
			$this->page_timing_value 			= $results->page_timing_value;
			$this->target_categories 			= !empty($results->target_categories) ? explode(',', $results->target_categories) : [];
			$this->exclusion_list 				= $results->exclusion_list;
			$this->custom_css 					= $results->custom_css;
			$this->meta 						= maybe_unserialize($results->meta);
			$this->deactivate 					= $results->deactivate;
			$this->deleted 						= $results->deleted;
		}

		return $this;
	}

	/**
	 * get_url
	 *
	 * @return void
	 */
	public function get_url()
	{
		// Converts the ID to URL for preview
		return !empty($this->image_id) ? wp_get_attachment_url($this->image_id) : OPTIN_BUDDY_URL . 'assets/img/japanese-food.jpg';
	}

	/**
	 * get_exclusion_list_array
	 * 
	 * Returns the exclusion list as an array
	 *
	 * @return array
	 */
	public function get_exclusion_list_array()
	{
		if (!empty($this->exclusion_list)) {
			// Replace all whitespace with a comma so we can format into an array
			$list = preg_replace('/\s+/', ',', $this->exclusion_list);
			// Remove all trailing / that is followed by a comma OR end of line
			$list = preg_replace('/(\/(,|$))/', ',', $list);
			// Remove trailing comma if any
			$list = preg_replace('/,$/', '', $list);
			return explode(',', $list);
		}
		return [];
	}

	/**
	 * get_custom_css
	 * 
	 * This will append the class selector ($class) to all instances of email-container 
	 * to prevent it from affecting other forms on the page.
	 *
	 * @param  string $class - e.g. .form-1[data-id="1"]
	 * @return string
	 */
	public function get_custom_css($class = '')
	{
		$styles = $this->custom_css;
		$styles = str_replace('email-container', "email-container$class", $styles);

		return $styles;
	}

	/**
	 * create
	 *
	 * @return void
	 */
	public function create()
	{
		global $wpdb;

		$props = [
			'type_id' 					=> $this->type_id,
			'template_id' 				=> $this->template_id,
			'image_id' 					=> $this->image_id,
			'header' 					=> $this->header,
			'body' 						=> $this->body,
			'button' 					=> $this->button,
			'disclaimer' 				=> $this->disclaimer,
			'success' 					=> $this->success,
			'has_name_field' 			=> $this->has_name_field,
			'send_email' 				=> $this->send_email,
			'send_email_subject' 		=> $this->send_email_subject,
			'send_email_message' 		=> $this->send_email_message,
			'inview' 					=> $this->inview,
			'page_type' 				=> $this->page_type,
			'page_location' 			=> $this->page_location,
			'page_location_value' 		=> $this->page_location_value,
			'form_location' 			=> $this->form_location,
			'page_timing' 				=> $this->page_timing,
			'page_timing_value' 		=> $this->page_timing_value,
			'target_categories' 		=> implode(',', $this->target_categories),
			'exclusion_list' 			=> $this->exclusion_list,
			'custom_css' 				=> $this->custom_css,
			'meta' 						=> maybe_serialize($this->meta),
			'deactivate' 				=> $this->deactivate,
			'deleted' 					=> $this->deleted
		];

		$success = $wpdb->insert(
			$this->table_name,
			$props
		);

		if ($success !== false) $this->id = $wpdb->insert_id;

		$error = $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormCreateException('We recieved an error while attempting to create your form, please try again');
		}
	}

	/**
	 * read
	 *
	 * @return object
	 */
	public function read()
	{
		global $wpdb;

		$result = $wpdb->get_row(sprintf("SELECT *, DATE_FORMAT(time, '%%b %%e, %%Y') AS date FROM $this->table_name WHERE id=%d LIMIT 0,1", $this->id));
		$error 	= $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormReadException('We recieved an error while attempting to retreieve your form, please try again');
		}

		return $result;
	}

	/**
	 * update
	 *
	 * @return void
	 */
	public function update()
	{
		global $wpdb;

		$props = [
			'type_id' 					=> $this->type_id,
			'template_id' 				=> $this->template_id,
			'image_id' 					=> $this->image_id,
			'header' 					=> $this->header,
			'body' 						=> $this->body,
			'button' 					=> $this->button,
			'disclaimer' 				=> $this->disclaimer,
			'success' 					=> $this->success,
			'has_name_field' 			=> $this->has_name_field,
			'send_email' 				=> $this->send_email,
			'send_email_subject' 		=> $this->send_email_subject,
			'send_email_message' 		=> $this->send_email_message,
			'inview' 					=> $this->inview,
			'page_type' 				=> $this->page_type,
			'page_location' 			=> $this->page_location,
			'page_location_value' 		=> $this->page_location_value,
			'form_location' 			=> $this->form_location,
			'page_timing' 				=> $this->page_timing,
			'page_timing_value' 		=> $this->page_timing_value,
			'target_categories' 		=> implode(',', $this->target_categories),
			'exclusion_list' 			=> $this->exclusion_list,
			'custom_css' 				=> $this->custom_css,
			'meta' 						=> maybe_serialize($this->meta),
			'deactivate' 				=> $this->deactivate,
			'deleted' 					=> $this->deleted
		];

		$wpdb->update(
			$this->table_name,
			$props,
			[
				'id' => $this->id
			]
		);

		$error = $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormUpdateException('We recieved an error while attempting to update your form, please try again');
		}
	}

	/**
	 * delete
	 *
	 * @return void
	 */
	public function delete()
	{
		global $wpdb;

		$this->deleted = 1;

		$wpdb->update(
			$this->table_name,
			[
				'deleted' => $this->deleted
			],
			[
				'id' => $this->id
			]
		);
		//$wpdb->delete($this->table_name, ['id' => $this->id]);

		$error = $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormDeleteException('We recieved an error while attempting to delete your form, please try again');
		}
	}

	/**
	 * set_id
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * read_form
	 *
	 * @param  mixed $id
	 * @return object
	 */
	public static function read_form($id)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$results 	= $wpdb->get_row(sprintf("SELECT *, CASE WHEN template_id = 6 THEN 'No Title' ELSE header END AS header, DATE_FORMAT(time, '%%b %%e, %%Y') AS date FROM $table_name WHERE id=%d LIMIT 0,1", $id));

		$error = $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormReadException('We recieved an error while attempting to retreive your form, please try again');
		}

		return $results;
	}

	/**
	 * read_forms
	 *
	 * @param  mixed $limit
	 * @return array
	 */
	public static function read_forms($limit = 10, $page = 1)
	{
		$page 	= $page < 1 ? 1 : $page;
		$offset = ($page - 1) * $limit;

		// pagination
		$sql = "SELECT COUNT(*) FROM {{table_name}} WHERE deleted = 0";
		$pagination = self::get_pagination_results($sql, $limit, $page);

		$results = self::get_results(sprintf("SELECT *, CASE WHEN template_id = 6 THEN 'No Title' ELSE header END AS header, DATE_FORMAT(time, '%%b %%e, %%Y') AS date FROM {{table_name}} WHERE deleted = 0 LIMIT 0, %d", $limit));

		return ['pagination' => $pagination, 'results' => $results];
	}

	/**
	 * update_form
	 *
	 * @param  mixed $form
	 * @return void
	 */
	public static function update_form($form)
	{
		global $wpdb;

		$form = (array)$form;

		// Loop over all the form keys and remove any key that is NOT part of the Form Object
		// Since this is in a static method, we must create an empty instance of the Form object to access its properties
		$MyForm = new Form();
		foreach ($form as $key => $value) {
			if (!property_exists($MyForm, $key)) {
				unset($form[$key]);
			}
		}

		$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';

		$wpdb->update(
			$table_name,
			$form,
			[
				'id' => $form['id']
			]
		);

		$error = $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormUpdateException('We recieved an error while attempting to update your form, please try again');
		}
	}

	/**
	 * delete_form
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public static function delete_form($id)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';

		$wpdb->update(
			$table_name,
			[
				'deleted' => 1
			],
			[
				'id' => $id
			]
		);

		//$wpdb->delete($table_name, ['id' => $id]);

		$error = $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormDeleteException('We recieved an error while attempting to create your form, please try again');
		}
	}

	/**
	 * get_row
	 *
	 * @param  mixed $sql
	 * @return array
	 */
	public static function get_row($sql)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$sql 		= str_replace('{{table_name}}', $table_name, $sql);

		$results 	= $wpdb->get_row($sql);
		$error 		= $wpdb->last_error;

		if ($error) {
			error_log($error);
			throw new FormReadException('We recieved an error while attempting to retreive your form, please try again');
		}

		return $results;
	}

	/**
	 * get_results
	 *
	 * @param  mixed $sql
	 * @return array
	 */
	public static function get_results($sql)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$sql 		= str_replace('{{table_name}}', $table_name, $sql);

		$results 	= $wpdb->get_results($sql);
		$error 		= $wpdb->last_error;

		if ($error) {
			error_log($error);
			throw new FormReadException('We recieved an error while attempting to retreive your form, please try again');
		}

		return $results;
	}

	/**
	 * get_var
	 *
	 * @param  mixed $sql
	 * @return mixed
	 */
	public static function get_var($sql)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$sql 		= str_replace('{{table_name}}', $table_name, $sql);

		$result = $wpdb->get_var($sql);
		$error 	= $wpdb->last_error;

		if ($error) {
			error_log($error);
			throw new FormReadException('We recieved an error while attempting to retreive your form, please try again');
		}

		return $result;
	}

	/**
	 * get_pagination_results
	 *
	 * @param  string $sql
	 * @param  int $limit
	 * @param  int $page
	 * @return array
	 */
	private static function get_pagination_results($sql, $limit, $page)
	{
		$total_items = self::get_var($sql);
		$total_pages = ceil($total_items / $limit);
		//error_log("limit: $limit, page: $page, total_items: $total_items, total_pages: $total_pages);
		//error_log($sql);

		return [
			'current_page' 		=> intval($page),
			'total_items' 		=> intval($total_items),
			'total_pages' 		=> intval($total_pages),
			'items_per_page' 	=> intval($limit)
		];
	}
}
