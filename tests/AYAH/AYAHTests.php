<?php

namespace AYAH\Tests;

use AYAH\AYAH;

class AYAHTests extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var AYAH
	 */
	protected $ayah;
	
	protected function setUp()
	{
		$this->ayah = new AYAH('de1f494c7042b842179803483e8d3ab1a55d4bd9', '8f5720c7bc128a09a2dbf06b7cef50dfea0f8193');
	}
	
	public function testGetPublisherHtml()
	{
		$publisherHtml = $this->ayah->getPublisherHTML();
		
		$this->assertEquals("<div id='AYAH'></div><script src='https://ws.areyouahuman.com/ws/script/de1f494c7042b842179803483e8d3ab1a55d4bd9' type='text/javascript' language='JavaScript'></script>", 
							$publisherHtml);
	}
	
	public function testRecordConversion_NoSessionSecret()
	{
		$this->assertFalse($this->ayah->recordConversion());
	}
	
	public function testScoreResult_NoSessionSecret()
	{
		$this->assertFalse($this->ayah->scoreResult());
	}
}

