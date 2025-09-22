<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Admin;

use Inc\Admin\BaseAdmin;
use Inc\Classes\Form;
use Inc\Classes\FormSubmission;

use \Exception;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * ReportsFormHandler
 */
class ReportsFormHandler extends BaseAdmin
{
	private $nonce = OPTIN_BUDDY_PREFIX . 'reports_nonce';

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * handle_settings_new_nonce_request
	 * Returns the new nonce
	 *
	 * @return void
	 */
	public function handle_reports_new_nonce_request()
	{
		$nonce = wp_create_nonce($this->nonce);
		wp_send_json(['success' => true, 'nonce' => $nonce]);
	}

	/**
	 * handle_dashboard_count_request
	 *
	 * @return void
	 */
	public function handle_dashboard_count_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		try {
			$query_results = FormSubmission::read_daily_monthly_yearly_count();

			$response = ['success' => true, 'results' => $query_results];
		} catch (Exception $e) {
			error_log($e->getMessage());
			$response = [
				'success' 	=> false,
				'error' 	=> 'Unable to process your request at this time, please try again shortly.'
			];
		}

		wp_send_json($response);
		die();
	}

	/**
	 * handle_dashboard_chart_monthly_request
	 *
	 * @return void
	 */
	public function handle_dashboard_chart_monthly_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		try {
			$query_results = FormSubmission::read_monthly_count_for_past_year();

			$response = ['success' => true, 'results' => $query_results];
		} catch (Exception $e) {
			error_log($e->getMessage());
			$response = [
				'success' 	=> false,
				'error' 	=> 'Unable to process your request at this time, please try again shortly.'
			];
		}

		wp_send_json($response);
		die();
	}

	/**
	 * handle_dashboard_chart_daily_request
	 *
	 * @return void
	 */
	public function handle_dashboard_chart_daily_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		try {
			$query_results = FormSubmission::read_daily_count_for_past_month();

			$response = ['success' => true, 'results' => $query_results];
		} catch (Exception $e) {
			error_log($e->getMessage());
			$response = [
				'success' 	=> false,
				'error' 	=> 'Unable to process your request at this time, please try again shortly.'
			];
		}

		wp_send_json($response);
		die();
	}

	/**
	 * handle_dashboard_forms_request
	 *
	 * @return void
	 */
	public function handle_dashboard_forms_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		try {
			$query_results = FormSubmission::read_top_5_forms_all_time();

			$response = ['success' => true, 'results' => $query_results];
		} catch (Exception $e) {
			error_log($e->getMessage());
			$response = [
				'success' 	=> false,
				'error' 	=> 'Unable to process your request at this time, please try again shortly.'
			];
		}

		wp_send_json($response);
		die();
	}

	/**
	 * handle_reports_submissions_request
	 *
	 * @return void
	 */
	public function handle_reports_submissions_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
			$response = ['success' => false, 'error' => 'The dates you had selected are not valid.'];
			wp_send_json($response);
			die();
		}

		$start_date = sanitize_text_field($_POST['start_date']);
		$end_date 	= sanitize_text_field($_POST['end_date']);

		// Validation...
		if ($start_date > $end_date) {
			$response = ['success' => false, 'error' => 'Your start date must be before or the same as your end date.'];
			wp_send_json($response);
			die();
		}

		try {
			$query_results = FormSubmission::read_form_submissions_between_dates($start_date, $end_date);
			$results = $query_results['results'];
			$pagination = $query_results['pagination'];

			foreach ($results as $result) {
				// TODO: Fix issue where meta maybe serialized more than once
				$result->meta = maybe_unserialize($result->meta);
				$result->meta = maybe_unserialize($result->meta);
			}

			$response = ['success' => true, 'pagination' => $pagination, 'results' => $results];
		} catch (Exception $e) {
			error_log($e->getMessage());
			$response = [
				'success' 	=> false,
				'error' 	=> 'Unable to process your request at this time, please try again shortly.'
			];
		}

		wp_send_json($response);
		die();
	}

	/**
	 * handle_reports_list_submissions_request
	 *
	 * @return void
	 */
	public function handle_reports_list_submissions_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (!isset($_POST['form_id'])) {
			$response = ['success' => false, 'error' => 'Invalid request.'];
			wp_send_json($response);
			die();
		}

		$start_date = sanitize_text_field($_POST['start_date']);
		$end_date 	= sanitize_text_field($_POST['end_date']);

		$results = [];
		$pagination = [];

		try {
			$query_results = FormSubmission::read_form_submissions_by_formid(intval($_POST['form_id']), $start_date, $end_date, 100);
			$results = $query_results['results'];
			$pagination = $query_results['pagination'];

			// Update the results
			foreach ($results as $result) {
				// TODO: Fix issue where meta maybe serialized more than once
				$result->meta = maybe_unserialize($result->meta);
				$result->meta = maybe_unserialize($result->meta);

				$type_id = intval($result->meta['type_id']);
				$template_id = intval($result->meta['template_id']);
				$type_name = $this->get_form_type_name($type_id);

				$result->meta['location'] = [$type_name];
				if ($type_id === 1) {	// inline
					$result->meta['location'] = [$type_name, $result->meta['page_location'], $result->meta['page_location_value']];
				} else if ($type_id === 2) {	// floating_box
					$result->meta['location'] = [$type_name, $result->meta['form_location']];
				}
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		$response = ['success' => true, 'results' => $results, 'pagination' => $pagination];
		wp_send_json($response);
		die();
	}

	/**
	 * handle_reports_top_5_forms_request
	 *
	 * @return void
	 */
	public function handle_reports_top_5_forms_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		$start_date = sanitize_text_field($_POST['start_date']);
		$end_date 	= sanitize_text_field($_POST['end_date']);

		$response = ['success' => true, 'results' => []];
		try {
			$response['results'] = FormSubmission::read_top_5_forms($start_date, $end_date);
		} catch (Exception $e) {
			error_log($e->getMessage());
			$response['success'] = false;
			$response['error'] = 'Unable to process your request at this time, please try again shortly.';
		}
		wp_send_json($response);
		die();
	}


	/**
	 * handle_reports_top_5_pages_request
	 *
	 * @return void
	 */
	public function handle_reports_top_5_pages_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		$start_date = sanitize_text_field($_POST['start_date']);
		$end_date 	= sanitize_text_field($_POST['end_date']);

		$response = ['success' => true, 'results' => []];
		try {
			$response['results'] = FormSubmission::read_top_5_pages($start_date, $end_date);
		} catch (Exception $e) {
			error_log($e->getMessage());
			$response['success'] = false;
			$response['error'] = 'Unable to process your request at this time, please try again shortly.';
		}
		wp_send_json($response);
		die();
	}

	/**
	 * handle_reports_list_forms_request
	 *
	 * @return void
	 */
	public function handle_reports_list_forms_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		$results = [];
		$pagination = [];
		try {
			//date_default_timezone_set('America/Los_Angeles');

			// Get a list of all my forms
			$query_results = Form::read_forms();
			$results = $query_results['results'];
			$pagination = $query_results['pagination'];

			if (is_array($results)) {
				foreach ($results as &$result) {
					$result = (array)$result;

					$result['header'] = empty($result['header']) ? 'No Title' : $result['header'];
					$type_name = $this->get_form_type_name($result['type_id']);
					$template_name = $this->get_form_template_name($result['template_id']);

					$result['type_name'] = $type_name;
					$result['template_name'] = $template_name;

					// Get the count of how many submissions for this form if any
					$result['count'] = FormSubmission::get_form_submission_count($result['id'])->totalSubmissions;
				}
			}

			// Sort the results by the column count in descending order
			$count = array_column($results, 'count');
			array_multisort($count, SORT_DESC, $results);
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		$response = ['success' => true, 'results' => $results, 'pagination' => $pagination];
		wp_send_json($response);
		die();
	}

	/**
	 * handle_reports_list_emails_request
	 *
	 * @return array
	 */
	public function handle_reports_list_emails_request()
	{
		$this->verify_nonce($_POST['nonce'], $this->nonce);

		if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
			$response = ['success' => false, 'error' => 'Invalid request.'];
			wp_send_json($response);
			die();
		}

		$results = [];
		$pagination = [];
		$file_url = '';

		try {
			$start_date = sanitize_text_field($_POST['start_date']);
			$end_date 	= sanitize_text_field($_POST['end_date']);
			$filter 	 = sanitize_text_field($_POST['filter']);
			$page 		= intval($_POST['page']);
			$limit 		= !empty($_POST['limit']) ? intval($_POST['limit']) : 100;
			$format 	= !empty($_POST['format']) ? sanitize_text_field($_POST['format']) : '';

			$query_results = FormSubmission::read_emails($start_date, $end_date, $filter, $limit, $page);
			$results = $query_results['results'];
			$pagination = $query_results['pagination'];

			if ($format !== 'csv') {
				// Update the results
				foreach ($results as $result) {
					// TODO: Fix issue where meta maybe serialized more than once
					$result->meta = maybe_unserialize($result->meta);
					$result->meta = maybe_unserialize($result->meta);
				}
			} else {
				if (!empty($results)) {
					$file_name = 'exported_emails.csv';
					$uploads = wp_upload_dir();
					$file_path = $uploads['path'] . '/' . $file_name;
					$file_url = $uploads['url'] . '/' . $file_name;

					// Open a file handler
					$f = fopen($file_path, 'w');
					if ($f === false) {
						$file_url = '';
					} else {
						fputcsv($f, ['Email', 'Sent', 'Date']);

						// Output each row of data
						foreach ($results as $row) {
							fputcsv($f, ['email' => $row->email, 'sent' => ($row->failed ? 'no' : 'yes'), 'date' => $row->date]);
						}

						// Close the file
						fclose($f);
					}

					$results = [];
				}
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		$response = ['success' => true, 'pagination' => $pagination, 'results' => $results, 'file_url' => $file_url];
		wp_send_json($response);
		die();
	}

	/**
	 * generate_csv
	 * Generates a CSV file from the specified result set
	 * 
	 * @param  mixed $results
	 * @param  string $file_path
	 * @return bool
	 */
	private function generate_csv($results, $file_path)
	{
		if (empty($results)) {
			return false;
		}

		// Open a file handle
		$f = fopen($file_path, 'w');
		if ($f === false) {
			return false;
		}

		// Output header row (if results are not empty)
		$keys = (array)$results[0];
		fputcsv($f, array_keys($keys));

		// Output each row of data
		foreach ($results as $row) {
			$newRow = (array)$row;
			fputcsv($f, $newRow);
		}

		// Close the file
		fclose($f);

		return true;
	}
}
