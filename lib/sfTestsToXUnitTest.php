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
	
	const LINE_PREFIX_FAIL = 'not ok';
	const LINE_PREFIX_INFO = '#';
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
	 * The time taken to execute the test
	 * @var float
	 */
	private $executionTime;
	
	/**
	 * The raw output of the test file
	 * @var string
	 */
	private $rawOutput;
	
	/**
	 * The path to the PHP executable
	 * @var string
	 */
	private $phpcli;
	
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
	 * Runs the test and parses the output
	 * 
	 * @param sfTestsToXUnitOutput $outputGenerator The sfTestsToXUnitOutput generator that will format the test results
	 */
	public function executeTest(sfTestsToXUnitOutput $outputGenerator)
	{
		// Add the test to the output generator
		$outputGenerator->addTest($this);
		
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
	
	// ----------------------------------------------------
	// Internals
	// ----------------------------------------------------
	
	/**
	 * Runs the test, stores the output and parses it
	 */
	private function runTest()
	{
		// Init
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
	}
	
	/**
	 * Parses a line of the raw output form the test
	 * 
	 * @param string $line The line from the raw output
	 */
	private function parseLine($line)
	{
		// If the line designates a passed test
		if (substr($line, 0, 2) == self::LINE_PREFIX_PASS)
		{
			$this->reportPass(sfTestsToXUnitUtil::getTestNumberFromLine($line));
		}
		// If the line designates a failed test
		else if (substr($line, 0, 6) == self::LINE_PREFIX_FAIL)
		{
			$this->reportFail(sfTestsToXUnitUtil::getTestNumberFromLine($line));
		}
		// If the line designates information about a test
		else if (substr($line, 0, 1) == self::LINE_PREFIX_INFO)
		{
			$this->reportInfo($line);
		}
	}
	
	/**
	 * Reports a pass
	 * 
	 * @param integer $testNumber The test number that has passed
	 */
	private function reportPass($testNumber)
	{
		// Init the test case
		$this->initTestCase($testNumber, sfTestsToXUnitTestCase::STATE_PASSED);
		
		// Log fail
		$this->testCasesPassed++;
	}
	
	/**
	 * Reports a fail
	 * 
	 * @param integer $testNumber The test number that has failed
	 */
	private function reportFail($testNumber)
	{
		// Init the test case
		$this->initTestCase($testNumber, sfTestsToXUnitTestCase::STATE_FAILED);
		
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
	private function initTestCase($testNumber, $state)
	{
		// Create and store the test case
		$this->testCases[$testNumber] = $this->currentTest = new sfTestsToXUnitTestCase($this, $testNumber, $state);
	}
}