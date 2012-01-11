<?php
/* $Id$
 * $URL$
 * $Copyright$ */

// Only encoding bitstrings as primitive and not constructed
class BER_Type_Bitstring implements BER_Type {
	const TYPE	= BER::TYPE_PRIMITIVE;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 3;
	
	// A list of booleans
	public $value = array();
	
	
	public function init($data) {
		if (!is_array($data)) {
			// TODO: Handle errors
			return;
		}
		
		$this->value = $data;
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
		$ignorebits = ord($data[$pos++]);
		
		if (is_null($length)) {
			$length = BER::strlen($data);
		}
		
		for($i=$pos; $i<$length; $i++) {
			$c = ord($data[$i]);
			
			for ($j=0; $j<(8 - ($i == ($length-1) ? $ignorebits : 0)); $j++) {
				$this->value[] = (boolean)($c & BIT8);
				$c <<= 1;
			}
		}
	}
	
	public function encode() {
		// Number of bits not set in the last part
		$l = ((8 - (count($this->value) % 8)) % 8);
		
		$r = $l;
		
		for ($i=0; $i<count($this->value); $i++) {
			if (($i % 8) === 0) { $c = 0; }
			$c <<= 1;
			if ($this->value[$i]) { $c += 1; }
			if (($i % 8) === 7) { $r .= chr($c); }
		}
		
		if ($l > 0) {
			$c <<= $l;
			$r .= chr($c);
		}
		
		return $r;
	}
}

?>