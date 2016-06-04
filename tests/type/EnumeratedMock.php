<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

class EnumeratedMock extends Enumerated {
	public static $choices = array(
		0	=> 'choice1',
		1	=> 'choice2',
		2	=> 'choice3',
	);
}
