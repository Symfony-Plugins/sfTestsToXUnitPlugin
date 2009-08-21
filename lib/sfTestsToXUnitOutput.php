<?php

/**
 * Generates an output in XUnit format from the executed tests data
 * 
 * @package sfTestsToXUnitPlugin
 * @author Stephen Melrose <stephen@sekka.co.uk>
 */
class sfTestsToXUnitOutput
{
	// ----------------------------------------------------
	// Constants
	// ----------------------------------------------------
	
	const DOM_TAG_NAME = 'testsuites';
	
	// ----------------------------------------------------
	// Properties
	// ----------------------------------------------------
	
	/**
	 * The DOMDocument output
	 * @var DOMDocument
	 */
	private $domDocument;
	
	/**
	 * The path to the output file
	 * @var unknown_type
	 */
	private $outputPath;
	
	/**
	 * The tests that have been run
	 * @var array
	 */
	private $tests;
	
	// ----------------------------------------------------
	// Public API
	// ----------------------------------------------------
	
	/**
	 * Creates a new sfTestsToXUnitOutput
	 * 
	 * @param string $outputPath The path to the output file
	 */
	public function __construct($outputPath)
	{
		// Store
		$this->outputPath = $outputPath;
		$this->tests = array();
		
		// Init
		$this->domDocument = new DOMDocument('1.0', 'utf-8');
	}
	
	/**
	 * Adds a sfTestsToXUnitTest to the output generator
	 * 
	 * @param sfTestsToXUnitTest $test The sfTestsToXUnitTest test to add
	 */
	public function addTest(sfTestsToXUnitTest $test)
	{
		// Store the test
		array_push($this->tests, $test);
	}
	
	/**
	 * Gets the output DOMDocument
	 * 
	 * @return DOMDocument
	 */
	public function getDOMDocument()
	{
		return $this->domDocument;
	}
	
	/**
	 * Outputs the test results to the specified XML file in XUnit format
	 */
	public function output()
	{
		// Generate the DOMDocument
		$domDocument = $this->generateDOM();
		
		// Save it to file
		$this->saveDOMDocumentToFile($domDocument);
	}
	
	// ----------------------------------------------------
	// Internals
	// ----------------------------------------------------
	
	/**
	 * Generates the DOMDocument and returns it
	 * 
	 * @return DOMDocument
	 */
	private function generateDOM()
	{
		// Check if the DOM has already been generated
		if ($this->getDOMDocument()->hasChildNodes())
		{
			return $this->getDOMDocument();
		}
		
		// Create the DOMElement for the tests
		$domElement = $this->getDOMDocument()->createElement(self::DOM_TAG_NAME);
		
		// For each test
		foreach($this->tests as $test)
		{
			// If the test actually has testcases (adding a testsuite with no tests causes errors in CruiseControl)
			if ($test->getTotalTestCases() > 0)
			{
				// Add it to the DOMElement
				$domElement->appendChild($test->convertToDOM());
			}
		}
		
		// Add the DOMElement to the document
		$this->getDOMDocument()->appendChild($domElement);
		
		// Return the DOMDocument
		return $this->getDOMDocument();
	}
	
	/**
	 * Saves a DOMDocument to the specified file path
	 * 
	 * @param DOMDocument $domDocument The DOMDocument to save
	 */
	private function saveDOMDocumentToFile(DOMDocument $domDocument)
	{
		// If the file doesn't exist
		if (!file_exists($this->outputPath))
		{
			// Get the folder
			$folder = substr($this->outputPath, 0, strlen($this->outputPath) - strlen(basename($this->outputPath)));
			if (!file_exists($folder) || !is_dir($folder))
			{
				throw new Exception('The target folder could not be determined.');
			}
			
			// Check it is writeable
			if (!is_writable($folder))
			{
				throw new Exception('The target folder can not be written to.');
			}
		}
		else
		{
			// Check it is writeable
			if (!is_writable($this->outputPath))
			{
				throw new Exception('The target output file can not be written to.');
			}
		}
		
		// Save the DOMDocument output to the file
		@file_put_contents($this->outputPath, $domDocument->saveXML());
	}
}