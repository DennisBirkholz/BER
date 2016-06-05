<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace DennisBirkholz\BER\type;

class EnumeratedTest extends \PHPUnit_Framework_TestCase
{
    public function testValidChoices()
    {
        for ($i=0; $i<count(EnumeratedMock::$choices); $i++) {
            $mock = new EnumeratedMock($i);
            
            $this->assertEquals($mock->value(), EnumeratedMock::$choices[$i]);
            $this->assertEquals($mock->value(true), $i);
            
            $mock = new EnumeratedMock(EnumeratedMock::$choices[$i]);
            
            $this->assertEquals($mock->value(), EnumeratedMock::$choices[$i]);
            $this->assertEquals($mock->value(true), $i);
        }
    }
    
    /**
     * @test
     */
    public function testIndexUnderflow()
    {
        $this->expectException(\Exception::class);
        new EnumeratedMock(-1);
    }
    
    /**
     * @test
     */
    public function testIndexOverflow()
    {
        $this->expectException(\Exception::class);
        new EnumeratedMock(count(EnumeratedMock::$choices));
    }
    
    /**
     * @test
     */
    public function testIllegalChoice()
    {
        $this->expectException(\Exception::class);
        new EnumeratedMock('foobar');
    }
}
