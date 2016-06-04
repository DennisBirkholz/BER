<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber;

abstract class Type
{
    const T_PRIMITIVE = 'Primitive';
    const T_CONSTRUCTED = 'Constructed';
    
    const C_UNIVERSAL = 'Universal';
    const C_APPLICATION = 'Application';
    const C_CONTEXTSPECIFIC = 'ContextSpecific';
    const C_PRIVATE = 'Private';
    
    protected $value = null;
    
    /**
    * Set the values after creating the empty object
    */
    public function init($value)
    {
        $this->value = $value;
    }
    
    /**
    * Create a new element from this data (type and length needs to be stripped before passing)
    */
    //abstract public function parse(&$data, $pos = 0, $length = null);
    
    /**
    * Get the value stored in that type
    */
    public function value()
    {
        return $this->value;
    }
    
    /**
    * Override this function to encode the actual object data
    */
    abstract protected function encodeData();
    
    /**
     * Generate the BER encoding of the object
     * 
     * Call the encode function of the supplied object
     * Calculate the length of the resulting encoding
     * Generate class, type and tag marker and length indicator
     * Return the complete encoding of obj
     */
    public function encode()
    {
        $encoding = $this->encodeData();
        return $this->generateEncodingIdentifier() . $this->calculateEncodingLength($encoding) . $encoding;
    }
    
    protected function generateEncodingIdentifier()
    {
        $identifier = 0;
        
        if (static::TYPE == self::T_CONSTRUCTED) {
            $identifier |= Parser::BIT6;
        }
        
        if (static::CLS == self::C_PRIVATE) {
            $identifier |= (Parser::BIT8 | Parser::BIT7);
        }
        
        elseif (static::CLS == self::C_CONTEXTSPECIFIC) {
            $identifier |= (Parser::BIT8);
        }
        
        elseif (static::CLS == self::C_APPLICATION) {
            $identifier |= (Parser::BIT7);
        }
        
        // Low tag, so only a single octet is needed for identifier
        if (static::TAG <= 30) {
            $identifier |= static::TAG;
            return chr($identifier);
        }
        
        // Indicate multi byte identifier
        $identifier |= (Parser::BIT5 | Parser::BIT4 | Parser::BIT3 | Parser::BIT2 | Parser::BIT1);
        $tag_num = static::TAG;
        $tag_str = chr($tag_num & 127);
        $tag_num >>= 7;
        
        while ($tag_num > 0) {
            $tag_str = chr(($tag_num & 127) | Parser::BIT8);
            $tag_num >>= 7;
        }
        
        return chr($identifier) . $tag_str;
    }
    
    protected function calculateEncodingLength(&$string)
    {
        $string_length = mb_strlen($string, 'ASCII');
        
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
        return chr(mb_strlen($return, 'ASCII') | Parser::BIT8) . $return;
    }
}
