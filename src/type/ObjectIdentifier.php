<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Parser;
use dennisbirkholz\ber\Type;

class ObjectIdentifier extends Type
{
    const TYPE	= Type::T_PRIMITIVE;
    const CLS	= Type::C_UNIVERSAL;
    const TAG	= 6;
    
    public function __construct(array $value)
    {
        $this->value = $value;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function parse(Parser $parser, $data)
    {
        $length = Parser::strlen($data);
        $values = [];
        $n = 0;
        
        for ($i=0; $i<$length; $i++) {
            if (!isset($values[$n])) {
                $values[$n] = 0;
            }
            
            $values[$n] <<= 7;
            $values[$n] += (int)(ord($data[$i]) & ~Parser::BIT8);
            
            // Bit was the last bit
            if ((ord($data[$i]) & Parser::BIT8) === 0) {
                $n++;
            }
        }
        
        if ($n === 0) {
            throw new \InvalidArgumentException('Empty Object Identifier supplied!');
        }
        
        $y = $values[0] % 40;
        $x = ($values[0] - $y)/40;
        
        $values[0] = $y;
        array_unshift($values, $x);
        
        return new static($values);
    }
    
    /**
     * {@inheritdoc}
     */
    public function encodeData() {
        $values = $this->value;
        $n = (array_shift($values) * 40);
        $n += array_shift($values);
        array_unshift($values, $n);
        
        $return = '';
        foreach ($values as $value) {
            $return .= $this->encodeNumber($value);
        }
        
        return $return;
    }
    
    /**
     * Encode a single number as required
     * 
     * @param int $number
     * @return string
     */
    private function encodeNumber($number)
    {
        $return = '';
        
        do {
            $return .= chr(($number & Parser::NOT_BIT8) | Parser::BIT8);
            $number >>= 7;
        } while ($number > 0);
        
        $return[0] = chr(ord($return[0]) & Parser::NOT_BIT8);
        
        return strrev($return);
    }
}