<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber;

abstract class Type implements TypeInterface
{
    protected $value = null;
    
    /**
    * Get the value stored in that type
    */
    public function value()
    {
        return $this->value;
    }
    
    /**
    * Override this function to encode the actual object data
    */
    abstract protected function encodeData();
    
    /**
     * {@inheritdoc}
     */
    public function encode()
    {
        $encoding = $this->encodeData();
        return Encoder::encodeIdentifier(static::TYPE, static::CLS, static::TAG)
            . Encoder::encodeLength(Parser::strlen($encoding))
            . $encoding;
    }
}
