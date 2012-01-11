<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_SequenceOf implements BER_Type {
	const TYPE	= BER::TYPE_CONSTRUCTED;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 16;
	
	/**
	 * Constrain sequence to have a minimum of elements. 0 means no minimum.
	 */
	protected static $min = 0;
	
	/**
	 * Constrain sequence to have a maximum of elements. Null means no limit.
	 */
	protected static $max = null;
	
	/**
	 * Constraints for the SequenceOf-Type work like the following:
	 *   It is a string containing the class name of the classes that can occur
	 * or
	 *   It is an array (for a SEQUENCE OF xxx CHOICE type) listing all classes that can occur
	 */
	protected static $definition = "";
	
	public $values = array();
	
	
	public function init($value) {
		if (!is_array($value)) {
			// TODO: handle error
			return;
		}
		
		$this->value = $value;
	}
	
	public function parse(&$data, $pos = 0, $length = null) {
		$me = get_class($this);
		$allowed = array();
		
		// Prepare search array
		if (is_array($me::$definition)) {
			foreach ($me::$definition AS $class) {
				$allowed[$class::TYPE][$class::CLS][$class::TAG] = $class;
			}
		}
		
		else {
			$class = $me::$definition;
			$allowed[$class::TYPE][$class::CLS][$class::TAG] = $class;
		}
		
		// Parse elements
		$elements = BER::parseToArray($data, $pos, $length);
		
		// Walk elements
		foreach ($elements AS $element) {
			if (
				!isset($allowed[$element['type']])
				|| !isset($allowed[$element['type']][$element['class']])
				|| !isset($allowed[$element['type']][$element['class']][$element['tag']])
			) {
				throw new Exception('Element (' . $element['type'] . ', ' . $element['class'] . ', ' . $element['tag'] .') not allowed for class "' . $me . '".');
			}
			
			// Create new element and store it
			$n = new $allowed[$element['type']][$element['class']][$element['tag']]();
			$n->parse(substr($data, $element['pos'], $element['length']));
			$this->values[] =& $n;
			unset($n);
		}
		
		if (($me::$min > 0) && (count($this->values) < $me::$min)) {
			throw new Exception('Class "' . $me . '" requires a minimum of #' . $me::$min . ' elements but has only #' . count($values) . '.');
		}
		
		if (!is_null($me::$max) && ($me::$max < count($this->values))) {
			throw new Exception('Class "' . $me . '" has a maximum of #' . $me::$max . ' elements but has #' . count($values) . ' elements.');
		}
	}
	
	public function encode() {
		$return = '';
		
		for($i=0; $i<count($this->value); $i++) {
			$return .= BER::encode($this->value[$i]);
		}
		
		return $return;
	}
}

?>