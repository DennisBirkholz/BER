<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_Boolean implements BER_Type {
	const TYPE	= BER::TYPE_PRIMITIVE;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 1;
	
	public $value = false;
	
	
	public function init($value) {
		if ($value instanceof self) {
			$this->value = $value->value;
			return;
		}
		
		if (!is_bool($value)) {
			// TODO: Handle error, throw exception maybe
			return;
		}
		
		$this->value = $value;
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
		// TODO: Handle length != 1 byte
		$this->value = (ord($data[$pos]) !== 0);
	}
	
	public function encode() {
		return chr($this->value ? 0xFF : 0x00);
	}
}

?>