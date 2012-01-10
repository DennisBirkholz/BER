<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_Null implements BER_Type {
	const TYPE	= BER::TYPE_PRIMITIVE;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 5;
	
	public function init($value) {
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
	}
	
	public function encode() {
		return '';
	}
}

?>