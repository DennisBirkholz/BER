<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;
use org\birkholz\Encoding\BER;

class Real implements BER\Type {
	const TYPE	= self::T_PRIMITIVE;
	const CLS	= self::C_UNIVERSAL;
	const TAG	= 9;
	
	public $value = 0.0;
	
	public function init($value) {
		if (!is_float($value)) {
			// TODO: handle error
			return;
		}
		
		$this->value = $value;
	}
	
	/**
	 * TODO: Implement it!
	 */
	public function parse(&$data, $pos = 0, $length = null) {
		if (strlen($data) === 1) {
			// Plus-infinity
			if (ord($data[0]) === BIT7) {
				$this->value = PHP_INT_MAX;
				return;
			}
			
			elseif (ord($data[0]) === (BIT7&BIT1)) {
				$this->value = PHP_INT_MAX+1;
				return;
			}
			
			else {
				throw new UnexpectedValueException('Could not parse float value, expected infinite value');
			}
		}
		
		// Character encoding style
		if (($data[0] & (BIT8&BIT7)) === 0) {
		
		}
		
		
		$this->value = 0;
		
		// If data starts with a 1, value is negative, invert the 0 so after shifting the number will be negative
		if (ord($data[0]) & BIT8) {
			$this->value = ~$this->value;
		}
		
		for ($i=0; $i<strlen($data); $i++) {
			$this->value <<= 8;
			$this->value += ord($data[$i]);
		}
		
		// PHP uses 2-complement to store integers, so no conversion is needed here
		// Negative
		//if (ord($data[0]) & BIT8) {
		//	print "NEGATIVE\n";
		//	$this->value--;
		//	$this->value = ~$this->value;
 		//	$this->value *= -1;
		//}
	}
	
	
	public function encodeData() {
		$v = $this->value;
		$r = '';
		
		// PHP uses 2-complement to store integers, so no conversion is needed here
		// if ($v < 0) {
		//	$v = ~abs($v);
		//	$v++;
		//}
		
		for ($i=0; $i<PHP_INT_SIZE; $i++) {
			$r = chr($v & 0xFF) . $r;
			$v >>= 8;
		}
			
		if (($this->value >= 0) && (ord($r[0]) & BIT8)) {
				$r = chr(0) . $r;
		}
		
		return $r;
	}
}
