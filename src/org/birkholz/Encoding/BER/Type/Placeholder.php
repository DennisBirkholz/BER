<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;
use org\birkholz\Encoding\BER;

class Placeholder implements BER\Type {
	public $type = '';
	public $class = '';
	public $tag = 0;
	public $value = null;
	
	public function __construct($type, $class, $tag, $value) {
		$this->type = $type;
		$this->class = $class;
		$this->tag = $tag;
		$this->value = $value;
	}
}
