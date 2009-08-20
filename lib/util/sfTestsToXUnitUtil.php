<?php

class sfTestsToXUnitUtil
{
	// ----------------------------------------------------
	// Public Static API
	// ----------------------------------------------------
	
	/**
	 * Parses out the test number from an output line
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