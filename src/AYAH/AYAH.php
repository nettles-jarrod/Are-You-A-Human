<?php

namespace AYAH;

class AYAH
{
	protected $ayah_publisher_key;
	protected $ayah_scoring_key;
	protected $ayah_web_service_host = 'ws.areyouahuman.com';
	protected $session_secret;
	
	/**
	 * Constructs a new AYAH instance and grabs the session secret if it exists.
	 * @param string $publisherKey
	 * @param string $scoringKey
	 * @param string $webServiceHost
	 * @throws InvalidArgumentException
	 */
	public function __construct($publisherKey = '', $scoringKey = '', $webServiceHost = 'ws.areyouahuman.com')
	{
		if(array_key_exists('session_secret', $_REQUEST)) {
			$this->session_secret = $_REQUEST['session_secret'];
		}
		
		$this->ayah_publisher_key = $publisherKey;
		$this->ayah_scoring_key = $scoringKey;
		$this->ayah_web_service_host = $webServiceHost;
	
		// If the constants exist, override with those
		if (defined('AYAH_PUBLISHER_KEY')) {
			$this->ayah_publisher_key = AYAH_PUBLISHER_KEY;
		}
	
		if (defined('AYAH_SCORING_KEY')) {
			$this->ayah_scoring_key = AYAH_SCORING_KEY;
		}
	
		if (defined('AYAH_WEB_SERVICE_HOST')) {
			$this->ayah_web_service_host = AYAH_WEB_SERVICE_HOST;
		}
	
		// Throw an exception if the appropriate information has not been provided.
		if ($this->ayah_publisher_key == '') {
			throw new InvalidArgumentException('AYAH publisher key is not defined.');
		}
	
		if ($this->ayah_scoring_key == '') {
			throw new InvalidArgumentException('AYAH scoring key is not defined.');
		}
	
		if ($this->ayah_web_service_host == '') {
			throw new InvalidArgumentException('AYAH web service host is not defined.');
		}
	}
	
	/**
	 * Returns the markup for PlayThru.
	 * 
	 * @return string
	 */
	public function getPublisherHTML()
	{
		$url = 'https://' . $this->ayah_web_service_host . "/ws/script/" . urlencode($this->ayah_publisher_key);
	
		return "<div id='AYAH'></div><script src='". $url ."' type='text/javascript' language='JavaScript'></script>";
	}
	
	/**
	 * Check whether the user is a human
	 * Wrapper for the scoreGame API call
	 *
	 * @return boolean
	 */
	public function scoreResult()
	{
		$result = false;
		
		if($this->session_secret)
		{
			$fields = array('session_secret' 	=> urlencode($this->session_secret),
							'scoring_key' 		=> $this->ayah_scoring_key);
			
			$resp = $this->doHttpsPostReturnJSONArray($this->ayah_web_service_host, '/ws/scoreGame', $fields);
			
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
	 *
	 * @return boolean
	 */
	public function recordConversion()
	{
		if(isset($this->session_secret))
		{
			return '<iframe style="border: none;" height="0" width="0" src="https://' .
					$this->ayah_web_service_host . '/ws/recordConversion/'.
					urlencode($this->session_secret) . '"></iframe>';
		}
		else
		{
			error_log('AYAH::recordConversion - AYAH Conversion Error: No Session Secret');
			return false;
		}
	}
	
	/**
	 * Do a HTTPS POST, return some JSON decoded as array (Internal function)
	 * @param $host hostname
	 * @param $path path
	 * @param $fields associative array of fields
	 * return JSON decoded data structure or empty data structure
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
	 * 
	 * @param unknown_type $hostname
	 * @param unknown_type $path
	 * @param unknown_type $fields
	 * @return Ambigous <string, mixed>
	 */
	protected function doHttpsPost($hostname, $path, $fields)
	{
		$result = "";
		// URLencode the post string
		$fields_string = "";
		
		foreach($fields as $key=>$value)
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