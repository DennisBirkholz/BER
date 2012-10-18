<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;
use org\birkholz\Encoding\BER;

class Null extends BER\Type {
	const TYPE	= self::T_PRIMITIVE;
	const CLS	= self::C_UNIVERSAL;
	const TAG	= 5;
	
	public function init($value) {
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
	}
	
	public function encodeData() {
		return '';
	}
	
	public function value() {
		return null;
	}
}
