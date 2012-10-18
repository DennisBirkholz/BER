<?php
/* $Id$
 * $URL$
 * $Copyright$ */

/**
 * @see http://www.itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf
 */
namespace org\birkholz\Encoding\BER;

abstract class Parser {
	// Contains mappings of types to class names
	protected static $registry = array(
		Type::PRIMITIVE		=> array(
			Type::UNIVERSAL			=> array(),
			Type::APPLICATION		=> array(),
			Type::CONTEXTSPECIFIC	=> array(),
			Type::PRIVATE			=> array(),
		),
		Type::CONSTRUCTED	=> array(
			Type::UNIVERSAL			=> array(),
			Type::APPLICATION		=> array(),
			Type::CONTEXTSPECIFIC	=> array(),
			Type::PRIVATE			=> array(),
		),
	);
	
	public static function register($path) {
		if (!file_exists($path) || !is_readable($path)) {
			throw new \InvalidArgumentException("File '$path' not found, could not include it.");
		}
		
		// Load class file
		require_once($path);
		
		// 
		$seq = explode(DIRECTORY_SEPARATOR, preg_replace('/\.(class|inc|lib)\.php$/i', '', $path));
		
		while (count($seq) && ($class = implode('_', $seq)) && !class_exists($class)) {
			array_shift($seq);
		}
		
		if (count($seq) === 0) {
			throw new InvalidArgumentException("No class found for file '$path'.");
		}
		
		self::$registry[$class::TYPE][$class::CLS][$class::TAG] = $class;
	}
	
	public static function init() {
		
	}
	
	function parse($data, $context = array()) {
		$elements = parseToArray($data);
		$r = array();
		
		foreach ($elements AS $element) {
			$type = $element['type'];
			$class = $element['class'];
			$tag = $element['tag'];
			
			$newclass = false;
			if (isset($context[$type]) && isset($context[$type][$class]) && isset($context[$type][$class][$tag])) {
				$newclass = $context[$type][$class][$tag];
			}
			
			elseif (isset($registry[$type][$class][$tag])) {
				$newclass = $registry[$type][$class][$tag];
			}
			
			if ($newclass) {
				$obj = new $newclass();
				$obj->parse(substr($data, $element['pos'], $element['length']));
				$r[] = $obj;
			}
			
			else {
				$r[] = new BER\Type\Placeholder($type, $class, $tag, substr($data, $element['pos'], $element['length']));
			}
			
		}
		
		return $r;
	}
	
	/**
		* Parse a BER encoded stream and create an array containing information
		*  about the found elements
		* Each element of the result consists of:
		*  element['class'] = One of the BER_CLASS_xxx constants
		*  element['type'] = BER_TYPE_PRIMITIVE or BER_TYPE_CONSTRUCTED
		*  element['tag'] = tag number of the element
		*  element['length'] = content length of the element
		*  element['pos'] = first octet of the content in $data
		*
		* @param &$data The octet stream to parse and find elements
		* @param $pos Start at this position in the stream instead of the first octet
		* @param $length Parse only $length octets instead to the end of the stream
		*/
	function parseToArray(&$data, $pos = 0, $length = null) {
		$struct = array();
		
		if (is_null($length)) { $length = strlen($data)-$pos; }
		$max = $pos+$length;
		
		while ($pos < $max) {
			$e = array();
			
			$e['class']		= parseEncodingClass($data, $pos);
			$e['type']		= parseEncodingType($data, $pos);
			$e['tag']		= parseEncodingTag($data, $pos);
			$e['length']	= parseEncodingLength($data, $pos);
			$e['pos']		= $pos;
				
			$pos += $e['length'];
			$struct[] = $e;
		}
		
		//print_r($struct);
		
		return $struct;
	}
	
	function parseEncodingClass(&$data, &$pos) {
		$c = ord($data[$pos]);
		
		// Get class
		if ($c & BIT8) {
			if ($c & BIT7) {
				return CLASS_PRIVATE;
			} else {
				return CLASS_CONTEXTSPECIFIC;
			}
		} else {
			if ($c & BIT7) {
				return CLASS_APPLICATION;
			} else {
				return CLASS_UNIVERSAL;
			}
		}
	}

	function parseEncodingType(&$data, &$pos) {
		$c = ord($data[$pos]);
		
		if ($c & BIT6) {
			return TYPE_CONSTRUCTED;
		} else {
			return TYPE_PRIMITIVE;
		}
	}

	function parseEncodingTag(&$data, &$pos) {
		$tag = (ord($data[$pos++]) & (BIT1|BIT2|BIT3|BIT4|BIT5));
		
		if ($tag < 31) { return $tag; }
		$tag = 0;
		
		do {
			$c = ord($data[$pos++]);
			
			$tag <<= 7;
			$tag += (~BIT8 & $c);
			
			// Found end marker
		} while ($c & BIT8);
		
		return $tag;
	}

	function parseEncodingLength(&$data, &$pos) {
		// Length
		$length = ord($data[$pos++]);
		
		if ($length === BIT8) {
			throw new Exception("Indefinite length field not implemented");
		}
		
		// Only one byte for length
		if (($length & BIT8) === 0) {
			return $length;
		}
		
		$parts = ($length & ~BIT8);
		$length = 0;
		
		for ($i=0; $i<$parts; $i++) {
			$length <<= 8;
			$length += ord($data[$pos++]);
		}
		
		return $length;
	}
}
