<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;
use org\birkholz\Encoding\BER;

class Boolean extends BER\Type {
	const TYPE	= self::T_PRIMITIVE;
	const CLS	= self::C_UNIVERSAL;
	const TAG	= 1;
	
	protected $value = false;
	
	
	public function init($value) {
		if ($value instanceof self) {
			$this->value = $value->value;
			return;
		}
		
		if (!is_bool($value)) {
			throw new \InvalidArgumentException('Can not initialize boolean with non-boolean value');
		}
		
		$this->value = $value;
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
		if ($length !== 1) {
			throw new \InvalidArgumentException('Failed to parse boolean data: length must be one byte');
		}
		
		$this->value = (ord($data[$pos]) !== 0);
	}
	
	public function encodeData() {
		return chr($this->value ? 0xFF : 0x00);
	}
}
