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
    $ayah = $this->getAyahMock(array('doHttpsPostReturnJSONArray'));

    $return = new \stdClass();
    $return->session_secret = 'sessionSecret';

    $ayah->expects($this->once())
         ->method('doHttpsPostReturnJSONArray')
         ->will($this->returnValue($return));

		$publisherHtml = $ayah->getPublisherHTML();
		
		$this->assertEquals("<div id='AYAH'></div><script src='https://ws.areyouahuman.com/ws/script/YOURPUBLISHERKEY/$return->session_secret' type='text/javascript' language='JavaScript'></script>", 
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
		
		$this->assertEquals('<iframe style="border: none;" height="0" width="0" src="https://ws.areyouahuman.com/ws/recordConversion/YOURPUBLISHERKEY"></iframe>', $retval);
		
		$this->ayah->setSessionSecret(null);
	}
	
	public function testScoreResult()
	{
		$this->ayah->setSessionSecret('testingsecret');
		
		$this->assertFalse($this->ayah->scoreResult());
		
		$this->ayah->setSessionSecret(null);
	}

  protected function getAyahMock(array $mockMethods = null)
  {
    return $this->getMockBuilder('AYAH\AYAH')
                ->setConstructorArgs(array('YOURPUBLISHERKEY', 'YOURSCORINGKEY'))
                ->setMethods($mockMethods)
                ->getMock();
  }
}






