<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Exceptions;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * 
 */
interface iException
{
	public function getMessage();				// Friendly exception message
	public function getCode();					// User-defined Exception code
	public function getFile();					// Source filename
	public function getLine();					// Source line
	public function getTrace();					// An array of the backtrace()
	public function getTraceAsString();			// Formatted string of trace
	public function get_vars();					// Array of items to pass back in the response
	public function getLogMessage();			// Message to log
	public function getErrMessage();			// Error message to log
	public function setLogMessage($message);
	public function setErrMessage($message);
	public function __toString(); 				// formatted string for display

	public function __construct($message = null, $vars = null, $code = 200, $previous = null);
}
