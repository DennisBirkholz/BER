<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Type;

class Nul extends Type
{
    const TYPE	= self::T_PRIMITIVE;
    const CLS	= self::C_UNIVERSAL;
    const TAG	= 5;
    
    public function init($value)
    {
    }
    
    public function parse(&$data, $pos = 0, $length = null)
    {
    }
    
    public function encodeData()
    {
        return '';
    }
    
    public function value()
    {
        return null;
    }
}
