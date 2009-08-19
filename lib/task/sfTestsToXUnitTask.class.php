<?php

/**
 * Task to execute the sfTestsToXUnit testing
 * 
 * @package sfTestsToXUnit
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
			new sfCommandOption('path', null, sfCommandOption::PARAMETER_REQUIRED, 'Path to the output file', sfConfig::get('sf_log_dir') . DIRECTORY_SEPARATOR . 'xunit.xml')
		));

		$this->namespace = 'test';
		$this->name = 'all-to-xunit';
		$this->briefDescription = 'Runs all tests and formats the output in XUnit format for CruiseControl, phpUnderControl, etc';
		$this->detailedDescription = 'Runs all tests and formats the output in XUnit format for CruiseControl, phpUnderControl, etc';
	}

	protected function execute($arguments = array(), $options = array())
	{
		// TODO
	}
}
