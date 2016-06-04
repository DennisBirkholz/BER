<?php
/*
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber;

/**
 * Class to hold BER specific constants
 * 
 * @author Dennis Birkholz <dennis@birkholz.biz>
 */
abstract class Constants
{
    /**
     * Type is a primitive type
     */
    const T_PRIMITIVE = 'Primitive';
    
    /**
     * Type is a constructed type
     */
    const T_CONSTRUCTED = 'Constructed';
    
    /**
     * Type has universal class
     */
    const C_UNIVERSAL = 'Universal';
    
    /**
     * Type has application class
     */
    const C_APPLICATION = 'Application';
    
    /**
     * Type has context specific class
     */
    const C_CONTEXTSPECIFIC = 'ContextSpecific';
    
    /**
     * Type has private class
     */
    const C_PRIVATE = 'Private';
    
    const BIT1 =   1;
    const BIT2 =   2;
    const BIT3 =   4;
    const BIT4 =   8;
    const BIT5 =  16;
    const BIT6 =  32;
    const BIT7 =  64;
    const BIT8 = 128;
    
    const NOT_BIT1 = (~self::BIT1 & 0xFF);
    const NOT_BIT2 = (~self::BIT2 & 0xFF);
    const NOT_BIT3 = (~self::BIT3 & 0xFF);
    const NOT_BIT4 = (~self::BIT4 & 0xFF);
    const NOT_BIT5 = (~self::BIT5 & 0xFF);
    const NOT_BIT6 = (~self::BIT6 & 0xFF);
    const NOT_BIT7 = (~self::BIT7 & 0xFF);
    const NOT_BIT8 = (~self::BIT8 & 0xFF);
}
