<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;

class Set extends Sequence {
	const TYPE	= self::T_CONSTRUCTED;
	const CLS	= self::C_UNIVERSAL;
	const TAG	= 17;
	
	protected static $randomOrder = true;
}
