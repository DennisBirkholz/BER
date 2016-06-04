<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    public function testInitTrue()
    {
        $bool = new Boolean();
        $bool->init(true);
        $this->assertEquals($bool->encode(), TestHelper::hex2Str('0x0101FF'));
    }
    
    public function testInitFalse()
    {
        $bool = new Boolean();
        $bool->init(false);
        $this->assertEquals($bool->encode(), TestHelper::hex2Str('0x010100'));
    }
    
    /**
     * @expectedException Exception
     */
    public function testInitIllegal()
    {
        $bool = new Boolean();
        $bool->init("invalid");
    }
    
    public function testParseTrue()
    {
        // Everything from 0x01 - 0xFF must be true
        for ($i=0x01; $i<=0xFF; $i++) {
            $data = chr($i);
            $bool = new Boolean();
            $bool->parse($data, 0, 1);
            $this->assertTrue($bool->value());
        }
    }
    
    public function testParseFalse()
    {
        $data = chr(0x00);
        
        $bool = new Boolean();
        $bool->parse($data, 0, 1);
        $this->assertFalse($bool->value());
    }
    
    /**
     * @expectedException Exception
     */
    public function testParseIllegal()
    {
        $data = "invalid";
        
        $bool = new Boolean();
        $bool->parse($data, 0, strlen($data));
    }
}
