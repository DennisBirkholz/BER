<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;
use org\birkholz\Encoding\BER;

/**
 * An enumerated cannot be instantiated directly as it does not know the valid choices.
 * Extend the Enumerated and fill the choices array to make it work 
 */
abstract class Enumerated extends Integer {
	const TYPE	= self::T_PRIMITIVE;
	const CLS	= self::C_UNIVERSAL;
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
			return static::$choices[$this->value];
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
