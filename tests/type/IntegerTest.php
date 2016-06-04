<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

class IntegerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify that all valid integer samples are parsed to match their actual value
     */
    public function testParseValidSamples()
    {
        $dir = new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'samples');

        foreach ($values = new \RegexIterator($dir, '/^Integer_valid_/') AS $file) {
            preg_match('/^Integer_valid_([0-9]+)byte_(-?[0-9]+)$/', $file, $matches);
            $bytes = (int)$matches[1];
            $value = (int)$matches[2];
            
            $data = file_get_contents($file->getPathName());
            
            $int = new Integer();
            $int->parse($data, 2, $bytes); // Need to skip 2 from the beginning to ignore tag, class, length, etc
            
            $this->assertEquals($int->value(), $value);
        }
    }

    /**
     * Verify that integer samples are encoded to valid values
     * 
     * added case 128: must be encoded in two bytes (a zero byte must be padded so it is not seen as -128)
     * added case -255: must be encoded in two bytes (a 0xFF byte must be padded so it is not seen as 1)
     */
    public function testEncodeValidSamples()
    {
        $dir = new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'samples');
        
        foreach ($values = new \RegexIterator($dir, '/^Integer_valid_/') AS $file) {
            preg_match('/^Integer_valid_([0-9]+)byte_(-?[0-9]+)$/', $file, $matches);
            $bytes = (int)$matches[1];
            $value = (int)$matches[2];
            
            $reference = file_get_contents($file->getPathName());
            
            $int = new Integer();
            $int->init($value);
            
            $encoded = $int->encode();
            
            $this->assertEquals(mb_strlen($reference, 'ASCII'), mb_strlen($encoded, 'ASCII'), "Encoded value $value, expecting $bytes bytes, got " . mb_strlen($encoded, 'ASCII') . ' bytes.');
            
            $this->assertEquals($reference, $encoded, "Encoded value $value does not match reference.\nReference: " . $this->dump($reference) . "\nEncoded:   " . $this->dump($encoded));
        }
    }

    private function dump($string)
    {
        $r = '';

        for ($i=0; $i<mb_strlen($string, 'ASCII'); $i++) {
            $r .= decbin(ord($string[$i])) . ' ';
        }

        return $r;
    }
}
