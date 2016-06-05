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

// Only encoding bitstrings as primitive and not constructed
class BitString extends Type
{
    const TYPE	= Constants::T_PRIMITIVE;
    const CLS	= Constants::C_UNIVERSAL;
    const TAG	= 3;
    
    /**
     * @var int
     */
    protected $unused = 0;
    
    
    public function __construct($data, $unusedbits = 0)
    {
        $this->value = $data;
        $this->unused = $unusedbits;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function parse(Parser $parser, $data)
    {
        $unusedbits = ord($data[0]);
        
        if (($unusedbits < 0) || ($unusedbits > 7)) {
            throw new \RuntimeException('Parse error, number of unused bits (' . $unusedbits . ') exceeds allowed bounds of 0 <= x <= 7!');
        }
        
        return new static(Parser::substr($data, 1), $unusedbits);
    }

    public function encodeData()
    {
        return chr($this->unused & 0xFF) . $this->value;
    }
    
    public function getBit($number)
    {
        if ($number < 0) {
            throw new \InvalidArgumentException("Invalid negative bit offset.");
        }
        
        $lastbit = (Parser::strlen($this->value)*8 - $this->unused - 1);
        if ($number > $lastbit) {
            throw new \InvalidArgumentException("Last available bit is $lastbit but $number requested.");
        }
        
        $bit = $number % 8;
        $char = ($number - $bit)/8;
        $bit = 7 - $bit;
        
        return ((\ord($this->value[$char]) & (1 << $bit)) !== 0);
    }
}
