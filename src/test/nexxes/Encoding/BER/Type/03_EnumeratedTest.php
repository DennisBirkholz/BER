<?php
/* $Id: Type.interface.php 17 2012-01-10 07:27:03Z dennis $
 * $URL: https://svn.aachen.birkholz.biz/nexxes-php/BasicEncodingRules/trunk/src/nexxes/encoding/Type.interface.php $
 * $Copyright$ */

namespace test\nexxes\Encoding\BER\Type;

use nexxes\Encoding\BER;
use nexxes\Encoding\BER\Type\Enumerated;

class EnumeratedMock extends Enumerated {
	public static $choices = array(
		0	=> 'choice1',
		1	=> 'choice2',
		2	=> 'choice3',
	);
}

class EnumeratedTest extends \PHPUnit_Framework_TestCase {
	public function testValidChoices() {
		$mock = new EnumeratedMock();
		
		for ($i=0; $i<count(EnumeratedMock::$choices); $i++) {
			$mock->init($i);
			
			$this->assertEquals($mock->value(), EnumeratedMock::$choices[$i]);
			$this->assertEquals($mock->value(true), $i);
			
			$mock->init(EnumeratedMock::$choices[$i]);
			
			$this->assertEquals($mock->value(), EnumeratedMock::$choices[$i]);
			$this->assertEquals($mock->value(true), $i);
		}
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testIndexUnderflow() {
		$mock = new EnumeratedMock();
		$mock->init(-1);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testIndexOverflow() {
		$mock = new EnumeratedMock();
		$mock->init(count(EnumeratedMock::$choices));
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testIllegalChoice() {
		$mock = new EnumeratedMock();
		$mock->init('foobar');
	}
}

?>