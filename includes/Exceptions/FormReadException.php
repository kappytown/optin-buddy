<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Exceptions;

use \Exception;

// Exit if accessed directly.
defined('ABSPATH') || exit;

class FormReadException extends BaseException
{
	private $defaultMessage = 'Unable to retrieve this form, please try again.';

	// You can override the constructor if you want to add additional functionality
	public function __construct($message = null, $vars = null, $code = 0, $previous = null)
	{
		if (empty($message)) {
			$message = $this->defaultMessage;
		}

		$this->logStackTrace = false;
		$this->doNotLog = true;

		return parent::__construct($message, $vars, $code, $previous);
	}
}
