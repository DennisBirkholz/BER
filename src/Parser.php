<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber;

/**
 * @see http://www.itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf
 */
abstract class Parser
{
    const BIT1 =   1;
    const BIT2 =   2;
    const BIT3 =   4;
    const BIT4 =   8;
    const BIT5 =  16;
    const BIT6 =  32;
    const BIT7 =  64;
    const BIT8 = 128;
    
    // Contains mappings of types to class names
    protected static $registry = [
        Type::T_PRIMITIVE => [
            Type::C_UNIVERSAL       	=> [
                type\BitString::TAG   => type\BitString::class,
                type\Boolean::TAG     => type\Boolean::class,
                type\Enumerated::TAG  => type\Enumerated::class,
                type\Integer::TAG     => type\Integer::class,
                type\Nul::TAG         => type\Nul::class,
                type\Real::TAG        => type\Real::class,
                type\OctetString::TAG => type\OctetString::class,
                type\UTF8String::TAG  => type\UTF8String::class,
            ],
            Type::C_APPLICATION     => [],
            Type::C_CONTEXTSPECIFIC => [],
            Type::C_PRIVATE         => [],
        ],
        Type::T_CONSTRUCTED	=> [
            Type::C_UNIVERSAL       => [
                type\Sequence::TAG   => type\Sequence::class,
    //                type\SequenceOf::TAG => type\SequenceOf::class,
                type\Set::TAG        => type\Set::class,
    //                type\SetOf::TAG      => type\SetOf::class,
            ],
            Type::C_APPLICATION     => [],
            Type::C_CONTEXTSPECIFIC => [],
            Type::C_PRIVATE         => [],
        ],
    ];
    
    public static function register($path)
    {
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
    
    public static function init()
    {
    }
    
    public static function parse($data, $context = array())
    {
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
                $obj->parse($data, $element['pos'], $element['length']);
                $r[] = $obj;
            }
            
            else {
                $r[] = new type\Placeholder($type, $class, $tag, substr($data, $element['pos'], $element['length']));
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
    public static function parseToArray(&$data, $pos = 0, $length = null)
    {
        $struct = array();
        
        if (is_null($length)) { $length = strlen($data)-$pos; }
        $max = $pos+$length;
        
        while ($pos < $max) {
            $e = array();
            
            $e['class']		= self::parseEncodingClass($data, $pos);
            $e['type']		= self::parseEncodingType($data, $pos);
            $e['tag']		= self::parseEncodingTag($data, $pos);
            $e['length']	= self::parseEncodingLength($data, $pos);
            $e['pos']		= $pos;
            
            $pos += $e['length'];
            $struct[] = $e;
        }
        
        //print_r($struct);
        
        return $struct;
    }
    
    protected static function parseEncodingClass(&$data, &$pos)
    {
        $c = ord($data[$pos]);
        
        // Get class
        if ($c & self::BIT8) {
            if ($c & self::BIT7) {
                return Type::C_PRIVATE;
            } else {
                return Type::C_CONTEXTSPECIFIC;
            }
        } else {
            if ($c & self::BIT7) {
                return Type::C_APPLICATION;
            } else {
                return Type::C_UNIVERSAL;
            }
        }
    }
    
    protected static function parseEncodingType(&$data, &$pos)
    {
        $c = ord($data[$pos]);
        
        if ($c & self::BIT6) {
            return Type::T_CONSTRUCTED;
        } else {
            return Type::T_PRIMITIVE;
        }
    }

    protected static function parseEncodingTag(&$data, &$pos)
    {
        $tag = (ord($data[$pos++]) & (self::BIT1|self::BIT2|self::BIT3|self::BIT4|self::BIT5));
        
        if ($tag < 31) { return $tag; }
        $tag = 0;
        
        do {
            $c = ord($data[$pos++]);
            
            $tag <<= 7;
            $tag += (~self::BIT8 & $c);
            
            // Found end marker
        } while ($c & self::BIT8);
        
        return $tag;
    }
    
    protected static function parseEncodingLength(&$data, &$pos)
    {
        // Length
        $length = ord($data[$pos++]);
        
        if ($length === self::BIT8) {
            throw new \RuntimeException("Indefinite length field not implemented");
        }
        
        // Only one byte for length
        if (($length & self::BIT8) === 0) {
            return $length;
        }
        
        $parts = ($length & ~self::BIT8);
        $length = 0;
        
        for ($i=0; $i<$parts; $i++) {
            $length <<= 8;
            $length += ord($data[$pos++]);
        }
        
        return $length;
    }
}
