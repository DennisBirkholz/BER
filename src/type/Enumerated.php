<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use \dennisbirkholz\ber\Constants;

/**
 * An enumerated cannot be instantiated directly as it does not know the valid choices.
 * Extend the Enumerated and fill the choices array to make it work 
 */
abstract class Enumerated extends Integer
{
    const TYPE	= Constants::T_PRIMITIVE;
    const CLS	= Constants::C_UNIVERSAL;
    const TAG	= 10;
    
    protected static $choices = array();
    
    public function __construct($value)
    {
        parent::{__FUNCTION__}($this->mapValue($value));
    }

    public function value($numerical = false)
    {
        if ($numerical) {
            return $this->value;
        } else {
            return static::$choices[$this->value];
        }
    }
    
    protected function mapValue($value)
    {
        $choices = static::$choices;
        
        // Supplied index is valid
        if (isset($choices[$value])) {
            return $value;
        }
        
        // Resolve named choice to numerical index
        elseif (($index = array_search($value, $choices, true)) !== false) {
            return $index;
        }
        
        else {
            throw new \Exception('Illegal value "' . $value . '" found for ' . preg_replace('/^\\\\?([^\\\\]+\\\\)*/', '', static::class).'!');
        }
    }
}
