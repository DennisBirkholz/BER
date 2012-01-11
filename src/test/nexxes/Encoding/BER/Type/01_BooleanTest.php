<?php
/* $Id: Type.interface.php 17 2012-01-10 07:27:03Z dennis $
 * $URL: https://svn.aachen.birkholz.biz/nexxes-php/BasicEncodingRules/trunk/src/nexxes/encoding/Type.interface.php $
 * $Copyright$ */

namespace test\nexxes\Encoding\BER\Type;

use nexxes\Encoding\BER\Type\Boolean;

class BooleanTest extends \PHPUnit_Framework_TestCase {
	
	public function testInitTrue() {
		$bool = new Boolean();
		$bool->init(true);
		$this->assertEquals(ord($bool->encode()), 0xFF);
	}
	
	public function testInitFalse() {
		$bool = new Boolean();
		$bool->init(false);
		$this->assertEquals(ord($bool->encode()), 0x00);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testInitIllegal() {
		$bool = new Boolean();
		$bool->init("invalid");
	}
	
	public function testParseTrue() {
		$data = chr(0xFF);
		
		$bool = new Boolean();
		$bool->parse($data, 0, 1);
		$this->assertTrue($bool->value());
	}
	
	public function testParseFalse() {
		$data = chr(0x00);
		
		$bool = new Boolean();
		$bool->parse($data, 0, 1);
		$this->assertFalse($bool->value());
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testParseIllegal() {
		$data = "invalid";
		
		$bool = new Boolean();
		$bool->parse($data, 0, strlen($data));
	}
}


?>