<?php namespace Lumby\GoogleAuthenticator;

/**
 * PHP Class for handling Google Authenticator 2-factor authentication
 *
 * @author    Michael Kliewe
 * @copyright 2012 Michael Kliewe
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link      http://www.phpgangsta.de/
 *
 *
 * Copyright 2014 Oliver Lumby
 *
 * - Added options for issuer in QRCode URL generator.
 * - Renamed some methods for better readability.
 *
 */

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class GoogleAuthenticatorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 *
	 */
	public function boot()
	{
		$this->package('lumby/google-authenticator');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
