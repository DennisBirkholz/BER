<?php
/* $Id$
 * $URL$
 * $Copyright$ */

if (!defined('SLASH')) { define('SLASH', DIRECTORY_SEPARATOR); }

define('BIT1',   1);
define('BIT2',   2);
define('BIT3',   4);
define('BIT4',   8);
define('BIT5',  16);
define('BIT6',  32);
define('BIT7',  64);
define('BIT8', 128);

// Base class
require_once(dirname(__FILE__) . SLASH . 'BER.class.php');

// Types
require_once(dirname(__FILE__) . SLASH . 'Type' . '.interface.php');

require_once(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Placeholder' . '.class.php');

// The default universal types
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Boolean' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Integer' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'BitString' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'OctetString' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Null' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Real' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Enumerated' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'SequenceOf' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Sequence' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'Set' . '.class.php');
BER::register(dirname(__FILE__) . SLASH . 'Type' . SLASH . 'UTF8String' . '.class.php');

?>