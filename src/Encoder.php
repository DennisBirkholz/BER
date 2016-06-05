<?php
/*
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace DennisBirkholz\BER;

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
        
        if ($type == Constants::T_CONSTRUCTED) {
            $identifier |= Constants::BIT6;
        }
        
        if ($class == Constants::C_PRIVATE) {
            $identifier |= (Constants::BIT8 | Constants::BIT7);
        }
        
        elseif ($class == Constants::C_CONTEXTSPECIFIC) {
            $identifier |= (Constants::BIT8);
        }
        
        elseif ($class == Constants::C_APPLICATION) {
            $identifier |= (Constants::BIT7);
        }
        
        // Low tag, so only a single octet is needed for identifier
        if ($tag <= 30) {
            $identifier |= $tag;
            return chr($identifier);
        }
        
        // Indicate multi byte identifier
        $identifier |= (Constants::BIT5 | Constants::BIT4 | Constants::BIT3 | Constants::BIT2 | Constants::BIT1);
        $tag_num = $tag;
        $tag_str = chr($tag_num & Constants::NOT_BIT8);
        $tag_num >>= 7;
        
        while ($tag_num > 0) {
            $tag_str = chr(($tag_num & Constants::NOT_BIT8) | Constants::BIT8) . $tag_str;
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
        return chr(Parser::strlen($return) | Constants::BIT8) . $return;
    }
}
