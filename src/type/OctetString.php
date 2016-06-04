<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;
use org\birkholz\Encoding\BER;

// Only encoding octetstrings as primitive and not constructed
class OctetString extends BER\Type {
	const TYPE	= self::T_PRIMITIVE;
	const CLS	= self::C_UNIVERSAL;
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
			$length = strlen($data);
		}
		
		$this->value = substr($data, $pos, $length);
	}
	
	public function encodeData() {
		return $this->value;
	}
}
