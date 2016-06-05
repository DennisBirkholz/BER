<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace DennisBirkholz\BER;

/**
 * @see http://www.itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf
 */
class Parser
{
    // Contains mappings of types to class names
    const MAPPING = [
        Constants::T_PRIMITIVE => [
            Constants::C_UNIVERSAL       => [
                type\BitString::TAG        => type\BitString::class,
                type\Boolean::TAG          => type\Boolean::class,
                type\Enumerated::TAG       => type\Enumerated::class,
                type\Integer::TAG          => type\Integer::class,
                type\Nul::TAG              => type\Nul::class,
                type\ObjectIdentifier::TAG => type\ObjectIdentifier::class,
                type\Real::TAG             => type\Real::class,
                type\OctetString::TAG      => type\OctetString::class,
                type\UTF8String::TAG       => type\UTF8String::class,
            ],
            Constants::C_APPLICATION     => [],
            Constants::C_CONTEXTSPECIFIC => [],
            Constants::C_PRIVATE         => [],
        ],
        Constants::T_CONSTRUCTED => [
            Constants::C_UNIVERSAL       => [
                type\Sequence::TAG   => type\Sequence::class,
    //                type\SequenceOf::TAG => type\SequenceOf::class,
                type\Set::TAG        => type\Set::class,
    //                type\SetOf::TAG      => type\SetOf::class,
            ],
            Constants::C_APPLICATION     => [],
            Constants::C_CONTEXTSPECIFIC => [],
            Constants::C_PRIVATE         => [],
        ],
    ];
    
    /**
     * Additional mapping to use with this parse.
     * Structure is same as with the MAPPING constant
     * @var array
     */
    protected $mapping = [];
    
    public function __construct($mapping = []) {
        $this->mapping = $mapping;
    }
    
    public function parse($data)
    {
        $mapping = self::MAPPING;
        $elements = self::parseToArray($data);
        $r = array();
        
        foreach ($elements AS $element) {
            $type = $element['type'];
            $class = $element['class'];
            $tag = $element['tag'];
            
            $newclass = false;
            if (isset($this->mapping[$type][$class][$tag])) {
                $newclass = $this->mapping[$type][$class][$tag];
            }
            
            elseif (isset($mapping[$type][$class][$tag])) {
                $newclass = $mapping[$type][$class][$tag];
            }
            
            if ($newclass) {
                $r[] = $newclass::parse($this, Parser::substr($data, $element['pos'], $element['length']));
            }
            
            else {
                $r[] = new type\Placeholder($type, $class, $tag, Parser::substr($data, $element['pos'], $element['length']));
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
    public static function parseToArray($data, $pos = 0, $length = null)
    {
        $struct = array();
        
        if (is_null($length)) { $length = Parser::strlen($data)-$pos; }
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
    
    protected static function parseEncodingClass($data, &$pos)
    {
        $c = ord($data[$pos]);
        
        // Get class
        if ($c & Constants::BIT8) {
            if ($c & Constants::BIT7) {
                return Constants::C_PRIVATE;
            } else {
                return Constants::C_CONTEXTSPECIFIC;
            }
        } else {
            if ($c & Constants::BIT7) {
                return Constants::C_APPLICATION;
            } else {
                return Constants::C_UNIVERSAL;
            }
        }
    }
    
    protected static function parseEncodingType($data, &$pos)
    {
        $c = ord($data[$pos]);
        
        if ($c & Constants::BIT6) {
            return Constants::T_CONSTRUCTED;
        } else {
            return Constants::T_PRIMITIVE;
        }
    }

    protected static function parseEncodingTag($data, &$pos)
    {
        $tag = (ord($data[$pos++]) & (Constants::BIT1|Constants::BIT2|Constants::BIT3|Constants::BIT4|Constants::BIT5));
        
        if ($tag < 31) { return $tag; }
        $tag = 0;
        
        do {
            $c = ord($data[$pos++]);
            
            $tag <<= 7;
            $tag += ($c & Constants::NOT_BIT8);
            
            // Found end marker
        } while ($c & Constants::BIT8);
        
        return $tag;
    }
    
    protected static function parseEncodingLength($data, &$pos)
    {
        // Length
        $length = ord($data[$pos++]);
        
        if ($length === Constants::BIT8) {
            throw new \RuntimeException("Indefinite length field not implemented");
        }
        
        // Only one byte for length
        if (($length & Constants::BIT8) === 0) {
            return $length;
        }
        
        $parts = ($length & Constants::NOT_BIT8);
        $length = 0;
        
        for ($i=0; $i<$parts; $i++) {
            $length <<= 8;
            $length += ord($data[$pos++]);
        }
        
        return $length;
    }
    
    /**
     * Get the number of bytes of the supplied string.
     * Required to circumvent multibyte string function overloading.
     * 
     * @param string $string
     * @return int
     */
    public static final function strlen($string)
    {
        return (function_exists('\mb_strlen') ? \mb_strlen($string, 'ASCII') : \strlen($string));
    }
    
    /**
     * Get a substring of a binary string.
     * Required to circumvent multibyte string function overloading.
     * 
     * @param string $string
     * @param int $offset
     * @param int|null $length
     * @return string
     */
    public static final function substr($string, $offset, $length = null)
    {
        return (
            function_exists('\mb_substr')
            ? \mb_substr($string, $offset, $length, 'ASCII')
            : \substr($string, $offset, $length)
        );
    }
}
