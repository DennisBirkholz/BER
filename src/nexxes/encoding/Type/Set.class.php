<?php
/* $Id$
 * $URL$
 * $Copyright$ */

class BER_Type_Set extends BER_Type_Sequence {
	const TYPE	= BER::TYPE_CONSTRUCTED;
	const CLS	= BER::CLASS_UNIVERSAL;
	const TAG	= 17;
	
	protected static $randomOrder = true;
}

?>