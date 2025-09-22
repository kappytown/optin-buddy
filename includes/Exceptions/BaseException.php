<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Exceptions;

use \Exception;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * APIException
 */
class BaseException extends Exception implements iException
{
	protected 	$message		= 'Unknown exception';	// Exception message
	protected 	$code    		= 200;					// User-defined HTTP status code
	protected 	$file; 									// Source filename of exception
	protected 	$line;									// Source line of exception
	protected 	$doNotLog 		= false;				// Set to true if you want to prevent logging all together
	protected 	$logStackTrace 	= true;					// Set to false to prevent logging of stack trace
	protected 	$vars 			= null;					// array of variables to append to message
	protected 	$logMessage		= '';					// Message to log
	protected 	$errMessage 	= '';					// Error message to log

	private 	$defaultMessage = 'Unknown Error';		// Default message if message is empty
	private   	$trace; 								// Unknown
	private   	$string;								// Unknown

	/**
	 *
	 *
	 * @param string 	message
	 * @param int 		code
	 * @param array 	vars
	 * @param throwable previous
	 */
	public function __construct($message = null, $vars = null, $code = 200, $previous = null)
	{
		if (empty($message)) $message = $this->defaultMessage;

		$this->vars = $vars;

		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);

		// Allows for chaining
		return $this;
	}

	/**
	 * get_vars
	 *
	 * @return array
	 */
	public function get_vars()
	{
		return $this->vars;
	}

	/**
	 * getLogMessage
	 *
	 * @return string
	 */
	public function getLogMessage()
	{
		return $this->logMessage;
	}

	/**
	 * getErrMessage
	 *
	 * @return string
	 */
	public function getErrMessage()
	{
		return $this->errMessage;
	}

	/**
	 * setLogMessage
	 *
	 * @param  string $message
	 * @return object
	 */
	public function setLogMessage($message)
	{
		$this->logMessage = $message;
		return $this;
	}

	/**
	 * setErrMessage
	 *
	 * @param  string $message
	 * @return object
	 */
	public function setErrMessage($message)
	{
		$this->errMessage = $message;
		return $this;
	}

	/**
	 * getDoNotLog
	 *
	 * @return bool
	 */
	public function getDoNotLog()
	{
		return $this->doNotLog;
	}

	/**
	 * setLogStackTrace
	 *
	 * @param  string $logStackTrace
	 * @return string
	 */
	public function setLogStackTrace($logStackTrace)
	{
		$this->logStackTrace = $logStackTrace;
		return $this;
	}

	/**
	 * __toString
	 *
	 * Returns a string representation of this object
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$message = get_class($this) . " '{$this->message}' in {$this->file} ({$this->line}) {$this->code}";
		if (!empty($this->errMessage)) {
			$message .= "\nERROR: {$this->errMessage}";
		}
		
		if ($this->logStackTrace) {
			return $message . "\n{$this->getTraceAsString()}";
		} else {
			return $message;
		}
	}

	/**
	 * getClass
	 * 
	 * Returns the short name of the exception class
	 *
	 * @return string
	 */
	public function getClass()
	{
		return (new \ReflectionClass($this))->getShortName();
	}
}
