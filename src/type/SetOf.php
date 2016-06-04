<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

class SetOf extends SequenceOf
{
	const TYPE	= self::T_CONSTRUCTED;
	const CLS	= self::C_UNIVERSAL;
	const TAG	= 17;
}
