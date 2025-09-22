<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes;

use Inc\Exceptions\FormCreateException;
use Inc\Utils;

use \Exception;
use \Throwable;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * FormStats
 */
class FormStats
{
	public $id;
	public $form_id;
	public $post_id;
	public $loads;
	public $views;
	public $submissions;
	public $occurred_at;
	private $table_name;

	/**
	 * __construct
	 *
	 * @param  int $id
	 * @param  int $form_id
	 * @param  int $post_id
	 * @param  int $loads
	 * @param  int $views
	 * @param  int $submissions
	 * @param  string $occurred_at
	 * @return void
	 */
	public function __construct($id = 0, $form_id = 0, $post_id = 0, $loads = 0, $views = 0, $submissions = 0, $occurred_at = '')
	{
		global $wpdb;

		$this->table_name 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_stats';
		$this->id 			= $id;
		$this->form_id 		= $form_id;
		$this->post_id 		= $post_id;
		$this->loads 		= $loads;
		$this->views 		= $views;
		$this->submissions 	= $submissions;
		$this->occurred_at 	= !empty($occurred_at) ? $occurred_at : date('Y-m-d');

		return $this;
	}

	/**
	 * form_object
	 *
	 * @param  mixed $data
	 * @return void
	 */
	public function form_object($data)
	{
		if (is_object($data)) $data = (array) $data;

		$this->id 			= isset($data['id']) ? intval($data['id']) : 0;
		$this->form_id 		= isset($data['form_id']) ? sanitize_text_field($data['form_id']) : '';
		$this->post_id 		= isset($data['post_id']) ? intval($data['post_id']) : 0;
		$this->loads 		= isset($data['loads']) ? intval($data['loads']) : 0;
		$this->views 		= isset($data['views']) ? intval($data['views']) : 0;
		$this->submissions 	= isset($data['submissions']) ? intval($data['submissions']) : 0;
		$this->occurred_at 	= isset($data['occurred_at']) ? Utils::sanitize_date($data['occurred_at']) : date('Y-m-d');

		return $this;
	}

	/**
	 * create
	 *
	 * @return void
	 */
	public function create()
	{
		global $wpdb;

		$success = $wpdb->insert(
			$this->table_name,
			[
				'form_id' => $this->form_id,
				'post_id' => $this->post_id,
				'loads' => $this->loads,
				'views' => $this->views,
				'submissions' => $this->submissions,
				'occurred_at' => $this->occurred_at
			]
		);

		if ($success !== false) $this->id = $wpdb->insert_id;

		$error = $wpdb->last_error;

		if ($error) {
			error_log($error);
			throw new FormCreateException('We recieved an error while attempting to create your form, please try again');
		}
	}

	/**
	 * create_from_ids
	 *
	 * @param  int $post_id
	 * @param  array $form_ids
	 * @param  string $type (loads | views | submissions)
	 * @param  int $loads
	 * @param  int $views
	 * @param  int $submissions
	 * @return void
	 */
	public static function create_from_ids($post_id, $form_ids, $type, $loads, $views, $submissions)
	{
		global $wpdb;

		$today 			= date('Y-m-d');
		$values 		= [];
		$placeholders 	= [];

		try {
			foreach ($form_ids as $form_id) {
				// Since form_ids are coming from the browser, we must check if it is a valid integer
				if (!is_numeric($form_id)) continue;

				$values[] = $form_id;
				$values[] = $post_id;
				$values[] = $loads;
				$values[] = $views;
				$values[] = $submissions;
				$values[] = $today;
				$placeholders[] = "('%d', '%d', '%d', '%d', '%d', '%s')";
			}

			// Convert values array into a string of placeholders
			$values_placeholder_string = implode(", ", $placeholders);

			$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_stats';

			// Prepare sql statement
			$sql = "INSERT INTO $table_name (form_id, post_id, loads, views, submissions, occurred_at) VALUES $values_placeholder_string ON DUPLICATE KEY UPDATE $type = $type + VALUES($type)";

			// Execute the statement
			$wpdb->query($wpdb->prepare($sql, $values));
		} catch (Exception $e) {
			error_log($e->getMessage());
			
		} catch (Throwable $t) {
			error_log($t->getMessage());
		}
	}
}
