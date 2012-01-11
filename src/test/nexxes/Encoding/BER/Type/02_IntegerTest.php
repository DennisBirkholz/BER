<?php
/* $Id: Type.interface.php 17 2012-01-10 07:27:03Z dennis $
 * $URL: https://svn.aachen.birkholz.biz/nexxes-php/BasicEncodingRules/trunk/src/nexxes/encoding/Type.interface.php $
 * $Copyright$ */

namespace test\nexxes\Encoding\BER\Type;

use nexxes\Encoding\BER;
use nexxes\Encoding\BER\Type\Integer;

function dump($string) {
	$r = '';
	
	for ($i=0; $i<BER\strlen($string); $i++) {
		$r .= decbin(ord($string[$i])) . ' ';
	}
	
	return $r;
}

class IntegerTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Verify that all valid integer samples are parsed to match their actual value
	 */
	public function testParseValidSamples() {
		$dir = new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'samples');
		
		foreach ($values = new \RegexIterator($dir, '/^Integer_valid_/') AS $file) {
			preg_match('/^Integer_valid_([0-9]+)byte_(-?[0-9]+)$/', $file, $matches);
			$bytes = (int)$matches[1];
			$value = (int)$matches[2];
			
			$data = file_get_contents($file->getPathName());
			
			$int = new Integer();
			$int->parse($data, 0, $bytes);
			
			$this->assertEquals($int->value(), $value);
		}
	}
	
	/**
	 * Verify that integer samples are encoded to valid values
	 * 
	 * added case 128: must be encoded in two bytes (a zero byte must be padded so it is not seen as -128)
	 * added case -255: must be encoded in two bytes (a 0xFF byte must be padded so it is not seen as 1)
	 */
	public function testEncodeValidSamples() {
		$dir = new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'samples');
		
		foreach ($values = new \RegexIterator($dir, '/^Integer_valid_/') AS $file) {
			preg_match('/^Integer_valid_([0-9]+)byte_(-?[0-9]+)$/', $file, $matches);
			$bytes = (int)$matches[1];
			$value = (int)$matches[2];
			
			$reference = file_get_contents($file->getPathName());
			
			$int = new Integer();
			$int->init($value);
			
			$encoded = $int->encode();
			
			$this->assertEquals(BER\strlen($reference), BER\strlen($encoded), "Encoded value $value, expecting $bytes bytes, got " . BER\strlen($encoded) . ' bytes.');
			
			$this->assertEquals($reference, $encoded, "Encoded value $value does not match reference.\nReference: " . dump($reference) . "\nEncoded:   " . dump($encoded));
		}
	}
}

?>