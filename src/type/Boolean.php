<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Type;

class Boolean extends Type
{
    const TYPE	= self::T_PRIMITIVE;
    const CLS	= self::C_UNIVERSAL;
    const TAG	= 1;
    
    protected $value = false;
    
    
    public function init($value)
    {
        if ($value instanceof self) {
            $this->value = $value->value;
            return;
        }
        
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Can not initialize boolean with non-boolean value');
        }
        
        $this->value = $value;
    }

    public function parse(&$data, $pos = 0, $length = null)
    {
        if ($length !== 1) {
            throw new \InvalidArgumentException('Failed to parse boolean data: length must be one byte');
        }
        
        $this->value = (ord($data[$pos]) !== 0);
    }
    
    public function encodeData() {
        return chr($this->value ? 0xFF : 0x00);
    }
}
