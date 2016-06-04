<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use \dennisbirkholz\ber\Constants;
use \dennisbirkholz\ber\Parser;
use \dennisbirkholz\ber\Type;

class SequenceOf extends Type
{
	const TYPE	= Constants::T_CONSTRUCTED;
	const CLS	= Constants::C_UNIVERSAL;
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
	
	
	public function __construct(array $value)
    {
		$this->value = $value;
	}
	
	public static function parse(Parser $parser, $data)
    {
		$allowed = array();
		
		// Prepare search array
		if (is_array(static::$definition)) {
			foreach (static::$definition AS $class) {
				$allowed[$class::TYPE][$class::CLS][$class::TAG] = $class;
			}
		}
		
		else {
			$class = static::$definition;
			$allowed[$class::TYPE][$class::CLS][$class::TAG] = $class;
		}
		
		// Parse elements
		$elements = Parser::parseToArray($data);
		
		// Walk elements
		foreach ($elements AS $element) {
			if (
				!isset($allowed[$element['type']])
				|| !isset($allowed[$element['type']][$element['class']])
				|| !isset($allowed[$element['type']][$element['class']][$element['tag']])
			) {
				throw new Exception('Element (' . $element['type'] . ', ' . $element['class'] . ', ' . $element['tag'] .') not allowed for class "' . get_class($this) . '".');
			}
			
			// Create new element and store it
			$n = new $allowed[$element['type']][$element['class']][$element['tag']]();
			$n->parse(Parser::substr($data, $element['pos'], $element['length']));
			$this->values[] =& $n;
			unset($n);
		}
		
		if ((static::$min > 0) && (count($this->values) < static::$min)) {
			throw new Exception('Class "' . get_class($this) . '" requires a minimum of #' . static::$min . ' elements but has only #' . count($values) . '.');
		}
		
		if (!is_null(static::$max) && (static::$max < count($this->values))) {
			throw new Exception('Class "' . get_class($this) . '" has a maximum of #' . static::$max . ' elements but has #' . count($values) . ' elements.');
		}
	}
	
	public function encode()
    {
		$return = '';
		
		for($i=0; $i<count($this->value); $i++) {
			$return .= $this->value[$i]->encode();
		}
		
		return $return;
	}
    
    /**
     * As encode() is overriden, encodeData() is never called.
     */
    protected function encodeData()
    {
    }
}
