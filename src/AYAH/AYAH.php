<?php

namespace AYAH;


class AYAH
{
	/**
	 * The developer publisher key.
	 * @var string
	 */
	protected $publisherKey;
	
	/**
	 * The developer scoring key.
	 * @var string
	 */
	protected $scoringKey;
	
	/**
	 * The webservice url to use in validating AYAH requests.
	 * @var string
	 */
	protected $webService = 'ws.areyouahuman.com';
	
	/**
	 * A session secret to be used in determining which AYAH request
	 * the library is dealing with.
	 * @var string
	 */
	protected $sessionSecret;
	
	/**
	 * Constructs a new AYAH instance and grabs the session secret if it exists.
	 * @param string $publisherKey
	 * @param string $scoringKey
	 * @param string $webServiceHost
	 * @throws InvalidArgumentException
	 */
	public function __construct($publisherKey, $scoringKey, $webServiceHost = 'ws.areyouahuman.com')
	{
		if(array_key_exists('sessionSecret', $_REQUEST)) {
			$this->sessionSecret = $_REQUEST['sessionSecret'];
		}
	
		// Throw an exception if the appropriate information has not been provided.
		if (!is_string($publisherKey) || !strlen($publisherKey) > 1) {
			throw new InvalidArgumentException('AYAH publisher key is not defined.');
		}
	
		if (!is_string($scoringKey) || !strlen($scoringKey) > 1) {
			throw new InvalidArgumentException('AYAH scoring key is not defined.');
		}
	
		if (!is_string($webServiceHost) || !strlen($webServiceHost) > 1) {
			throw new InvalidArgumentException('AYAH web service host is not defined.');
		}
		
		$this->publisherKey = $publisherKey;
		$this->scoringKey = $scoringKey;
		$this->webService = $webServiceHost;
	}
	
	/**
	 * Sets the session secret.
	 * @param string $secret
	 * @return void
	 */
	public function setSessionSecret($secret)
	{
		$this->sessionSecret = $secret;
	}
	
	/**
	 * Returns the markup for PlayThru.
	 * @return string
	 */
	public function getPublisherHTML()
	{
		$url = 'https://' . $this->webService . "/ws/script/" . urlencode($this->publisherKey);
	
		return "<div id='AYAH'></div><script src='". $url ."' type='text/javascript' language='JavaScript'></script>";
	}
	
	/**
	 * Check whether the user is a human. Wrapper for the scoreGame API call
	 * @return boolean
	 */
	public function scoreResult()
	{
		$result = false;
		
		if($this->sessionSecret)
		{
			$fields = array('sessionSecret' 	=> urlencode($this->sessionSecret),
							'scoring_key' 		=> $this->scoringKey);
			
			$resp = $this->doHttpsPostReturnJSONArray($this->webService, '/ws/scoreGame', $fields);
			
			if($resp) {
				$result = ($resp->status_code == 1);
			}
		}
	
		return $result;
	}
	
	/**
	 * Records a conversion
	 * Called on the goal page that A and B redirect to
	 * A/B Testing Specific Function
	 * @return boolean
	 */
	public function recordConversion()
	{
		if(isset($this->sessionSecret))
		{
			return '<iframe style="border: none;" height="0" width="0" src="https://' .
					$this->webService . '/ws/recordConversion/'.
					urlencode($this->sessionSecret) . '"></iframe>';
		}
		else
		{
			error_log('AYAH::recordConversion - AYAH Conversion Error: No Session Secret');
			return false;
		}
	}
	
	/**
	 * Do an HTTPS POST, return some JSON decoded as array.
	 * @param $host hostname
	 * @param $path path
	 * @param $fields associative array of fields
	 * return array Decoded json structure or empty data structure
	 */
	protected function doHttpsPostReturnJSONArray($hostname, $path, $fields)
	{
		$result = $this->doHttpsPost($hostname, $path, $fields);
	
		if ($result) {
			$result = json_decode($result);
		} else {
			error_log("AYAH::doHttpsPostGetJSON: Post to https://$hostname$path returned no result.");
			$result = array();
		}
	
		return $result;
	}
	
	/**
	 * Initiate a request to the AYAH web service through curl or using a socket
	 * if the system does not have curl available.
	 * @param string $hostname
	 * @param string $path
	 * @param array $fields
	 * @return Ambigous <string, mixed>
	 */
	protected function doHttpsPost($hostname, $path, $fields)
	{
		$result = '';
		$fields_string = '';
		
		//Url encode the string
		foreach($fields as $key => $value)
		{
			if (is_array($value)) {
				foreach ($value as $v) {
					$fields_string .= $key . '[]=' . $v . '&';
				}
			} else {
				$fields_string .= $key.'='.$value.'&';
			}
		}
		
		rtrim($fields_string,'&');
	
		// cURL or something else
		if(function_exists('curl_init'))
		{
			$curlsession = curl_init();
			curl_setopt($curlsession, CURLOPT_URL, 'https://' . $hostname . $path);
			curl_setopt($curlsession, CURLOPT_POST,count($fields));
			curl_setopt($curlsession, CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($curlsession, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curlsession, CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($curlsession, CURLOPT_SSL_VERIFYPEER,false);
			
			$result = curl_exec($curlsession);
		}
		else
		{
			error_log("No cURL support.");
	
			// Build a header
			$http_request  = "POST $path HTTP/1.1\r\n";
			$http_request .= "Host: $hostname\r\n";
			$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
			$http_request .= "Content-Length: " . strlen($fields_string) . "\r\n";
			$http_request .= "User-Agent: AreYouAHuman/PHP 1.0.2\r\n";
			$http_request .= "Connection: Close\r\n";
			$http_request .= "\r\n";
			$http_request .= $fields_string ."\r\n";
	
			$result = '';
			$errno = $errstr = "";
			$fs = fsockopen("ssl://" . $hostname, 443, $errno, $errstr, 10);
			if( false == $fs ) {
				error_log('Could not open socket');
			} else {
				fwrite($fs, $http_request);
				while (!feof($fs)) {
					$result .= fgets($fs, 4096);
				}
	
				$result = explode("\r\n\r\n", $result, 2);
				$result = $result[1];
			}
		}
	
		return $result;
	}
}