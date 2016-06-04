<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Parser;
use dennisbirkholz\ber\Type;

class Real extends Type
{
    const TYPE	= self::T_PRIMITIVE;
    const CLS	= self::C_UNIVERSAL;
    const TAG	= 9;
    
    public function __construct($value)
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Invalid value supplied!");
        }
        
        $this->value = (float)$value;
    }
    
    /**
     * TODO: Implement it!
     */
    public static function parse(Parser $parse, $data)
    {
        if (Parser::strlen($data) === 1) {
            // Plus-infinity
            if (ord($data[0]) === Parser::BIT7) {
                return new static(INF);
            }
            
            elseif (ord($data[0]) === (Parser::BIT7 & Parser::BIT1)) {
                return new static(-INF);
            }
            
            else {
                throw new UnexpectedValueException('Could not parse float value, expected infinite value');
            }
        }

        // Character encoding style
        if (($data[0] & (Parser::BIT8 & Parser::BIT7)) === 0) {
        }
        
        $value = 0;
        
        // If data starts with a 1, value is negative, invert the 0 so after shifting the number will be negative
        if (ord($data[0]) & Parser::BIT8) {
            $value = ~$value;
        }
        
        for ($i=0; $i<strlen($data); $i++) {
            $value <<= 8;
            $value += ord($data[$i]);
        }
        
        // PHP uses 2-complement to store integers, so no conversion is needed here
        // Negative
        //if (ord($data[0]) & Parser::BIT8) {
        //	print "NEGATIVE\n";
        //	$value--;
        //	$value = ~$value;
        //	$value *= -1;
        //}
        
        return new static($value);
    }
    
    public function encodeData()
    {
        $v = $this->value;
        $r = '';
        
        // PHP uses 2-complement to store integers, so no conversion is needed here
        // if ($v < 0) {
        //	$v = ~abs($v);
        //	$v++;
        //}
        
        for ($i=0; $i<PHP_INT_SIZE; $i++) {
            $r = chr($v & 0xFF) . $r;
            $v >>= 8;
        }
        
        if (($this->value >= 0) && (ord($r[0]) & Parser::BIT8)) {
                $r = chr(0) . $r;
        }
        
        return $r;
    }
}
