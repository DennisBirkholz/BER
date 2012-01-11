<?php

abstract class BER {
	const TYPE_PRIMITIVE		= 'Primitive';
	const TYPE_CONSTRUCTED		= 'Constructed';
	
	const CLASS_UNIVERSAL		= 'Universal';
	const CLASS_APPLICATION		= 'Application';
	const CLASS_CONTEXTSPECIFIC	= 'ContextSpecific';
	const CLASS_PRIVATE			= 'Private';
	
	
	public static $registry = array(
		BER::TYPE_PRIMITIVE		=> array(
			BER::CLASS_UNIVERSAL		=> array(),
			BER::CLASS_APPLICATION		=> array(),
			BER::CLASS_CONTEXTSPECIFIC	=> array(),
			BER::CLASS_PRIVATE			=> array(),
		),
		BER::TYPE_CONSTRUCTED	=> array(
			BER::CLASS_UNIVERSAL		=> array(),
			BER::CLASS_APPLICATION		=> array(),
			BER::CLASS_CONTEXTSPECIFIC	=> array(),
			BER::CLASS_PRIVATE			=> array(),
		),
	);
	
	final public static function register($path) {
		if (!file_exists($path) || !is_readable($path)) {
			throw new InvalidArgumentException("File '$path' not found, could not include it.");
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
	
	
	final public static function parse($data, $context = array()) {
		$elements = self::parseToArray($data);
		$r = array();
		
		foreach ($elements AS $element) {
			$type = $element['type'];
			$class = $element['class'];
			$tag = $element['tag'];
			
			$newclass = false;
			if (isset($context[$type]) && isset($context[$type][$class]) && isset($context[$type][$class][$tag])) {
				$newclass = $context[$type][$class][$tag];
			}
			
			elseif (isset(self::$registry[$type][$class][$tag])) {
				$newclass = self::$registry[$type][$class][$tag];
			}
			
			if ($newclass) {
				$obj = new $newclass();
				$obj->parse(substr($data, $element['pos'], $element['length']));
				$r[] = $obj;
			}
			
			else {
				$r[] = new BER_Type_Placeholder($type, $class, $tag, substr($data, $element['pos'], $element['length']));
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
	final public static function parseToArray(&$data, $pos = 0, $length = null) {
		$struct = array();
		if (is_null($length)) { $length = BER::strlen($data)-$pos; }
		$max = $pos+$length;
		
		while ($pos < $max) {
			$e = array();
			
			$e['class'] = self::parseEncodingClass($data, $pos);
			$e['type'] = self::parseEncodingType($data, $pos);
			$e['tag'] = self::parseEncodingTag($data, $pos);
			$e['length'] = self::parseEncodingLength($data, $pos);
			$e['pos'] = $pos;
			
			$pos += $e['length'];
			$struct[] = $e;
		}
		
		//print_r($struct);
		
		return $struct;
		
	}
	
	final protected static function parseEncodingClass(&$data, &$pos) {
		$c = ord($data[$pos]);
		
		// Get class
		if ($c & BIT8) {
			if ($c & BIT7) {
				return BER::CLASS_PRIVATE;
			} else {
				return BER::CLASS_CONTEXTSPECIFIC;
			}
		} else {
			if ($c & BIT7) {
				return BER::CLASS_APPLICATION;
			} else {
				return BER::CLASS_UNIVERSAL;
			}
		}
	}
	
	final protected static function parseEncodingType(&$data, &$pos) {
		$c = ord($data[$pos]);
		
		if ($c & BIT6) {
			return BER::TYPE_CONSTRUCTED;
		} else {
			return BER::TYPE_PRIMITIVE;
		}
	}
	
	final protected static function parseEncodingTag(&$data, &$pos) {
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
	
	final protected static function parseEncodingLength(&$data, &$pos) {
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
	
	
	/**
	 * Call the encode function of the supplied object
	 * Calculate the length of the resulting encoding
	 * Generate class, type and tag marker and length indicator
	 * Return the complete encoding of obj
	 */
	final public static function encode(BER_Type $obj) {
		$encoding = $obj->encode();
		return self::generateEncodingIdentifier($obj) . self::calculateEncodingLength($encoding) . $encoding;
	}
	
	final protected static function generateEncodingIdentifier($obj) {
		$identifier = 0;
		$class = get_class($obj);
		
		if ($class::TYPE == BER::TYPE_CONSTRUCTED) {
			$identifier |= BIT6;
		}
		
		if ($class::CLS == BER::CLASS_PRIVATE) {
			$identifier |= (BIT8|BIT7);
		}
		
		elseif ($class::CLS == BER::CLASS_CONTEXTSPECIFIC) {
			$identifier |= (BIT8);
		}
		
		elseif ($class::CLS == BER::CLASS_APPLICATION) {
			$identifier |= (BIT7);
		}
		
		// Low tag, so only a single octet is needed for identifier
		if ($class::TAG <= 30) {
			$identifier |= $class::TAG;
			return chr($identifier);
		}
		
		// Indicate multi byte identifier
		$identifier |= (BIT5|BIT4|BIT3|BIT2|BIT1);
		$tag_num = $class::TAG;
		$tag_str = chr($tag_num & 127);
		$tag_num >>= 7;
		
		while ($tag_num > 0) {
			$tag_str = chr(($tag_num & 127) | BIT8);
			$tag_num >>= 7;
		}
		
		return chr($identifier) . $tag_str;
	}
	
	final protected static function calculateEncodingLength(&$string) {
		$string_length = BER::strlen($string);
		
		// Only one byte is needed for length
		if ($string_length < 128) {
			return chr($string_length);
		}
		
		// Using multibyte length 
		$return = '';
		
		do {
			$return = chr($string_length & 0xFF) . $return;
			$string_length >>= 8;
		} while ($string_length > 0);
		
		// BIT8 needs to be set as multi byte length indicator
		return chr(BER::strlen($return) | BIT8) . $return;
	}
	
	
	/**
	 * Wrapper for string length
	 * Build workarounds here if mb_string function overloading is active and strlen() interprets $string as multibyte string
	 * FIXME
	 */
	final public static function strlen(&$string) {
		return strlen($string);
	}
	
	/**
	 * Wrapper for substring
	 * Build workarounds here if mb_string function overloading is active and substr() interprets $string as multibyte string
	 * FIXME
	 */
	final public static function substr(&$string, $pos = 0, $length = null) {
		return substr($string, $pos, (is_null($length) ? self::strlen($string) : $length));
	}
}

?>