<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace DennisBirkholz\BER\type;

use \DennisBirkholz\BER\Parser;

class ObjectIdentifierTest extends \PHPUnit_Framework_TestCase
{
    private $cases = [
        '0x06092a864886f70d01010b' => [1, 2, 840, 113549, 1, 1, 11],
        '0x06027703' => [2, 39, 3],
        '0x06027803' => [2, 40, 3],
        '0x06027903' => [2, 41, 3],
        '0x0603813403' => [2, 100, 3],
    ];
    
    /**
     * @test
     */
    public function testParse()
    {
        $parser = new Parser();
        
        foreach ($this->cases as $unparsed => $parsed) {
            $encoded = TestHelper::hex2str($unparsed);
            $oid = $parser->parse($encoded)[0];
            $this->assertInstanceOf(ObjectIdentifier::class, $oid);
            $this->assertSame($parsed, $oid->value());
        }
    }
    
    /**
     * @test
     */
    public function testEncode()
    {
        foreach ($this->cases as $unparsed => $parsed) {
            $encoded = TestHelper::hex2str($unparsed);
            $oid = new ObjectIdentifier($parsed);
            $this->assertSame($encoded, $oid->encode());
        }
    }
}
