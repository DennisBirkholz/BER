<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_Placeholder {
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

?>