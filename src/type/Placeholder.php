<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

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
    
    public function parse(&$data, $pos = 0, $length = null)
    {
    }
}
