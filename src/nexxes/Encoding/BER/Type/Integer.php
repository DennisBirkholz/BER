<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace nexxes\Encoding\BER\Type;
use nexxes\Encoding\BER;

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'BER.php');

class Integer implements BER\Type {
	const TYPE	= BER\TYPE_PRIMITIVE;
	const CLS	= BER\CLASS_UNIVERSAL;
	const TAG	= 2;
	
	protected $value = 0;
	
	
	public function init($value) {
		if ($value instanceof self) {
			$this->value = $value->value;
			return;
		}
		
		if (!is_int($value)) {
			throw new \InvalidArgumentException('Illegal value for class ' . __CLASS__);
		}
		
		$this->value = $value;
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
		$this->value = 0;
		
		if (is_null($length)) {
			$length = BER\strlen($data) - $pos;
		}
		
		// If data starts with a 1, value is negative, invert the 0 so after shifting the number will be negative
		if (ord($data[$pos]) & BER\BIT8) {
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
			
			// Do not pad with zeros or ones
			if ($v === 0x00) { break; }
			if ($v ===   -1) { break; }
		}
		
		// Need to pad with a byte of zeros so positive number is not mistaken as negative
		if (($this->value >= 0) && (ord($r[0]) & BER\BIT8)) {
			$r = chr(0) . $r;
		}
		
		elseif (($this->value <= 0) && (!(ord($r[0]) & BER\BIT8))) {
			$r = chr(0xFF) . $r;
		}
		
		// If the contents octets of an integer value encoding consist of more than one octet, then the bits of the first octet
		// and bit 8 of the second octet:
		// a) shall not all be ones; and
		// b) shall not all be zero.
		/*while (
			(BER\strlen($r) > 1)
			&& ((ord($r[0]) & BER\BIT8) === (ord($r[1]) & BER\BIT8))
			&& ((ord($r[0]) === 0x00) || (ord($r[0]) === 0xFF))
		) {
			$r = substr($r, 1);
		}*/
		
		return $r;
	}
	
	public function value() {
		return $this->value;
	}
}

?>