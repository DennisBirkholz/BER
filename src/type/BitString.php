<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Type;

// Only encoding bitstrings as primitive and not constructed
class BitString extends Type
{
    const TYPE	= self::T_PRIMITIVE;
    const CLS	= self::C_UNIVERSAL;
    const TAG	= 3;
    
    // A list of booleans
    protected $value = array();
    
    
    public function init($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Need an array of bits for a bitstring');
        }
        
        $this->value = $data;
    }

    public function parse(&$data, $pos = 0, $length = null)
    {
        $unusedbits = ord($data[$pos++]);
        
        if (($unusedbits < 0) || ($unusedbits > 7)) {
            throw new \RuntimeException('Parse error, number of unused bits (' . $unusedbits . ') exceeds allowed bounds of 0 <= x <= 7!');
        }
        
        if (is_null($length)) {
            $length = strlen($data);
        }
        
        for($i=$pos; $i<$length; $i++) {
            $c = ord($data[$i]);
            
            // Walk each bit
            for ($j=0; $j<8; $j++) {
                // Skip unused bits at end of string
                if (($i+1 === $length) && ($j >= (8 - $unusedbits))) {
                    break;
                }
                
                $this->value[] = (boolean)($c & BIT8);
                $c <<= 1;
            }
        }
    }

    public function encodeData()
    {
        // Number of bits not set in the last part
        $l = 8 - (count($this->value) % 8);
        // 8 means byte is completely used
        if ($l === 8) { $l = 0; }
        
        // Encode as 8bit integer
        $r = chr($l & 0xFF);
        
        for ($i=0; $i<count($this->value); $i++) {
            if (($i % 8) === 0) { $c = 0; }
            $c <<= 1;
            if ($this->value[$i]) { $c += 1; }
            if (($i % 8) === 7) { $r .= chr($c); }
        }
        
        if ($l > 0) {
            $c <<= $l;
            $r .= chr($c);
        }
        
        return $r;
    }
}
