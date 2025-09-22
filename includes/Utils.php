<?php

/**
 * @package Optin_Buddy
 */

namespace Inc;

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Utils
{

	/**
	 * send_mail
	 * Sends an email using wp_mail with default headers
	 *
	 * @param  string $to
	 * @param  string $title
	 * @param  string $message
	 * @param  array $additionalHeaders
	 * @return boolean
	 */
	public static function send_mail($to, $title, $message, $additionalHeaders = [])
	{
		$from 		= get_option('admin_email');
		$blog_name 	= get_bloginfo('name');
		$blog_name 	= str_replace("&#039;", "'", $blog_name);
		$blog_url 	= get_site_url();
		$domain 	= str_replace('www.', '', parse_url($blog_url));
		$defaultHeaders = [
			'Content-Type' => 'text/html; charset=UTF-8',
			'From' => $blog_name . ' <noreply@' . $domain['host'] . '>',
			'Reply-To' => $blog_name . ' <' . $from . '>'
		];

		$merged = array_merge($defaultHeaders, $additionalHeaders);
		$headers = [];
		foreach ($merged as $key => $value) {
			$headers[] = "\"$key: $value\"";
		}

		return wp_mail($to, $title, $message, $headers);
	}

	/**
	 * get_file_contents
	 * Loads the contents of the specified file
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function get_file_contents($path)
	{
		if (file_exists($path)) {
			$content = file_get_contents($path);
			return $content;
		}

		return '';
	}

	/**
	 * sanitize_date
	 * Sanitizes a date string to ensure it is in the correct format (YYYY-MM-DD). 
	 * If not, returns the current date or a specified default format.
	 *
	 * @param  string $date
	 * @param  string $default_format
	 * @return string
	 */
	public static function sanitize_date($date, $default_format = 'Y-m-d')
	{
		preg_match('/^\d{4}-\d{2}-\d{2}$/', $date, $matches);
		if (count($matches) === 0) {
			return !empty($default_format) ? date($default_format) : '';
		}
		return $date;
	}
}
