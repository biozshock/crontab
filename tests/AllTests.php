<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

if (!defined('APPPATH')) {
	define('APPPATH', realpath(dirname(__FILE__) . '/..') . '/');
}

if (!defined('BASEPATH')) {
	define('BASEPATH', realpath(dirname(__FILE__) . '/..') . '/');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once dirname(__FILE__) . '/crontab_test.php';


class AllTests extends PHPUnit_Framework_TestSuite
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}
	
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Crontab testsuite');
		$suite->addTestSuite('Crontab_test');
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}