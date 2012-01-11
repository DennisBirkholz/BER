<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace nexxes\Encoding\BER\Type;
use nexxes\Encoding\BER;

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'BER.php');

/**
 * An enumerated cannot be instantiated directly as it does not know the valid choices.
 * Extend the Enumerated and fill the choices array to make it work 
 */
abstract class Enumerated extends Integer {
	const TYPE	= BER\TYPE_PRIMITIVE;
	const CLS	= BER\CLASS_UNIVERSAL;
	const TAG	= 10;
	
	protected static $choices = array();
	
	
	public function parse(&$data, $pos = 0, $length = null) {
		parent::parse($data, $pos, $length);
		$this->verify();
	}
	
	public function init($v) {
		$this->value = $v;
		$this->verify();
	}
	
	public function value($numerical = false) {
		if ($numerical) {
			return $this->value;
		} else {
			$me = get_class($this);
			return $me::$choices[$this->value];
		}
	}
	
	protected function verify() {
		$me = get_class($this);
		$p =& $me::$choices;
		$v = $this->value;
		
		// Supplied index is valid
		if (isset($p[$v])) {
			return true;
		}
		
		// Resolve named choice to numerical index
		elseif (($index = array_search($v, $p, true)) !== false) {
			$this->value = $index;
			return true;
		}
		
		else {
			throw new \Exception('Illegal value "' . $v . '" found for sequence.');
		}
	}
}

?>