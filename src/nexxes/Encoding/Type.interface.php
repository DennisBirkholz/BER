<?php
/* $Id$
 * $URL$
 * $Copyright$ */

interface BER_Type {
	/**
	 * Set the values after creating the empty object
	 */
	public function init($value);
	
	/**
	 * Create a new element from this data (type and length needs to be stripped before passing)
	 */
	public function parse(&$data, $pos = 0, $length = null);
	
	/**
	 * Generate the BER encoding of the object
	 */
	public function encode();
}

?>