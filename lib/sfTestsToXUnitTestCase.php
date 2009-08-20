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
	
	const DOM_TAG_NAME = 'testcase';
	const DOM_ATTR_NAME = 'name';
	const DOM_ATTR_EXECUTION_TIME = 'time';
	const DOM_FAILURE_TAG_NAME = 'failure';
	
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
	 * The name of the test case
	 * @var string
	 */
	private $name;
	
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
	public function __construct(&$test, $testNumber, $name, $state)
	{
		// Store
		$this->test = $test;
		$this->testNumber = $testNumber;
		$this->name = $name;
		$this->state = $state;
	}
	
	/**
	 * Adds information to the test case
	 * 
	 * @param string $info Information to add
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
	}
	
	/**
	 * Converts the test case into a DOMElement and returns it
	 * 
	 * @return DOMElement
	 */
	public function convertToDOM()
	{
		// Create temp DOMDocument so the DOMElement won't be read only
		$domDocument = &$this->getTest()->getOutputGenerator()->getDOMDocument();
		
		// Create the DOMElement for the test case
		$domElement = $domDocument->createElement(self::DOM_TAG_NAME);
		$domElement->setAttribute(self::DOM_ATTR_NAME, 'Test ' . $this->getTestNumber());
		$domElement->setAttribute(self::DOM_ATTR_EXECUTION_TIME, round($this->getTest()->getExecutionTime() / $this->getTest()->getTotalTestCases(), 4));
		
		// If the test failed and there is info
		if ($this->getState() == self::STATE_FAILED && $this->getInfo() != NULL)
		{
			// Create a failure node
			$failureNode = $domDocument->createElement(self::DOM_FAILURE_TAG_NAME, $this->getName() . "\n" . $this->getInfo());
			
			// Add it to the DOMElement
			$domElement->appendChild($failureNode);
		}
		
		// Return
		return $domElement;
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
	 * Gets the name of the test case
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
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