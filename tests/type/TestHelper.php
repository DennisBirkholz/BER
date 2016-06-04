<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use \dennisbirkholz\ber\Parser;

abstract class TestHelper
{
    /**
     * Supply a hex sequence that is to be converted to a string (that may contain unprintable characters)
     */
    public static function hex2str($hex)
    {
        if ($hex[1] == 'x') {
            $hex = Parser::substr($hex, 2);
        }
        
        $position = 0;
        $string_length = Parser::strlen($hex);
        $return = '';
        
        while ($position < $string_length) {
            $return .= chr(hexdec($hex[$position++].$hex[$position++]));
        }
        
        return $return;
    }
    
    public static function str2hex($string)
    {
        $count = Parser::strlen($string);
        $r = '0x';
        
        for ($i=0; $i<$count; $i++) {
            $r .= str_pad(dechex(ord($string[$i])), 2, '0', STR_PAD_LEFT);
        }
        
        return $r;
    }
    
    public static function arr2bin($array)
    {
        $r = '';
        
        for ($i=0; $i<count($array); $i++) {
            $r .= ($array[$i] ? '1' : '0');
        }
        
        return $r;
    }
}
