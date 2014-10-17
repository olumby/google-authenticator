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

use Lumby\GoogleAuthenticator\Base32;

class GoogleAuthenticator {

	/**
	 * The length of generated codes
	 *
	 * @var int
	 */
	protected $codeLength;

	/**
	 * The users Google Auth secret
	 *
	 * @var string
	 */
	protected $authSecret;

	/**
	 * @param string $authSecret
	 * @param int    $codeLength
	 */
	function __construct($authSecret = '', $codeLength = 6)
	{
		$this->authSecret = $authSecret;
		$this->codeLength = $codeLength;
	}

	/**
	 * Generate a new secret
	 *
	 * @param int $secretLength
	 * @return string
	 */
	public function generateSecret($secretLength = 16)
	{
		$validChars = (new Base32)->_getBase32LookupTable();
		unset($validChars[32]);

		$secret = '';
		for ($i = 0; $i < $secretLength; $i ++)
		{
			$secret .= $validChars[array_rand($validChars)];
		}

		$this->authSecret = $secret;

		return $secret;
	}

	/**
	 * Get QR code image using Google Charts
	 *
	 * @param string $name
	 * @param string $issuer
	 * @internal param string $secret
	 * @return string
	 */
	public function generateGoogleChartQr($name, $issuer = '')
	{
		$urlencoded = $this->generateQrUrl($name, $issuer);

		return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . $urlencoded . '';
	}

	/**
	 * Get the encoded URL for QR Code
	 *
	 * @param        $name
	 * @param string $issuer
	 * @return string
	 */
	public function generateQrUrl($name, $issuer = '')
	{

		if ($issuer != '')
		{
			$urlencoded = urlencode('otpauth://totp/' . $issuer . ':' . $name . '?secret=' . $this->authSecret . '&issuer=' . $issuer);
		} else
		{
			$urlencoded = urlencode('otpauth://totp/' . $name . '?secret=' . $this->authSecret . '');
		}

		return $urlencoded;
	}

	/**
	 * Verify the provided code
	 *
	 * @param string $code
	 * @param int    $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
	 * @return bool
	 */
	public function verifyCode($code, $discrepancy = 1)
	{
		$currentTimeSlice = floor(time() / 30);

		for ($i = - $discrepancy; $i <= $discrepancy; $i ++)
		{
			$calculatedCode = $this->generateCode($this->secret, $currentTimeSlice + $i);
			if ($calculatedCode == $code)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Set the auth secret
	 *
	 * @param string $authSecret
	 */
	public function setAuthSecret($authSecret)
	{
		$this->authSecret = $authSecret;
	}

	/**
	 * Calculate the code, with given secret and point in time
	 *
	 * @param string   $secret
	 * @param int|null $timeSlice
	 * @return string
	 */
	private function generateCode($secret, $timeSlice = null)
	{
		if ($timeSlice === null)
		{
			$timeSlice = floor(time() / 30);
		}

		$secretkey = $this->_base32Decode($secret);

		// Pack time into binary string
		$time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
		// Hash it with users secret key
		$hm = hash_hmac('SHA1', $time, $secretkey, true);
		// Use last nipple of result as index/offset
		$offset = ord(substr($hm, - 1)) & 0x0F;
		// grab 4 bytes of the result
		$hashpart = substr($hm, $offset, 4);

		// Unpack binary value
		$value = unpack('N', $hashpart);
		$value = $value[1];

		// Only 32 bits
		$value = $value & 0x7FFFFFFF;

		$modulo = pow(10, $this->codeLength);

		return str_pad($value % $modulo, $this->codeLength, '0', STR_PAD_LEFT);
	}

}
