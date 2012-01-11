<?php
/* $Id$
 * $URL$
 * $Copyright$ */

// Only encoding octetstrings as primitive and not constructed
class BER_Type_OctetString implements BER_Type {
	const TYPE	= BER::TYPE_PRIMITIVE;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 4;
	
	// A simple string of 8bit characters
	public $value = '';
	
	
	public function init($value) {
		if ($value instanceof self) {
			$this->value = $value->value;
		} else {
			$this->value = $value;
		}
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
		if (is_null($length)) {
			$length = BER::strlen($data);
		}
		
		$this->value = BER::substr($data, $pos, $length);
	}
	
	public function encode() {
		return $this->value;
	}
}

?>