<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace DennisBirkholz\BER\type;

use \DennisBirkholz\BER\Parser;

/**
 * @covers \DennisBirkholz\BER\type\BitString
 */
class BitStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $encoded;
    
    /**
     * @var string
     */
    private $decoded;
    
    public function setUp()
    {
        $this->encoded = TestHelper::hex2str('0x040A3B5F291CD0');
        $this->decoded = TestHelper::hex2str('0x0A3B5F291CD0');
    }
    
    /**
     * @test
     */
    public function testEncode()
    {
        $bitstring = new BitString($this->decoded, 4);
        $this->assertSame($this->encoded, $bitstring->encodeData());
    }
    
    /**
     * @test
     */
    public function testParse()
    {
        $bitstring = BitString::parse(new Parser(), $this->encoded);
        $this->assertInstanceOf(BitString::class, $bitstring);
        $this->assertSame($this->decoded, $bitstring->value());
    }
    
    /**
     * @test
     */
    public function testGetBit()
    {
        $bitstring = new BitString($this->decoded, 4);
        // 0x0 = 0000
        $this->assertFalse($bitstring->getBit(0));
        $this->assertFalse($bitstring->getBit(1));
        $this->assertFalse($bitstring->getBit(2));
        $this->assertFalse($bitstring->getBit(3));
        // 0xA = 1010
        $this->assertTrue($bitstring->getBit(4));
        $this->assertFalse($bitstring->getBit(5));
        $this->assertTrue($bitstring->getBit(6));
        $this->assertFalse($bitstring->getBit(7));
        // 0x3 = 0011
        $this->assertFalse($bitstring->getBit(8));
        $this->assertFalse($bitstring->getBit(9));
        $this->assertTrue($bitstring->getBit(10));
        $this->assertTrue($bitstring->getBit(11));
        // 0xB = 1011
        $this->assertTrue($bitstring->getBit(12));
        $this->assertFalse($bitstring->getBit(13));
        $this->assertTrue($bitstring->getBit(14));
        $this->assertTrue($bitstring->getBit(15));
        // 0xD = 1101
        $this->assertTrue($bitstring->getBit(40));
        $this->assertTrue($bitstring->getBit(41));
        $this->assertFalse($bitstring->getBit(42));
        $this->assertTrue($bitstring->getBit(43));
    }
    
    /**
     * @test
     */
    public function testInvalidBit1()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $bitstring = new BitString($this->decoded, 4);
        $this->assertFalse($bitstring->getBit(-1));
    }
    
    /**
     * @test
     */
    public function testInvalidBit2()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $bitstring = new BitString($this->decoded, 4);
        $this->assertFalse($bitstring->getBit(44));
    }
    
    /**
     * @test
     */
    public function testConstructorInvalidUnusedBitNegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        new BitString($this->decoded, -1);
    }
    
    /**
     * @test
     */
    public function testConstructorInvalidUnusedBitTooLarge()
    {
        $this->expectException(\InvalidArgumentException::class);
        new BitString($this->decoded, 8);
    }
    
    /**
     * @test
     */
    public function testParseInvalidUnusedBitNegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $encoded = $this->encoded;
        $encoded[0] = chr(-1);
        BitString::parse(new Parser(), $encoded);
    }
    
    /**
     * @test
     */
    public function testParseInvalidUnusedBitTooLarge()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $encoded = $this->encoded;
        $encoded[0] = chr(8);
        BitString::parse(new Parser(), $encoded);
    }
    
    public function testExport()
    {
        $bitstring = new BitString($this->decoded);
        
        $target  = sprintf("%-20s (%d)\n", '  BitString', 6);
        $target .= sprintf("   %04x %02x %02x %02x %02x %02x %02x\n", 0, ord($this->decoded[0]), ord($this->decoded[1]), ord($this->decoded[2]), ord($this->decoded[3]), ord($this->decoded[4]), ord($this->decoded[5]));
        
        $this->assertSame($target, $bitstring->export(2, 20));
    }
}
