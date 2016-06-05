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
     * {@inheritdoc}
     */
    public static function parse(Parser $parser, $data)
    {
        if (Parser::strlen($data) !== 1) {
            throw new \InvalidArgumentException('Failed to parse boolean data: length must be one byte');
        }
        
        return new static((ord($data[0]) !== 0));
    }
    
    /**
     * {@inheritdoc}
     */
    public function encodeData() {
        return chr($this->value ? 0xFF : 0x00);
    }
    
    /**
     * {@inheritdoc}
     */
    public function export($level = 0, $width = 30)
    {
        return sprintf("%-".$width."s: %s\n", str_repeat(' ', $level).preg_replace('/^\\\\?([^\\\\]+\\\\)*/', '', static::class), ($this->value ? 'TRUE' : 'FALSE'));
    }
}
