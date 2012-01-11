<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_Enumerated extends BER_Type_Integer {
	const TYPE	= BER::TYPE_PRIMITIVE;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 10;
	
	protected static $possibilities = array();
	
	
	public function parse(&$data, $pos = 0, $length = null) {
		parent::parse($data, $pos, $length);
		$this->verify();
	}
	
	public function init($v) {
		parent::init($v);
		$this->verify();
	}
	
	protected function verify() {
		$me = get_class($this);
		$p =& $me::$possibilities;
		$v = $this->value;
		
		if (isset($p[$v])) {
			$this->value = $p[$v];
		}
		
		elseif (array_search($v, $p, true)) {
		}
		
		else {
			throw new Exception('Illegal value "' . $v . '" found for sequence.');
		}
	}
}

?>