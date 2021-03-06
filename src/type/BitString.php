<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace DennisBirkholz\BER\type;

use \DennisBirkholz\BER\Constants;
use \DennisBirkholz\BER\Parser;
use \DennisBirkholz\BER\Type;

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
        if (($unusedbits < 0) || ($unusedbits > 7)) {
            throw new \InvalidArgumentException('Number of unused bits (' . $unusedbits . ') exceeds allowed bounds of 0 <= x <= 7!');
        }
        
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
            throw new \InvalidArgumentException('Parse error, number of unused bits (' . $unusedbits . ') exceeds allowed bounds of 0 <= x <= 7!');
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
    
    /**
     * {@inheritdoc}
     */
    public function export($level = 0, $width = 30)
    {
        $str = sprintf("%-".$width."s (%d)", str_repeat(' ', $level).preg_replace('/^\\\\?([^\\\\]+\\\\)*/', '', self::class), Parser::strlen($this->value));
        
        $pos = 0;
        $lines = ceil(Parser::strlen($this->value)/16);
        for ($line=0; $line<$lines; $line++) {
            $chars = ($line+1 == $lines ? Parser::strlen($this->value) % 16 : 16);
            
            $str .= sprintf("\n%s%04x", str_repeat(' ', $level+1), $pos); 
            
            for ($char=0; $char<$chars; $char++) {
                $str .= sprintf(" %02x", ord($this->value[$pos+$char]));
            }
            
            $pos += 16;
        }
        
        return $str . "\n";
    }
}
