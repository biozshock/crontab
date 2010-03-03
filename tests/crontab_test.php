<?php
include_once APPPATH . 'file_helper.php';
include_once APPPATH . 'helpers.php';
include_once APPPATH . 'libraries/crontab.php';
include_once APPPATH . 'config/constants.php';

class Crontab_test extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
		require APPPATH . 'config/crontab.php';
		$this->config = $config;
		
		$crontab = new Crontab($this->config);
		$crontab->add_job('* * * * *', 'init');
	}

	function tearDown()
	{
		@unlink($this->config['cronfile']);
	}

	function test_construct()
	{
		$crontab = new Crontab($this->config);
		foreach ($this->config as $key => $item) {
			$this->assertTrue(isset($crontab->$key));
		}
	}
	
	function test_add_job()
	{
		$time = '0 * * * *';
		$action = 'test_add_job';
		
		$crontab = new Crontab($this->config);
		$crontab->add_job($time, $action);
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		
		$this->assertTrue(in_array($job, $file_items));
	}
	
	function test_add_job_duplicate()
	{
		$time = '0 * * * *';
		$action = 'test_add_job';
		
		$crontab = new Crontab($this->config);
		$crontab->add_job($time, $action);
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		
		$this->assertTrue(in_array($job, $file_items));
		
		$crontab->add_job($time, $action);
		
		$this->assertEquals($file_items, explode("\n", read_file($crontab->cronfile)));
	}
	
	function test_add_job_integer()
	{
		$crontab = new Crontab($this->config);
		
		$time_int = time();
		$time = date($crontab->crontime, $time_int + $crontab->time_offset * 60 * 60);
		$action = 'test_add_job';
		
		
		$crontab->add_job($time_int, $action);
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		
		$this->assertTrue(in_array($job, $file_items));
	}
	
	function test_remove_job_integer()
	{
		$crontab = new Crontab($this->config);
		
		$time_int = time();
		$time = date($crontab->crontime, $time_int + $crontab->time_offset * 60 * 60);
		$action = 'test_add_job';
		
		$crontab->add_job($time_int, $action);
		$file_items = explode("\n", read_file($crontab->cronfile));
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertTrue(in_array($job, $file_items));
		
		//actual test
		$crontab->remove_job($time_int, $action);
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertFalse(in_array($job, $file_items));
	}
	
	function test_remove_job()
	{
		$crontab = new Crontab($this->config);
		
		$time = '0 * * * *';
		$action = 'test_add_job';
		
		$crontab->add_job($time, $action);
		$file_items = explode("\n", read_file($crontab->cronfile));
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertTrue(in_array($job, $file_items));
		
		//actual test
		$crontab->remove_job($time, $action);
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertFalse(in_array($job, $file_items));
	}
	
	function test_replace_time()
	{
		$crontab = new Crontab($this->config);
		
		$time = '0 * * * *';
		$time_to = '0 12 * * *';
		$action = 'test_replace_time';
		
		$crontab->add_job($time, $action);
		$file_items = explode("\n", read_file($crontab->cronfile));
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertTrue(in_array($job, $file_items));
		
		//actual test
		$crontab->replace_time($time, $time_to, $action);
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertFalse(in_array($job, $file_items));
		
		$job = $time_to . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertTrue(in_array($job, $file_items));
	}
	
	function test_remove_all_jobs()
	{
		$crontab = new Crontab($this->config);
		
		$times = array('0 * * * *', '0 12 * * *', '0 12 * 2 *');
		$action = 'test_remove_all';
		
		foreach ($times as $time) {
			$crontab->add_job($time, $action);
		}
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		foreach ($times as $time) {
			$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
			$this->assertTrue(in_array($job, $file_items));
		}
		
		//actual test
		$crontab->remove_all_jobs($action);
		
		$file_items = explode("\n", read_file($crontab->cronfile));
		$job = $time . ' ' . $crontab->php_path . ' ' . $crontab->cli_path . $action;
		$this->assertFalse(in_array($job, $file_items));
	}
}