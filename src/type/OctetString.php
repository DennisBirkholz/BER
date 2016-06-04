<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Type;

// Only encoding octetstrings as primitive and not constructed
class OctetString extends Type
{
    const TYPE	= self::T_PRIMITIVE;
    const CLS	= self::C_UNIVERSAL;
    const TAG	= 4;
    
    // A simple string of 8bit characters
    public $value = '';
    
    
    public function init($value)
    {
        if ($value instanceof self) {
            $this->value = $value->value;
        } else {
            $this->value = $value;
        }
    }

    public function parse(&$data, $pos = 0, $length = null)
    {
        if (is_null($length)) {
            $length = strlen($data);
        }
        
        $this->value = substr($data, $pos, $length);
    }

    public function encodeData()
    {
        return $this->value;
    }
}
