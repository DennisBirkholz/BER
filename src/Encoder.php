<?php
/*
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber;

/**
 * Class that contains helper methods for encoding BER stuff
 *
 * @author Dennis Birkholz <dennis@birkholz.biz>
 */
abstract class Encoder
{
    /**
     * Helper method that generates an identifier string for an element.
     * 
     * @return string
     */
    final public static function encodeIdentifier($type, $class, $tag)
    {
        $identifier = 0;
        
        if ($type == TypeInterface::T_CONSTRUCTED) {
            $identifier |= Parser::BIT6;
        }
        
        if ($class == TypeInterface::C_PRIVATE) {
            $identifier |= (Parser::BIT8 | Parser::BIT7);
        }
        
        elseif ($class == TypeInterface::C_CONTEXTSPECIFIC) {
            $identifier |= (Parser::BIT8);
        }
        
        elseif ($class == TypeInterface::C_APPLICATION) {
            $identifier |= (Parser::BIT7);
        }
        
        // Low tag, so only a single octet is needed for identifier
        if ($tag <= 30) {
            $identifier |= $tag;
            return chr($identifier);
        }
        
        // Indicate multi byte identifier
        $identifier |= (Parser::BIT5 | Parser::BIT4 | Parser::BIT3 | Parser::BIT2 | Parser::BIT1);
        $tag_num = $tag;
        $tag_str = chr($tag_num & Parser::NOT_BIT8);
        $tag_num >>= 7;
        
        while ($tag_num > 0) {
            $tag_str = chr(($tag_num & Parser::NOT_BIT8) | Parser::BIT8) . $tag_str;
            $tag_num >>= 7;
        }
        
        return chr($identifier) . $tag_str;
    }
    
    /**
     * Encode the supplied length for use as the length field in a BER encoded message.
     * 
     * @param int $string_length
     * @return string
     */
    final public static function encodeLength($string_length)
    {
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
        return chr(Parser::strlen($return) | Parser::BIT8) . $return;
    }
}
