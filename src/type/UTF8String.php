<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;

// Only encoding octetstrings as primitive and not constructed
class UTF8String extends OctetString {
	const TAG = 12;
}
