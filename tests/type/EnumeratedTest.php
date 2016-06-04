<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

class EnumeratedTest extends \PHPUnit_Framework_TestCase
{
    public function testValidChoices()
    {
        $mock = new EnumeratedMock();
        
        for ($i=0; $i<count(EnumeratedMock::$choices); $i++) {
            $mock->init($i);
            
            $this->assertEquals($mock->value(), EnumeratedMock::$choices[$i]);
            $this->assertEquals($mock->value(true), $i);
            
            $mock->init(EnumeratedMock::$choices[$i]);
            
            $this->assertEquals($mock->value(), EnumeratedMock::$choices[$i]);
            $this->assertEquals($mock->value(true), $i);
        }
    }
    
    /**
     * @expectedException Exception
     */
    public function testIndexUnderflow()
    {
        $mock = new EnumeratedMock();
        $mock->init(-1);
    }
    
    /**
     * @expectedException Exception
     */
    public function testIndexOverflow()
    {
        $mock = new EnumeratedMock();
        $mock->init(count(EnumeratedMock::$choices));
    }
    
    /**
     * @expectedException Exception
     */
    public function testIllegalChoice()
    {
        $mock = new EnumeratedMock();
        $mock->init('foobar');
    }
}
