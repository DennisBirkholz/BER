<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use \dennisbirkholz\ber\Parser;
use \dennisbirkholz\ber\Type;

class Placeholder extends Type
{
    public $type = '';
    public $class = '';
    public $tag = 0;
    public $value = null;
    
    public function __construct($type, $class, $tag, $value)
    {
        $this->type = $type;
        $this->class = $class;
        $this->tag = $tag;
        $this->value = $value;
    }
    
    protected function encodeData()
    {
    }
    
    public static function parse(Parser $parser, $data)
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function export($level = 0, $width = 30)
    {
        return sprintf("%-".$width."s: type: %s, class: %s, tag: %s, value: %s\n", str_repeat(' ', $level).preg_replace('/^\\\\?([^\\\\]+\\\\)*/', '', static::class), $this->type, $this->class, $this->tag, $this->value);
    }
}
