<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

// Only encoding octetstrings as primitive and not constructed
class UTF8String extends OctetString
{
	const TAG = 12;
}
