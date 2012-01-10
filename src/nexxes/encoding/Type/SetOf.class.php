<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_SetOf extends BER_Type_SequenceOf {
	const TYPE	= BER::TYPE_CONSTRUCTED;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 17;
}

?>