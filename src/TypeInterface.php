<?php
/*
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber;

/**
 * @author Dennis Birkholz <dennis@birkholz.biz>
 */
interface TypeInterface
{
    /**
     * Create a new element from the supplied data.
     * The supplied parser is required so elements that can contain
     *  other elements can delegate parsing to the parser instance.
     * 
     * @param \dennisbirkholz\ber\Parser $parser
     * @param string $data
     * @return static
     */
    public static function parse(Parser $parser, $data);
    
    /**
     * Generate the BER encoding of the object
     * 
     * Call the encode function of the supplied object
     * Calculate the length of the resulting encoding
     * Generate class, type and tag marker and length indicator
     * Return the complete encoding of obj
     * 
     * @return string
     */
    public function encode();
    
    /**
     * Get the value contained in the element.
     */
    public function value();
}
