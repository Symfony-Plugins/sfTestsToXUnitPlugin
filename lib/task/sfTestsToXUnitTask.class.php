<?php

/**
 * Task to execute the sfTestsToXUnit testing
 * 
 * @package sfTestsToXUnitPlugin
 * @author Stephen Melrose <stephen@sekka.co.uk>
 */
class sfTestsToXUnitTask extends sfBaseTask
{
	// ----------------------------------------------------
	// Internals
	// ----------------------------------------------------
	
	protected function configure()
	{
		$this->aliases = array('test-to-xunit');
		
		$this->addOptions(array(
			new sfCommandOption('path', null, sfCommandOption::PARAMETER_REQUIRED, 'Path to the output file', sfConfig::get('sf_log_dir') . DIRECTORY_SEPARATOR . 'xunit.xml'),
			new sfCommandOption('phpcli', null, sfCommandOption::PARAMETER_REQUIRED, 'Path to the PHP executable', NULL)
		));

		$this->namespace = 'test';
		$this->name = 'all-to-xunit';
		$this->briefDescription = 'Runs all tests and formats the output in XUnit format for CruiseControl, phpUnderControl, etc';
		$this->detailedDescription = 'Runs all tests and formats the output in XUnit format for CruiseControl, phpUnderControl, etc';
	}

	protected function execute($arguments = array(), $options = array())
	{
		// Get the list of test files
		$finder = sfFinder::type('file')->follow_link()->name('*Test.php');
		$testFiles = $finder->in(sfConfig::get('sf_test_dir'));
		
		// Check to see if any test files were found
		if (!is_array($testFiles) || count($testFiles) < 1)
		{
			throw new Exception('You must register some test files before running them!');
		}
		
		// Create a new output generator
		$outputGenerator = new sfTestsToXUnitOutput($options['path']);
		
		// For each test
		foreach($testFiles as $testFile)
		{
			// Create a new test
			$test = new sfTestsToXUnitTest($testFile, $options['phpcli']);
			$test->executeTest($outputGenerator);
		}
		
		// Output the tests information in XUnit format
		$outputGenerator->output();
	}
}
