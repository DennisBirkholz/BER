<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Parser;
use dennisbirkholz\ber\Type;

/**
 * An enumerated cannot be instantiated directly as it does not know the valid choices.
 * Extend the Enumerated and fill the choices array to make it work 
 */
abstract class Enumerated extends Integer
{
    const TYPE	= Type::T_PRIMITIVE;
    const CLS	= Type::C_UNIVERSAL;
    const TAG	= 10;
    
    protected static $choices = array();
    
    public function __construct($value)
    {
        parent::{__FUNCTION__}($value);
        $this->verify();
    }
    
    /**
     * {@inheritdoc}
     */
    public static function parse(Parser $parser, $data)
    {
        $enum = parent::{__FUNCTION__}($parser, $data);
        $enum->verify();
        return $enum;
    }

    public function value($numerical = false)
    {
        if ($numerical) {
            return $this->value;
        } else {
            return static::$choices[$this->value];
        }
    }
    
    protected function verify()
    {
        $me = get_class($this);
        $p = $me::$choices;
        $v = $this->value;
        
        // Supplied index is valid
        if (isset($p[$v])) {
            return true;
        }
        
        // Resolve named choice to numerical index
        elseif (($index = array_search($v, $p, true)) !== false) {
            $this->value = $index;
            return true;
        }
        
        else {
            throw new \Exception('Illegal value "' . $v . '" found for sequence.');
        }
    }
}
