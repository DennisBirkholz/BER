<?php
/* $Id$
 * $URL$
 * $Copyright$ */

/**
 * @see http://www.itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf
 */

namespace nexxes\Encoding\BER;

const BIT1	=   1;
const BIT2	=   2;
const BIT3	=   4;
const BIT4	=   8;
const BIT5	=  16;
const BIT6	=  32;
const BIT7	=  64;
const BIT8	= 128;

const TYPE_PRIMITIVE		= 'Primitive';
const TYPE_CONSTRUCTED		= 'Constructed';

const CLASS_UNIVERSAL		= 'Universal';
const CLASS_APPLICATION		= 'Application';
const CLASS_CONTEXTSPECIFIC	= 'ContextSpecific';
const CLASS_PRIVATE			= 'Private';

// Contains mappings of types to class names
$registry = array(
	TYPE_PRIMITIVE		=> array(
		CLASS_UNIVERSAL			=> array(),
		CLASS_APPLICATION		=> array(),
		CLASS_CONTEXTSPECIFIC	=> array(),
		CLASS_PRIVATE			=> array(),
	),
	TYPE_CONSTRUCTED	=> array(
		CLASS_UNIVERSAL			=> array(),
		CLASS_APPLICATION		=> array(),
		CLASS_CONTEXTSPECIFIC	=> array(),
		CLASS_PRIVATE			=> array(),
	),
);

function register($path) {
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
	
	$registry[$class::TYPE][$class::CLS][$class::TAG] = $class;
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
				$r[] = new Type\Placeholder($type, $class, $tag, substr($data, $element['pos'], $element['length']));
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
	
	if (\is_null($length)) { $length = strlen($data)-$pos; }
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
		return BER::TYPE_CONSTRUCTED;
	} else {
		return BER::TYPE_PRIMITIVE;
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


/**
	* Call the encode function of the supplied object
	* Calculate the length of the resulting encoding
	* Generate class, type and tag marker and length indicator
	* Return the complete encoding of obj
	*/
function encode(Type $obj) {
	$encoding = $obj->encode();
	return generateEncodingIdentifier($obj) . calculateEncodingLength($encoding) . $encoding;
}

function generateEncodingIdentifier($obj) {
	$identifier = 0;
	$class = get_class($obj);
	
	if ($class::TYPE == TYPE_CONSTRUCTED) {
		$identifier |= BIT6;
	}
	
	if ($class::CLS == CLASS_PRIVATE) {
		$identifier |= (BIT8|BIT7);
	}
	
	elseif ($class::CLS == CLASS_CONTEXTSPECIFIC) {
		$identifier |= (BIT8);
	}
	
	elseif ($class::CLS == CLASS_APPLICATION) {
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

function calculateEncodingLength(&$string) {
	$string_length = strlen($string);
	
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
function strlen(&$string) {
	return \strlen($string);
}

/**
 * Wrapper for substring
 * Build workarounds here if mb_string function overloading is active and substr() interprets $string as multibyte string
 * FIXME
 */
function substr(&$string, $pos = 0, $length = null) {
	return \substr($string, $pos, (\is_null($length) ? \strlen($string) : $length));
}

?>