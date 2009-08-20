<?php

/**
 * Stores information about a specific test case
 * 
 * @package sfTestsToXUnitPlugin
 * @author Stephen Melrose <stephen@sekka.co.uk>
 */
class sfTestsToXUnitTestCase
{
	// ----------------------------------------------------
	// Constants
	// ----------------------------------------------------
	
	const STATE_FAILED = 0;
	const STATE_PASSED = 1;
	
	// ----------------------------------------------------
	// Properties
	// ----------------------------------------------------
	
	/**
	 * Information about the test case, e.g. error
	 * @var string
	 */
	private $info;
	
	/**
	 * The state of the test case
	 * @var integer
	 */
	private $state;
	
	/**
	 * The sfTestsToXUnitTest the test case is from
	 * @var sfTestsToXUnitTest
	 */
	private $test;
	
	/**
	 * The test number of the sfTestsToXUnitTest
	 * @var integer
	 */
	private $testNumber;
	
	// ----------------------------------------------------
	// Public API
	// ----------------------------------------------------
	
	/**
	 * Creates a new sfTestsToXUnitTestCase
	 * 
	 * @param sfTestsToXUnitTest $test The sfTestsToXUnitTest the test case is from
	 * @param integer $testNumber The test number of the sfTestsToXUnitTest
	 * @param string $state The state of the test case
	 */
	public function __construct(&$test, $testNumber, $state)
	{
		// Store
		$this->test = $test;
		$this->testNumber = $testNumber;
		$this->state = $state;
	}
	
	/**
	 * Adds information to the test case
	 * 
	 * @param string $info Information to add
	 * @return boolean
	 */
	public function addInfo($info)
	{
		// Add information
		if ($this->info == NULL)
		{
			$this->info = $info;
		}
		else
		{
			$this->info .= "\n" . $info;
		}
		
		// Return
		return true;
	}
	
	/**
	 * Gets the test case's information
	 * 
	 * @return string
	 */
	public function getInfo()
	{
		return $this->info;
	}
	
	/**
	 * Gets the state of the testcase
	 * 
	 * @return integer
	 */
	public function getState()
	{
		return $this->state;
	}
	
	/**
	 * Gets the test case's parent sfTestsToXUnitTest
	 * 
	 * @return sfTestsToXUnitTest
	 */
	public function getTest()
	{
		return $this->test;
	}
	
	/**
	 * Gets the test case's test number of the parent sfTestsToXUnitTest
	 * 
	 * @return integer
	 */
	public function getTestNumber()
	{
		return $this->testNumber;
	}
}