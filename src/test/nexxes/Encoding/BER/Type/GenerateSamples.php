#!/usr/bin/php
<?php

define('SAMPLE_DIR',  __DIR__ . DIRECTORY_SEPARATOR . 'samples');

class IntegerSample {
	// Minimal number to get the value from
	private $min = 0x00;
	
	// Maximal number to get the value from
	private $max = 0x7F;
	
	// Sample value
	private $value = 0;
	
	// The encoded value
	private $data = '';
	
	private $negative = false;
	
	// Number of bytes to use, between 1 and PHP_INT_SIZE (4 on 32bit, 8 on 64bit)
	private $bytes = 1;
	
	// Store samples here
	private $sampledir = SAMPLE_DIR;
	
	public function __construct($bytes = 1, $negative = false) {
		print 'Writing valid ' . ($negative ? 'negative ' : '') . 'integer sample with ' . $bytes . ' bytes ...';
		flush();
		
		$this->bytes = $bytes;
		$this->negative = $negative;
		
		$this->calcBoundaries();
		$this->generateValue();
		$this->encodeValue();
		$this->writeSample();
		print "\r" . 'Wrote valid integer sample with ' . $bytes . ' bytes: ' . str_pad(($negative ? '-' : ' ') . $this->value, 20, ' ', STR_PAD_LEFT) . ' ';
		
		$min = str_pad(($negative ? '-' : ' ') . $this->min, 20, ' ', STR_PAD_LEFT);
		$max = str_pad(($negative ? '-' : ' ') . $this->max, 20, ' ', STR_PAD_LEFT);
		
		print ($negative ? '['.$max.','.$min.']' : '['.$min.','.$max.']') . "\n";
	}
	
	/**
	 * Generate boundaries: minimum should be the smallest number with $bytes bytes
	 *  and maximum the greatest number with $bytes bytes and the most significant bit set to zero (make it a positive number)
	 */
	private function calcBoundaries() {
		$min = 0x00;
		$max = 0x7F;
		
		for ($bytes = 2; $bytes <= $this->bytes; $bytes++) {
			$min <<= 8;
			$min += 0xFF;
			
			$max <<=8;
			$max += 0xFF;
		}
		
		if ($min > 0) { $min += 1; }
		
		$this->min = $min;
		$this->max = $max;
	}
	
	private function generateValue() {
		$this->value = rand($this->min, $this->max);
	}
	
	private function encodeValue() {
		$str = '';
		
		// Calculate the 2 complement of the number
		// If negative, invert and then add 1
		// Overflows are ignored here
		if ($this->negative) {
			$val = ~$this->value;
			$val += 1;
		} else {
			$val = $this->value;
		}
		
		// Encode the value
		for ($byte=0; $byte<$this->bytes; $byte++) {
			$str = chr($val & 0xFF) . $str;
			$val >>= 8;
		}
		
		$this->data = $str;
	}
	
	private function writeSample() {
		if (!is_dir($this->sampledir)) { mkdir($this->sampledir); }
		
		$fname = $this->sampledir . DIRECTORY_SEPARATOR . 'Integer_valid_' . $this->bytes . 'byte_' . ($this->negative ? '-' : '') . $this->value;
		$fp = fopen($fname, 'w');
		fwrite($fp, $this->data);
		fclose($fp);
	}
}


// Generate one sample for each number of bytes up to PHP_INT_SIZE 
for ($bytes=1; $bytes<=PHP_INT_SIZE; $bytes++) {
	new IntegerSample($bytes, false);
	new IntegerSample($bytes, true);
}

?>