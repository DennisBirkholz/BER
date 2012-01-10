<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_Integer implements BER_Type {
	const TYPE	= BER::TYPE_PRIMITIVE;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 2;
	
	public $value = 0;
	
	
	public function init($value) {
		if ($value instanceof self) {
			$this->value = $value->value;
			return;
		}
		
		if (!is_int($value)) {
			throw new Exception('Illegal value for class ' . __CLASS__);
			// TODO: handle error
			return;
		}
		
		$this->value = $value;
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
		$this->value = 0;
		
		if (is_null($length)) {
			$length = BER::strlen($data) - $pos;
		}
		
		// If data starts with a 1, value is negative, invert the 0 so after shifting the number will be negative
		if (ord($data[$pos]) & BIT8) {
			$this->value = ~$this->value;
		}
		
		for ($i=0; $i<$length; $i++,$pos++) {
			$this->value <<= 8;
			$this->value += ord($data[$pos]);
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
	
	
	public function encode() {
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
		
		while (BER::strlen($r) > 1) {
			// If the contents octets of an integer value encoding consist of more than one octet, then the bits of the first octet
			// and bit 8 of the second octet:
			// a) shall not all be ones; and
			// b) shall not all be zero.
			if (((ord($r[0]) == 0) && ((ord($r[1]) & BIT8) == 0)) || (((ord($r[0]) & 255) == 255) && (ord($r[1]) & BIT8))) {
				$r = substr($r, 1);
			}
		}
		
		return $r;
	}
}

?>