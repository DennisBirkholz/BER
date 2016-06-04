<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use \dennisbirkholz\ber\Constants;
use \dennisbirkholz\ber\Parser;
use \dennisbirkholz\ber\Type;

class Boolean extends Type
{
    const TYPE	= Constants::T_PRIMITIVE;
    const CLS	= Constants::C_UNIVERSAL;
    const TAG	= 1;
    
    public function __construct($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Can not initialize boolean with non-boolean value');
        }
        
        $this->value = $value;
    }
    
    /**
     * @param string $data
     * @param int $pos
     * @param int $length
     * @return static
     */
    public static function parse(Parser $parser, $data)
    {
        if (Parser::strlen($data) !== 1) {
            throw new \InvalidArgumentException('Failed to parse boolean data: length must be one byte');
        }
        
        return new static((ord($data[0]) !== 0));
    }
    
    public function encodeData() {
        return chr($this->value ? 0xFF : 0x00);
    }
}
