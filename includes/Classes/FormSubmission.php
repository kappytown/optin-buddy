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
 * FormSubmission
 * 
 * This class stores all form submissions into the WP database
 * as well as provides methods to retrieve and analyze the data.
 */
class FormSubmission
{
	public $id;
	public $form_id;
	public $post_id;
	public $user_hash;
	public $email;
	public $meta;
	public $error;
	public $time;
	private $table_name;

	/**
	 * __construct
	 *
	 * @param  int $id
	 * @param  int $form_id
	 * @param  int $post_id
	 * @param  string $user_hash
	 * @param  string $email
	 * @param  array $meta
	 * @param  string $time
	 * @return void
	 */
	public function __construct($id = 0, $form_id = 0, $post_id = 0, $user_hash = '', $email = '', $meta = [], $error = '', $time = '')
	{
		global $wpdb;

		$this->table_name 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_submissions';
		$this->id 			= $id;
		$this->form_id 		= $form_id;
		$this->post_id 		= $post_id;
		$this->user_hash 	= $user_hash;
		$this->email 		= $email;
		$this->meta 		= $meta;
		$this->error 	 	= $error;
		$this->time 		= $time;

		return $this;
	}

	/**
	 * from_object
	 * Populates the class properties from the specified object
	 *
	 * @param object $data
	 * @return void
	 */
	public function from_object($data)
	{
		if (is_object($data)) $data = (array) $data;

		$this->id 			= isset($data['id']) ? intval($data['id']) : 0;
		$this->form_id 		= isset($data['form_id']) ? sanitize_text_field($data['form_id']) : '';
		$this->post_id 		= isset($data['post_id']) ? intval($data['post_id']) : 0;
		$this->user_hash 	= isset($data['user_hash']) ? sanitize_text_field($data['user_hash']) : '';
		$this->email 		= isset($data['email']) ? sanitize_email($data['email']) : '';
		$this->meta 		= isset($data['meta']) ? maybe_unserialize($data['meta']) : [];
		$this->error 		= isset($data['error']) ? sanitize_text_field($data['error']) : '';
		$this->time 		= isset($data['time']) ? intval($data['time']) : '';

		return $this;
	}

	/**
	 * create
	 * Creates a new form submission record in the WP database
	 *
	 * @return void
	 */
	public function create()
	{
		global $wpdb;

		/*$success = $wpdb->insert(
			$this->table_name,
			[
				'form_id' 		=> $this->form_id,
				'post_id' 		=> $this->post_id,
				'user_hash' 	=> $this->user_hash,
				'email' 		=> $this->email,
				'meta' 			=> maybe_serialize($this->meta),
				'error' 		=> $this->error
			]
		);*/

		$success = $wpdb->query($wpdb->prepare('INSERT IGNORE INTO ' . $this->table_name . '(form_id, post_id, user_hash, email, meta, error) VALUES(%d, %d, %s, %s, %s, %s)', $this->form_id, $this->post_id, $this->user_hash, $this->email, maybe_serialize($this->meta), $this->error));

		if ($success !== false) $this->id = $wpdb->insert_id;

		$error = $wpdb->last_error;

		if ($error) {
			error_log($error);
			throw new FormCreateException('We recieved an error while attempting to create your form, please try again');
		}
	}

	/**
	 * read
	 * Reads the form submission by id
	 *
	 * @return object
	 */
	public function read()
	{
		return self::get_row(sprintf("SELECT *, DATE_FORMAT(time, '%%b %%e, %%Y') AS time FROM {{table_submissions}} WHERE id=%d LIMIT 0,1", $this->id));
	}

	/**
	 * update
	 * Updates the form submission record in the WP database
	 *
	 * @return void
	 */
	public function update()
	{
		global $wpdb;

		$wpdb->update(
			$this->table_name,
			[
				'form_id' 	=> $this->form_id,
				'post_id' 	=> $this->post_id,
				'email' 	=> $this->email,
				'meta' 		=> maybe_serialize($this->meta),
				'error' 	=> $this->error
			],
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
	 * Deletes the form submission record in the WP database
	 *
	 * @return void
	 */
	public function delete()
	{
		self::delete_form($this->id);
	}

	/**
	 * set_id
	 * Sets the form submission id
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * get_row
	 * Returns a single row from the database based on the specified SQL
	 *
	 * @param  mixed $sql
	 * @return object
	 */
	public static function get_row($sql)
	{
		global $wpdb;

		$table_forms 		= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$table_submissions 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_submissions';
		$wp_posts 			= $wpdb->prefix . 'posts';
		$sql = str_replace('{{table_forms}}', $table_forms, $sql);
		$sql = str_replace('{{table_submissions}}', $table_submissions, $sql);
		$sql = str_replace('{{wp_posts}}', $wp_posts, $sql);

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
	 * Returns multiple rows from the database based on the specified SQL
	 *
	 * @param  mixed $sql
	 * @return array
	 */
	public static function get_results($sql)
	{
		global $wpdb;

		$table_forms 		= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$table_submissions 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_submissions';
		$wp_posts 			= $wpdb->prefix . 'posts';
		$sql = str_replace('{{table_forms}}', $table_forms, $sql);
		$sql = str_replace('{{table_submissions}}', $table_submissions, $sql);
		$sql = str_replace('{{wp_posts}}', $wp_posts, $sql);

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
	 * Returns a single variable from the database based on the specified SQL
	 *
	 * @param  mixed $sql
	 * @return mixed
	 */
	public static function get_var($sql)
	{
		global $wpdb;

		$table_forms 		= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'forms';
		$table_submissions 	= $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_submissions';
		$wp_posts 			= $wpdb->prefix . 'posts';
		$sql = str_replace('{{table_forms}}', $table_forms, $sql);
		$sql = str_replace('{{table_submissions}}', $table_submissions, $sql);
		$sql = str_replace('{{wp_posts}}', $wp_posts, $sql);

		$result = $wpdb->get_var($sql);
		$error 	= $wpdb->last_error;

		if ($error) {
			error_log($error);
			throw new FormReadException('We recieved an error while attempting to retreive your form, please try again');
		}

		return $result;
	}

	/**
	 * read_form_submission
	 * Returns a single form submission by id
	 *
	 * @param  mixed $id
	 * @return object
	 */
	public static function read_form_submission($id)
	{
		return self::get_row(sprintf("SELECT *, DATE_FORMAT(time, '%%b %%e, %%Y') AS date FROM {{table_submissions}} WHERE id=%d LIMIT 0, 1", $id));
	}

	/**
	 * read_form_submissions
	 * Returns a list of form submissions
	 *
	 * @param  mixed $limit
	 * @return array
	 */
	public static function read_form_submissions($limit = 10, $page = 1)
	{
		$page = $page < 1 ? 1 : $page;
		$offset = ($page - 1) * $limit;

		$sql = "SELECT COUNT(*) FROM {{table_submissions}}";
		$pagination = self::get_pagination_results($sql, $limit, $page);

		$results = self::get_results(sprintf("SELECT *, DATE_FORMAT(time, '%%b %%e, %%Y') AS date FROM {{table_submissions}} ORDER BY time DESC LIMIT %d, %d", $offset, $limit));

		return ['pagination' => $pagination, 'results' => $results];

/*
// HOW TO GROUP BY FORM ID AND SHOW HOW MANY DIFFERENT LOCATIONS PER ID
// e.g. This form was submitted 20 times in 4 different locations. This location was the most effective
// e.g. Between these dates, this form was the most effective. It was submitted 20 times in n different locations. This location was the most effective
// e.g. You have 20 new form submissions since July 10, 2023
SELECT 
	COUNT(form_id), 
    post_id, 
    form_id,  
    (
        SELECT 
     		GROUP_CONCAT(a.location SEPARATOR '\n') 
       	FROM 
        (
            SELECT 
            	form_id, 
				CONCAT(location, ' (', COUNT(location), ')') AS location
            FROM 
            	wp_opt_bud_form_submissions GROUP BY location
        ) AS a
        WHERE 
			a.form_id = fs.form_id
    ) AS numLocations,
    GROUP_CONCAT(DISTINCT provider ORDER BY provider ASC SEPARATOR '\n') AS providers,
    GROUP_CONCAT(DISTINCT location SEPARATOR '\n') AS locations
FROM 
	wp_opt_bud_form_submissions AS fs 
GROUP BY 
	form_id

///////////////////////////////////

SELECT
	COUNT(afs.form_id) AS numSubmissions,
    afs.*
FROM
    wp_opt_bud_forms AS af
INNER JOIN wp_opt_bud_form_submissions AS afs
ON
    af.id = afs.form_id
GROUP BY
    afs.form_id;
*/
	}

	/**
	 * get_form_submission_count
	 * 
	 * Returns the total number of form submissions for the specified form_id
	 *
	 * @param  mixed $form_id
	 * @return object
	 */
	public static function get_form_submission_count($form_id)
	{
		return self::get_row("SELECT IFNULL((" . sprintf("SELECT COUNT(id) AS totalSubmissions FROM {{table_submissions}} WHERE form_id=%d GROUP BY form_id LIMIT 0,1", $form_id) . "), 0) AS totalSubmissions");
	}

	/**
	 * get_errors
	 */
	public static function get_errors($start_date, $end_date, $limit = 10, $page = 1){}

	/**
	 * get_emails
	 */
	public static function get_emails($start_date, $end_date, $limit = 10, $page = 1){}

	/**
	 * read_daily_monthly_yearly_count
	 * 
	 * @return array
	 */
	public static function read_daily_monthly_yearly_count()
	{
		return self::get_results("SELECT DATE_FORMAT(CURDATE(), '%b %D') AS 'day', DATE_FORMAT(CURDATE(), '%M') AS 'month', DATE_FORMAT(CURDATE(), '%Y') AS 'year', (SELECT COUNT(*) AS count FROM {{table_submissions}} WHERE DATE(time) = CURDATE()) AS daily, (SELECT COUNT(*) AS count FROM {{table_submissions}} WHERE EXTRACT(YEAR_MONTH FROM time) = EXTRACT(YEAR_MONTH FROM CURDATE())) AS monthly, (SELECT COUNT(*) AS count FROM {{table_submissions}} WHERE YEAR(time) = YEAR(CURDATE())) AS yearly");
	}

	/**
	 * read_monthly_count_for_past_year
	 * 
	 * @return array
	 */
	public static function read_monthly_count_for_past_year()
	{
		return self::get_results("SELECT COUNT(*) AS count, DATE_FORMAT(time, '%b %Y') AS date, DATE(time) AS dt, EXTRACT(YEAR_MONTH FROM time) AS yearMonth FROM {{table_submissions}} GROUP BY yearMonth ORDER BY dt DESC LIMIT 12");
	}

	/**
	 * read_daily_count_for_past_month
	 * 
	 * @return array
	 */
	public static function read_daily_count_for_past_month()
	{
		return self::get_results("SELECT COUNT(*) AS count, DATE_FORMAT(time, '%b %d') AS date, DATE(time) AS dt FROM {{table_submissions}} WHERE DATE(time) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE() GROUP BY dt");
	}

	/**
	 * read_top_5_forms_all_time
	 * 
	 * @return array
	 */
	public static function read_top_5_forms_all_time()
	{
		return self::get_results("SELECT COUNT(sub.id) AS count, CASE WHEN form.template_id = 6 THEN 'No Title' ELSE form.header END AS title FROM {{table_submissions}} AS sub INNER JOIN {{table_forms}} AS form ON sub.form_id = form.id GROUP BY form.id ORDER BY count DESC limit 5;");
	}

	/**
	 * read_top_5_forms
	 * Returns the top 5 most submitted forms
	 *
	 * @param  string $start_date
	 * @param  string $end_date
	 * @return array
	 */
	public static function read_top_5_forms($start_date, $end_date)
	{
		return self::get_results(sprintf("SELECT COUNT(ts.form_id) AS count, CASE WHEN f.template_id = 6 THEN 'No Title' ELSE f.header END AS title FROM {{table_submissions}} ts iNNER JOIN {{table_forms}} f ON ts.form_id = f.id WHERE DATE(ts.time) BETWEEN '%s' AND '%s'  GROUP BY ts.form_id ORDER BY count DESC LIMIT 5", $start_date, $end_date));
	}

	/**
	 * read_top_5_pages
	 * Returns the top 5 pages forms were submitted on
	 * 
	 * @param  string $start_date
	 * @param  string $end_date
	 * @return array
	 */
	public static function read_top_5_pages($start_date, $end_date)
	{
		return self::get_results(sprintf("SELECT COUNT(p.id) AS count, p.id, p.post_title AS title FROM {{table_submissions}} ts INNER JOIN {{wp_posts}} p ON ts.post_id = p.id WHERE DATE(ts.time) BETWEEN '%s' AND '%s' GROUP BY p.id ORDER BY count DESC LIMIT 5", $start_date, $end_date));
	}

	/**
	 * read_daily_form_submissions
	 * Returns all the forms submitted each day for the last month
	 *
	 * @param  string $date
	 * @return array
	 */
	public static function read_daily_form_submissions($date)
	{
		return self::get_results(sprintf("SELECT COUNT(ts.form_id) AS count, ts.form_id, IFNULL(tf.header, 'No Title') AS title2, DATE_FORMAT(ts.time, '%%b %%e, %%Y') AS title FROM {{table_submissions}} AS ts INNER JOIN {{table_forms}} AS tf ON tf.id = ts.form_id WHERE DATE(ts.time) BETWEEN DATE_SUB('%s', INTERVAL 30 DAY) AND '%s' GROUP BY title, form_id ORDER BY title", $date, $date));
	}

	/**
	 * read_form_submissions_between_dates
	 * Returns a list of all form submissions between the specified dates
	 *
	 * @param  string $start_date
	 * @param  string $end_date
	 * @param  int $limit
	 * @return array
	 */
	public static function read_form_submissions_between_dates($start_date, $end_date, $limit = 10, $page = 1)
	{
		$page = $page < 1 ? 1 : $page;
		$offset = ($page - 1) * $limit;

		$sql = sprintf("SELECT COUNT(DISTINCT(ts.form_id)) FROM {{table_submissions}} AS ts WHERE DATE(ts.time) BETWEEN '%s' AND '%s'", $start_date, $end_date);
		$pagination = self::get_pagination_results($sql, $limit, $page);

		$results = self::get_results(sprintf("SELECT COUNT(ts.form_id) AS num, ts.form_id, IFNULL(tf.header, 'No Title') AS header, DATE_FORMAT(MAX(ts.time), '%%b %%e, %%Y') AS time, ts.meta FROM {{table_submissions}} AS ts LEFT JOIN {{table_forms}} AS tf ON tf.id = ts.form_id WHERE DATE(ts.time) BETWEEN '%s' AND '%s' GROUP BY ts.form_id ORDER BY num DESC LIMIT %d, %d", $start_date, $end_date, $offset, $limit));

		return ['pagination' => $pagination, 'results' => $results];
	}

	/**
	 * read_form_submissions_by_formid
	 * Returns a list of all submissions for the specified form id during the specified dates
	 *
	 * @param  int $form_id
	 * @param  int $limit
	 * @return array
	 */
	public static function read_form_submissions_by_formid($form_id, $start_date, $end_date, $limit = 10, $page = 1)
	{
		$page = $page < 1 ? 1 : $page;
		$offset = ($page - 1) * $limit;

		$sql = sprintf(sprintf("SELECT COUNT(p.id) FROM {{table_submissions}} ts INNER JOIN {{wp_posts}} p on ts.post_id = p.id WHERE form_id=%d AND DATE(ts.time) BETWEEN '%s' AND '%s'", $form_id, $start_date, $end_date));
		$pagination = self::get_pagination_results($sql, $limit, $page);

		$results = self::get_results(sprintf("SELECT p.id, p.post_title, p.post_type, ts.meta, DATE_FORMAT(ts.time, '%%b %%e, %%Y') AS time FROM {{table_submissions}} ts INNER JOIN {{wp_posts}} p on ts.post_id = p.id WHERE form_id=%d AND DATE(ts.time) BETWEEN '%s' AND '%s' ORDER BY ts.time DESC LIMIT %d, %d", $form_id, $start_date, $end_date, $offset, $limit));

		return ['pagination' => $pagination, 'results' => $results];
	}

	/**
	 * read_emails
	 * Returns a list of all captured emails during the form submission
	 *
	 * @param  string $start_date
	 * @param  string $end_date
	 * @param  int $limit
	 * @return array
	 */
	public static function read_emails($start_date, $end_date, $filter = '', $limit = 10, $page = 1)
	{
		$page = $page < 1 ? 1 : $page;
		$offset = ($page - 1) * $limit;
		$filter_clause = '';

		switch ($filter) {
			case 'succeeded':
				$filter_clause = 'error = "" AND ';
				break;
			case 'failed':
				$filter_clause = 'error != "" AND ';
				break;
			default:
				$filter_clause = '';
		}

		// pagination
		$sql = sprintf("SELECT COUNT(*) FROM {{table_submissions}} WHERE %s DATE(time) BETWEEN '%s' AND '%s'", $filter_clause, $start_date, $end_date);
		$pagination = self::get_pagination_results($sql, $limit, $page);

		$results = self::get_results(sprintf("SELECT email, DATE_FORMAT(time, '%%b %%e, %%Y') AS date, meta, CASE WHEN error != '' THEN 1 ELSE 0 END AS failed, error FROM {{table_submissions}} WHERE %s DATE(time) BETWEEN '%s' AND '%s' ORDER BY time DESC LIMIT %d, %d", $filter_clause, $start_date, $end_date, $offset, $limit));

		return ['pagination' => $pagination, 'results' => $results];
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
		$total_items 	= self::get_var($sql);
		$total_pages 	= ceil($total_items / $limit);

		return [
			'current_page' 		=> intval($page),
			'total_items' 		=> intval($total_items),
			'total_pages' 		=> intval($total_pages),
			'items_per_page' 	=> intval($limit)
		];
	}

	/**
	 * delete_form_submission
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public static function delete_form_submission($id)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . OPTIN_BUDDY_PREFIX . 'form_submissions';
		$wpdb->delete($table_name, ['id' => $id]);

		$error = $wpdb->last_error;
		if ($error) {
			error_log($error);
			throw new FormDeleteException('We recieved an error while attempting to create your form, please try again');
		}
	}
}
