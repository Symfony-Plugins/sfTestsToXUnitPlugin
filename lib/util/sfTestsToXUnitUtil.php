<?php

class sfTestsToXUnitUtil
{
	// ----------------------------------------------------
	// Public Static API
	// ----------------------------------------------------
	
	/**
	 * Parses out the test name from the a raw output line
	 * 
	 * @param string $line The line of the raw output
	 * @return string
	 */
	public static function getTestNameFromLine($line)
	{
		// Validate
		if (!is_string($line))
		{
			return false;
		}
		
		// Get the test name
		if (!preg_match("/ - ([^\n]+$)/i", $line, $matches) || !array_key_exists(1, $matches))
		{
			return false;
		}
		
		// Return
		return $matches[1];
	}
	
	/**
	 * Parses out the test number from a raw  output line
	 * 
	 * @param string $line The line of the raw output
	 * @return integer
	 */
	public static function getTestNumberFromLine($line)
	{
		// Validate
		if (!is_string($line))
		{
			return false;
		}
		
		// Get the test number
		if (!preg_match("/^(ok|not ok) ([0-9]+)/i", $line, $matches) || !array_key_exists(2, $matches))
		{
			return false;
		}
		
		// Return
		return (int) $matches[2];
	}
}