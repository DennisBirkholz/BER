<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace test\org\birkholz\Encoding\BER\Type;
use org\birkholz\Encoding\BER\Type\BitString;

use org\birkholz\Encoding\BER; 

class BitStringTest extends \PHPUnit_Framework_TestCase {
	public function testBitString1Bit() {
		$this->helper(1);
	}
	
	public function testBitString2Bit() {
		$this->helper(2);
	}
	
	public function testBitString3Bit() {
		$this->helper(3);
	}
	
	public function testBitString4Bit() {
		$this->helper(4);
	}
	
	public function testBitString5Bit() {
		$this->helper(5);
	}
	
	public function testBitString6Bit() {
		$this->helper(6);
	}
	
	public function testBitString7Bit() {
		$this->helper(7);
	}
	
	public function testBitString8Bit() {
		$this->helper(8);
	}
	
	public function testBitString9Bit() {
		$this->helper(9);
	}
	
	public function testBitString10Bit() {
		$this->helper(10);
	}
	
	protected function helper($bits) {
		$max = (0x01 << $bits) - 1;
		
		for ($i=0; $i<=$max; $i++) {
			$values = $this->parseValueToArray($i, $bits);
			$parsed = $this->encodeValuefromArray($values, $bits);
			
			$t = new BitString();
			$t->init($values);
			$encoded = $t->encode();
			
			$this->assertEquals($encoded, $parsed, 'Encoding mismatch: is ' . TestHelper::str2hex($encoded) . ', should ' . TestHelper::str2hex($parsed));
			
			$t = new BitString();
			$t->parse($parsed, 2);
			$this->assertEquals($values, $t->value(), 'Decoding mismatch: is ' . TestHelper::arr2bin($t->value()) . ', should ' . TestHelper::arr2bin($values));
		}
	}
	
	protected function parseValueToArray($value, $bits) {
		$r = array();
		$m = 0x01;
		
		for ($i=0; $i<$bits; $i++) {
			$r[$i] = (boolean)($value & $m);
			$m <<= 1;
		}
		
		$s = '';
		for ($i=count($r)-1; $i>=0; $i--) {
			$s .= ($r[$i] ? '1' : '0');
		}
		
		return $r;
	}
	
	protected function encodeValuefromArray($values, $bits) {
		$rest = (($bits % 8) ? 8 - ($bits % 8) : 0);
		$rounds = ceil($bits / 8);
		$return = chr($rest);
		
		for ($round=1; $round<=$rounds; $round++) {
			$byte = 0;
			
			for ($pos=7; $pos>=0; $pos--) {
				// Abort
				if (($round == $rounds) && ($rest > 0) && ($pos < $rest)) { break; }
				
				$bool = array_shift($values);
				
				if ($bool) {
					$byte += (0x01<<$pos);
				}
			}
			
			$return .= chr($byte);
		}
		
		return chr(0x03) . chr($rounds+1) . $return;
	}
}
