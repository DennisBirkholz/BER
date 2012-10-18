<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace org\birkholz\Encoding\BER\Type;

class SetOf extends SequenceOf {
	const TYPE	= self::T_CONSTRUCTED;
	const CLS	= self::C_UNIVERSAL;
	const TAG	= 17;
}
