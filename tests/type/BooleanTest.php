<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace DennisBirkholz\BER\type;

use \DennisBirkholz\BER\Parser;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testInitTrue()
    {
        $bool = new Boolean(true);
        $this->assertEquals($bool->encode(), TestHelper::hex2Str('0x0101FF'));
    }
    
    /**
     * @test
     */
    public function testInitFalse()
    {
        $bool = new Boolean(false);
        $this->assertEquals($bool->encode(), TestHelper::hex2Str('0x010100'));
    }
    
    /**
     * @test
     */
    public function testInitIllegal()
    {
        $this->expectException(\Exception::class);
        new Boolean("invalid");
    }
    
    /**
     * @test
     */
    public function testParseTrue()
    {
        // Everything from 0x01 - 0xFF must be true
        for ($i=0x01; $i<=0xFF; $i++) {
            $data = chr($i);
            $bool = Boolean::parse(new Parser(), $data, 0, 1);
            $this->assertTrue($bool->value());
        }
    }
    
    /**
     * @test
     */
    public function testParseFalse()
    {
        $data = chr(0x00);
        
        $bool = Boolean::parse(new Parser(), $data, 0, 1);
        $this->assertFalse($bool->value());
    }
    
    /**
     * @test
     */
    public function testParseInvalidLength()
    {
        $this->expectException(\Exception::class);
        $data = "invalid";
        Boolean::parse(new Parser(), $data, 0, \strlen($data));
    }
}
