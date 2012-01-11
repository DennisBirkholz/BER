<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace nexxes\Encoding\BER\Type;
use nexxes\Encoding\BER;

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'BER.php');

class Boolean implements BER\Type {
	const TYPE	= BER\TYPE_PRIMITIVE;
	const CLS	= BER\CLASS_UNIVERSAL;
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
	
	public function encode() {
		return chr($this->value ? 0xFF : 0x00);
	}
	
	public function value() {
		return $this->value;
	}
}

?>