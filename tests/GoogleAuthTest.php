<?php

class GoogleAuthTest extends TestCase {

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethod(&$object, $methodName, array $parameters = array())
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	public function test_secret_generation()
	{
		$googleAuth = new \Lumby\GoogleAuthenticator\GoogleAuthenticator();

		$secret = $googleAuth->generateSecret();

		$this->assertSame(strlen($secret), 16);
	}

	public function test_qr_url()
	{
		$googleAuth = new \Lumby\GoogleAuthenticator\GoogleAuthenticator();

		$secret = $googleAuth->generateSecret();
		$name = "Tester";
		$issuer = "Test Co.";

		$qrUrl = $googleAuth->generateQrUrl($name, $issuer);

		$match = urlencode('otpauth://totp/' . $issuer . ':' . $name . '?secret=' . $secret . '&issuer=' . $issuer);

		$this->assertSame($qrUrl, $match);
	}

	public function test_google_qr_url()
	{
		$googleAuth = new \Lumby\GoogleAuthenticator\GoogleAuthenticator();

		$secret = $googleAuth->generateSecret();
		$name = "Tester";
		$issuer = "Test Co.";
		$size = 300;

		$qrUrl = $googleAuth->generateGoogleChartQr($name, $issuer, $size);

		$urlencoded = urlencode('otpauth://totp/' . $issuer . ':' . $name . '?secret=' . $secret . '&issuer=' . $issuer);
		$match = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&chld=M|0&cht=qr&chl=' . $urlencoded . '';

		$this->assertSame($qrUrl, $match);
	}

	public function test_secret_verification()
	{
		$googleAuth = new \Lumby\GoogleAuthenticator\GoogleAuthenticator();

		$secret = $googleAuth->generateSecret();
		$code = $this->invokeMethod($googleAuth, 'generateCode', array($secret));

		$validation = $googleAuth->verifyCode($code);

		$this->assertTrue($validation);

	}

}