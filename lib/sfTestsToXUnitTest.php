<?php

/**
 * Executes a specific test and parses the output
 * 
 * @package sfTestsToXUnitPlugin
 * @author Stephen Melrose <stephen@sekka.co.uk>
 */
class sfTestsToXUnitTest
{
	// ----------------------------------------------------
	// Constants
	// ----------------------------------------------------
	
	const DOM_TAG_NAME = 'testsuite';
	const DOM_ATTR_NAME = 'name';
	const DOM_ATTR_TOTAL_TESTS = 'tests';
	const DOM_ATTR_TOTAL_FAILURES = 'failures';
	const DOM_ATTR_TOTAL_ERRORS = 'errors';
	const DOM_ATTR_EXECUTION_TIME = 'time';
	
	const LINE_PREFIX_FAIL = 'not ok';
	const LINE_PREFIX_INFO = '\#[\s]{4}';
	const LINE_PREFIX_PASS = 'ok';
	
	// ----------------------------------------------------
	// Properties
	// ----------------------------------------------------
	
	/**
	 * The current test case
	 * @var sfTestsToXUnitTestCase
	 */
	private $currentTest;
	
	/**
	 * Whether an error occurred while running the test
	 * @var boolean
	 */
	private $errorDetected;
	
	/**
	 * The time taken to execute the test
	 * @var float
	 */
	private $executionTime;
	
	/**
	 * The output generator of the test
	 * @var sfTestsToXUnitOutput
	 */
	private $outputGenerator;
	
	/**
	 * The path to the PHP executable
	 * @var string
	 */
	private $phpcli;
	
	/**
	 * The raw output of the test file
	 * @var string
	 */
	private $rawOutput;
	
	/**
	 * The path to the test file
	 * @var string
	 */
	private $testFilePath;
	
	/**
	 * Information on the test cases that have been run
	 * @var array
	 */
	private $testCases;
	
	/**
	 * The number of test cases that have failed
	 * @var integer
	 */
	private $testCasesFailed;
	
	/**
	 * The number of test cases that have passed
	 * @var integer
	 */
	private $testCasesPassed;
	
	// ----------------------------------------------------
	// Public API
	// ----------------------------------------------------
	
	/**
	 * Creates a new sfTestsToXUnitTest
	 * 
	 * @param string $testFilePath The path to the test file
	 * @param string $phpcli An optional path to the PHP executable
	 */
	public function __construct($testFilePath, $phpcli = NULL)
	{
		// Store
		$this->testFilePath = $testFilePath;
		$this->phpcli = $phpcli == NULL ? sfToolkit::getPhpCli() : $phpcli;
	}
	
	/**
	 * Converts the test into a DOMElement and returns it
	 * 
	 * @return DOMElement
	 */
	public function convertToDOM()
	{
		// Create temp DOMDocument so the DOMElement won't be read only
		$domDocument = &$this->getOutputGenerator()->getDOMDocument();
		
		// Create the DOMElement for the test
		$domElement = $domDocument->createElement(self::DOM_TAG_NAME);
		$domElement->setAttribute(self::DOM_ATTR_NAME, $this->getName());
		$domElement->setAttribute(self::DOM_ATTR_TOTAL_TESTS, $this->getTotalTestCases());
		$domElement->setAttribute(self::DOM_ATTR_TOTAL_FAILURES, $this->getTotalTestCasesFailed());
		$domElement->setAttribute(self::DOM_ATTR_TOTAL_ERRORS, ($this->ifErrorDetected() ? 1 : 0));
		$domElement->setAttribute(self::DOM_ATTR_EXECUTION_TIME, round($this->getExecutionTime(), 4));
		
		// For each test case
		foreach($this->testCases as $testCase)
		{
			// Add it to the DOMElement
			$domElement->appendChild($testCase->convertToDOM());
		}
		
		// Return
		return $domElement;
	}
	
	/**
	 * Runs the test and parses the output
	 * 
	 * @param sfTestsToXUnitOutput $outputGenerator The sfTestsToXUnitOutput generator that will format the test results
	 */
	public function executeTest(sfTestsToXUnitOutput $outputGenerator)
	{
		// Store the generator
		$this->outputGenerator = $outputGenerator;
		
		// Add the test to the output generator
		$this->outputGenerator->addTest($this);
		
		// Run the test
		$this->runTest();
	}
	
	/**
	 * Gets the execution time of the test
	 * 
	 * @return float
	 */
	public function getExecutionTime()
	{
		return $this->executionTime;
	}
	
	/**
	 * Gets the name of the test
	 * 
	 * @return string
	 */
	public function getName()
	{
		return str_replace('.php', '', basename($this->testFilePath));
	}
	
	/**
	 * Gets the output generator of the test
	 * 
	 * @return sfTestsToXUnitOutput
	 */
	public function getOutputGenerator()
	{
		return $this->outputGenerator;
	}
	
	/**
	 * Gets the total number of test cases that failed
	 * 
	 * @return integer
	 */
	public function getTotalTestCasesFailed()
	{
		return $this->testCasesFailed;
	}
	
	/**
	 * Gets the total number of test cases run
	 * 
	 * @return integer
	 */
	public function getTotalTestCases()
	{
		if (count($this->testCases) < 1 && $this->ifErrorDetected())
		{
			return 1;
		}
		else
		{
			return count($this->testCases);
		}
	}
	
	/**
	 * Determines if an error occurred while running the test
	 * 
	 * @return boolean
	 */
	public function ifErrorDetected()
	{
		return $this->errorDetected;
	}
	
	// ----------------------------------------------------
	// Internals
	// ----------------------------------------------------
	
	/**
	 * Runs the test, stores the output and parses it
	 */
	private function runTest()
	{
		// Init
		$this->errorDetected = false;
		$this->testCases = array();
		$this->testCasesFailed = 0;
		$this->testCasesPassed = 0;
		
		// Start recording
		ob_start();
		$timerStart = microtime(true);
		
		// Run the test
		passthru(sprintf('%s "%s" 2>&1', $this->phpcli, $this->testFilePath), $return);
		
		// Stop recording
		$timerEnd = microtime(true);
	    $this->rawOutput = ob_get_contents();
	    ob_end_clean();
	    
	    // Calculate the execution time of the test
	    $this->executionTime = $timerEnd - $timerStart;
	    
	    // Parse the output
	    $this->parseOutput();
	}
	
	/**
	 * Parses the raw output of the test
	 */
	private function parseOutput()
	{
		// Parse each line of the output
		$outputLines = explode("\n", $this->rawOutput);
		foreach($outputLines as $outputLine)
		{
			$this->parseLine(trim($outputLine));
		}
		
		// Check that the test finished successfully
		if (!preg_match("/Looks like (everything went fine|you failed [0-9]+ tests of [0-9]+)\./i", $this->rawOutput))
		{
			// Set that an error occurred if not
			$this->errorDetected = true;
		}
	}
	
	/**
	 * Parses a line of the raw output form the test
	 * 
	 * @param string $line The line from the raw output
	 */
	private function parseLine($line)
	{
		// If the line designates a passed test
		if (preg_match("/^".self::LINE_PREFIX_PASS."/i", $line))
		{
			$this->reportPass($line);
		}
		// If the line designates a failed test
		else if (preg_match("/^".self::LINE_PREFIX_FAIL."/i", $line))
		{
			$this->reportFail($line);
		}
		// If the line designates information about a test
		else if (preg_match("/^".self::LINE_PREFIX_INFO."/i", $line))
		{
			$this->reportInfo($line);
		}
	}
	
	/**
	 * Reports a pass
	 * 
	 * @param integer $testNumber The test number that has passed
	 */
	private function reportPass($line)
	{
		// Create the test case
		$this->createTestCase(
			sfTestsToXUnitUtil::getTestNumberFromLine($line), 
			sfTestsToXUnitUtil::getTestNameFromLine($line),
			sfTestsToXUnitTestCase::STATE_PASSED
		);
		
		// Log fail
		$this->testCasesPassed++;
	}
	
	/**
	 * Reports a fail
	 * 
	 * @param integer $testNumber The test number that has failed
	 */
	private function reportFail($line)
	{
		// Create the test case
		$this->createTestCase(
			sfTestsToXUnitUtil::getTestNumberFromLine($line), 
			sfTestsToXUnitUtil::getTestNameFromLine($line),
			sfTestsToXUnitTestCase::STATE_FAILED
		);
		
		// Log fail
		$this->testCasesFailed++;
	}
	
	/**
	 * Reports information about a test
	 * 
	 * @param string $line The raw output line with the information
	 */
	private function reportInfo($line)
	{
		// Check to see if there is a current test case
		if (!($this->currentTest instanceof sfTestsToXUnitTestCase))
		{
			return false;
		}
		
		// Format the info
		$lineInfo = trim(substr($line, 1));
		
		// Add to test case
		$this->currentTest->addInfo($lineInfo);
	}
	
	/**
	 * Creates a new test case for the test
	 * 
	 * @param integer $testNumber The test number of the test case
	 * @param integer $state The state of the test case
	 */
	private function createTestCase($testNumber, $name, $state)
	{
		// Check if a test case already exists
		if (array_key_exists($testNumber, $this->testCases) && ($this->testCases[$testNumber] instanceof sfTestsToXUnitTestCase))
		{
			return false;
		}
		
		// Create and store the test case
		$this->testCases[$testNumber] = $this->currentTest = new sfTestsToXUnitTestCase($this, $testNumber, $name, $state);
	}
}