<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * EmailProvider
 */
class EmailProvider
{
	/**
	 * provider
	 *
	 * @var mixed
	 */
	private $provider;

	/**
	 * __construct
	 *
	 * @param  mixed $providerName
	 * @return void
	 */
	public function __construct($providerName)
	{
		$this->provider = EmailProviderFactory::create_provider($providerName);
	}

	/**
	 * send_email
	 *
	 * @param  mixed $email
	 * @return void
	 */
	public function send_email($email, $name, $post_meta, $overrides = [])
	{
		return $this->provider->send_email($email, $name, $post_meta, $overrides);
	}

	/**
	 * get_provider
	 *
	 * @return object
	 */
	public function get_provider()
	{
		return $this->provider;
	}

	/**
	 * get_details
	 *
	 * @return string
	 */
	public function get_details()
	{
		return $this->provider->get_details();
	}
}
