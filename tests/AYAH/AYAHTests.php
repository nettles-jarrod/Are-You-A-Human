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
		$this->ayah = new AYAH('YOURPUBLISHERKEY', 'YOURSCORINGKEY');
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
	
	public function testRecordConversion()
	{
		$this->ayah->setSessionSecret('testingsecret');
		$retval = $this->ayah->recordConversion();
		
		$this->assertEquals('<iframe style="border: none;" height="0" width="0" src="https://ws.areyouahuman.com/ws/recordConversion/testingsecret"></iframe>', $retval);
		
		$this->ayah->setSessionSecret(null);
	}
	
	public function testScoreResult()
	{
		$this->ayah->setSessionSecret('testingsecret');
		
		$this->assertFalse($this->ayah->scoreResult());
		
		$this->ayah->setSessionSecret(null);
	}
}






