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
     * {@inheritdoc}
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
    
    /**
     * {@inheritdoc}
     */
    public function export($level = 0, $width = 30)
    {
        return sprintf("%-".$width."s: %s\n", str_repeat(' ', $level).preg_replace('/^\\\\?([^\\\\]+\\\\)*/', '', static::class), $this->value);
    }
}
