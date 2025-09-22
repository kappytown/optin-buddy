<?php

/**
 * @package Optin_Buddy
 */

namespace Inc\Classes;

use Inc\Classes\Providers\ActiveCampaignProvider;
use Inc\Classes\Providers\AWeberProvider;
use Inc\Classes\Providers\BrevoProvider;
use Inc\Classes\Providers\ConstantContactProvider;
use Inc\Classes\Providers\ConvertKitProvider;
use Inc\Classes\Providers\DripProvider;
use Inc\Classes\Providers\FlodeskProvider;
use Inc\Classes\Providers\GetResponseProvider;
use Inc\Classes\Providers\KeapProvider;
use Inc\Classes\Providers\MailChimpProvider;
use Inc\Classes\Providers\MailerLiteProvider;
use Inc\Classes\Providers\SendGridProvider;
use Inc\Classes\Providers\WordPressProvider;
use Inc\Classes\Providers\UnknownProvider;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * EmailProviderFactory
 */
class EmailProviderFactory
{
	/**
	 * create_provider
	 * Returns an instanticated instance of the specified provider
	 * 
	 * @param  mixed $provider
	 * @return object
	 */
	public static function create_provider($provider)
	{
		$Provider = self::get_provider($provider);
		return new $Provider();
	}

	/**
	 * get_provider
	 * Returns the classname of the specified provider 
	 * 
	 * @param  mixed $provider
	 * @return object
	 */
	public static function get_provider($provider)
	{
		switch ($provider) {
			case 'activecampaign':
				return ActiveCampaignProvider::class;
			case 'aweber':
				return AWeberProvider::class;
			case 'brevo':
				return BrevoProvider::class;
			case 'constant_contact':
				return ConstantContactProvider::class;
			case 'convertkit':
				return ConvertKitProvider::class;
			case 'drip':
				return DripProvider::class;
			case 'flodesk':
				return FlodeskProvider::class;
			case 'getresponse':
				return GetResponseProvider::class;
			case 'keap':
				return KeapProvider::class;
			case 'mailchimp':
				return MailChimpProvider::class;
			case 'mailerlite':
				return MailerLiteProvider::class;
			case 'sendgrid':
				return SendGridProvider::class;
			case 'wordpress':
				return WordPressProvider::class;

			default:
				return UnknownProvider::class;
		}
	}
}
