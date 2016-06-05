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

class Nul extends Type
{
    const TYPE	= Constants::T_PRIMITIVE;
    const CLS	= Constants::C_UNIVERSAL;
    const TAG	= 5;
    
    public function __construct($value = null)
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public static function parse(Parser $parser, $data)
    {
        return new static(null);
    }
    
    /**
     * {@inheritdoc}
     */
    public function encodeData()
    {
        return '';
    }
    
    /**
     * {@inheritdoc}
     */
    public function value()
    {
        return null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function export($level = 0, $width = 30)
    {
        return sprintf("%-".$width."s: %s\n", str_repeat(' ', $level).preg_replace('/^\\\\?([^\\\\]+\\\\)*/', '', static::class), 'NULL');
    }
}
