<?php
/* $Id$
 * $URL$
 * $Copyright$ */

namespace {
	const BIT1 =   1;
	const BIT2 =   2;
	const BIT3 =   4;
	const BIT4 =   8;
	const BIT5 =  16;
	const BIT6 =  32;
	const BIT7 =  64;
	const BIT8 = 128;
}

namespace org\birkholz\Encoding\BER {

	/**
	* Wrapper for string length
	* Build workarounds here if mb_string function overloading is active and strlen() interprets $string as multibyte string
	* FIXME
	*/
	function strlen(&$string) {
		return \strlen($string);
	}

	/**
	* Wrapper for substring
	* Build workarounds here if mb_string function overloading is active and substr() interprets $string as multibyte string
	* FIXME
	*/
	function substr(&$string, $pos = 0, $length = null) {
		return \substr($string, $pos, (\is_null($length) ? \strlen($string) : $length));
	}

	abstract class Type {
		const T_PRIMITIVE = 'Primitive';
		const T_CONSTRUCTED = 'Constructed';

		const C_UNIVERSAL = 'Universal';
		const C_APPLICATION = 'Application';
		const C_CONTEXTSPECIFIC = 'ContextSpecific';
		const C_PRIVATE = 'Private';
		
		protected $value = null;
		
		/**
		* Set the values after creating the empty object
		*/
		public function init($value) {
			$this->value = $value;
		}
		
		/**
		* Create a new element from this data (type and length needs to be stripped before passing)
		*/
		abstract public function parse(&$data, $pos = 0, $length = null);
		
		/**
		* Get the value stored in that type
		*/
		public function value() {
			return $this->value;
		}
		
		/**
		* Override this function to encode the actual object data
		*/
		abstract protected function encodeData();
		
		/**
			* Generate the BER encoding of the object
			* 
			* Call the encode function of the supplied object
			* Calculate the length of the resulting encoding
			* Generate class, type and tag marker and length indicator
			* Return the complete encoding of obj
			*/
		public function encode() {
			$encoding = $this->encodeData();
			return $this->generateEncodingIdentifier() . $this->calculateEncodingLength($encoding) . $encoding;
		}
		
		protected function generateEncodingIdentifier() {
			$identifier = 0;
			
			if (static::TYPE == self::T_CONSTRUCTED) {
				$identifier |= BIT6;
			}
			
			if (static::CLS == self::C_PRIVATE) {
				$identifier |= (BIT8|BIT7);
			}
			
			elseif (static::CLS == self::C_CONTEXTSPECIFIC) {
				$identifier |= (BIT8);
			}
			
			elseif (static::CLS == self::C_APPLICATION) {
				$identifier |= (BIT7);
			}
			
			// Low tag, so only a single octet is needed for identifier
			if (static::TAG <= 30) {
				$identifier |= static::TAG;
				return chr($identifier);
			}
			
			// Indicate multi byte identifier
			$identifier |= (BIT5|BIT4|BIT3|BIT2|BIT1);
			$tag_num = static::TAG;
			$tag_str = chr($tag_num & 127);
			$tag_num >>= 7;
			
			while ($tag_num > 0) {
				$tag_str = chr(($tag_num & 127) | BIT8);
				$tag_num >>= 7;
			}
			
			return chr($identifier) . $tag_str;
		}
		
		protected function calculateEncodingLength(&$string) {
			$string_length = strlen($string);
			
			// Only one byte is needed for length
			if ($string_length < 128) {
				return chr($string_length);
			}
			
			// Using multibyte length 
			$return = '';
			
			do {
				$return = chr($string_length & 0xFF) . $return;
				$string_length >>= 8;
			} while ($string_length > 0);
			
			// BIT8 needs to be set as multi byte length indicator
			return chr(strlen($return) | BIT8) . $return;
		}
	} // End class Type
} // End namespace BER
