<?php
/**
 * Class for handling crontab jobs for CodeIgniter.
 * 
 * This classs provides handling of crontab jobs by performing unix command through exec.
 * Command to be executed `crontab $cronfile`.
 * This action WILL REPLACE ALL CRONTAB sheduled tasks for crontab for user under which your server runs,
 * so BE CAREFUL.
 * 
 * Please make sure:
 *  - you'r php exec is enabled
 *  - your server user has access to crontab
 *  - you know what you are doing
 * 
 * get latest version at git://github.com/biozshock/crontab.git
 * 
 * @author biozshock
 * @package Codeigniter
 * @subpackage Libraries
 */
class Crontab
{
	/**
	 * Options of crontab. Loaded from config file if any
	 * 
	 * @var array
	 */
	protected $_options;
	
	/**
	 * Constructor
	 * 
	 * @param array $config
	 * @return void
	 */
	public function __construct($config)
	{
		foreach ($config as $key => $value) {
			$this->$key = $value;
		}
		if (!defined('PHPUnit_MAIN_METHOD')) {
			$CI = &get_instance();
			$CI->load->helper('file');
		}
	}
	
	/**
	 * Adds job for action.
	 * 
	 * Action is a cli script you want to be executed
	 * 
	 * @param string/int $time time in crontab format
	 * 								if int then translate_timestamp function will be used with default format
	 * @param string $action action you want to be executed
	 * @return bool
	 */
	public function add_job($time, $action)
	{
		if (is_int($time)) {
			$time = $this->translate_timestamp($time);
		}
		if (!preg_match('~^([\d/\\*]+)\s([\d/\\*]+)\s([\d/\\*]+)\s([\d/\\*]+)\s([\d/\\*]+)$~', $time)) {
			log_message('DEBUG', 'Wrong preg for cron time ' . $time);
			return FALSE;
		}
		
		if (strpos($action, $this->cli_path) === FALSE) {
			$action = $this->cli_path . $action;
		}
		
		$jobs = explode("\n", read_file($this->cronfile));
		
		if (!is_array($jobs)) {
			$jobs = array();
		}
		
		$job = $time . ' ' .$this->php_path . ' ' . $action;
		
		if (!in_array($job, $jobs)) {
			$this->_write($job . "\n", FOPEN_READ_WRITE_CREATE);
		}
		
		return TRUE;
	}
	
	/**
	 * Removes cronjob for action at specified time
	 * 
	 * @param string/int $time time in crontab format
	 * 								if int then translate_timestamp function will be used with default format
	 * @param string $action action you want to be executed
	 * @return bool
	 */
	public function remove_job($time, $action)
	{
		if (is_int($time)) {
			$time = $this->translate_timestamp($time);
		}
		
		if (!preg_match('~^([\d/\\*]+)\s([\d/\\*]+)\s([\d/\\*]+)\s([\d/\\*]+)\s([\d/\\*]+)$~', $time)) {
			log_message('DEBUG', 'Wrong preg for cron time ' . $time);
			return FALSE;
		}
		
		if (strpos($action, $this->cli_path) === FALSE) {
			$action = $this->cli_path . $action;
		}
		
		$jobs = explode("\n", read_file($this->cronfile));
		
		if (!is_array($jobs)) {
			$jobs = array();
		}
		
		$job = $time . ' ' .$this->php_path . ' ' . $action;
		
		if (($index = array_search($job, $jobs)) !== FALSE) {
			unset($jobs[$index]);
		}
		
		$this->_write(implode("\n", $jobs));
		
		return TRUE;
	}
	
	/**
	 * Replaces time for action
	 * 
	 * @param string $from_time time in crontab format was sheduled 
	 * 							if int then translate_timestamp function will be used with default format
	 * @param string $to_time time in crontab format should be scheduled
	 * 							if int then translate_timestamp function will be used with default format
	 * @param string $action action you want to be executed
	 * @return bool
	 */
	public function replace_time($from_time, $to_time, $action)
	{
		$this->remove_job($from_time, $action);
		$this->add_job($to_time, $action);
	}
	
	/**
	 * Remove all jobs for action
	 * 
	 * @param string $action action you want to be executed
	 * @return void
	 */
	public function remove_all_jobs($action)
	{
		$jobs = explode("\n", read_file($this->cronfile));
		
		if (!is_array($jobs)) {
			$jobs = array();
		}
		
		if (strpos($action, $this->cli_path) === FALSE) {
			$action = $this->cli_path . $action;
		}
		
		foreach ($jobs as &$job) {
			if (preg_match('~' . $action . '$~', $job)) {
				$job = '----';
			}
		}
		
		$this->_write(str_replace("----\n", '', implode("\n", $jobs)));
	}
	
	/**
	 * Write to cronfile function
	 * 
	 * @param string $text text to write
	 * @param string $mode
	 * @return void
	 */
	protected function _write($text, $mode = FOPEN_WRITE_CREATE_DESTRUCTIVE)
	{
		log_message('DEBUG', 'Writing to cronfile: ' . $this->cronfile);
		write_file($this->cronfile, $text, $mode);
		$this->_exec('crontab ' . $this->cronfile);
	}
	
	/**
	 * Translate timestamp to cron time string
	 * 
	 * @param int $timestamp
	 * @param string $format format of cron time for date() function
	 * @return string
	 */
	public function translate_timestamp($timestamp, $format = '') // s i H d m ? Y  but w/o leading zeroz
	{
		if ($format == '') {
			$format = $this->crontime;
		}
		return date($format, $timestamp + ($this->time_offset * 60 * 60));
	}
	
	/**
	 * Exec of external program
	 * 
	 * @param string $command command to execute
	 * @return void
	 */
	protected function _exec($command)
	{
		$output = array();
		if (!defined('PHPUnit_MAIN_METHOD')) {
			exec($command, $output);
		} else {
			$output = array('called from phpunit');
		}
		log_message('DEBUG', 'Exec output: ' . implode("\n", $output));
	}
	
	public function __get($variable)
	{
		if (isset($this->$variable)) {
			return $this->_options[$variable];
		}
		
		return NULL;
	}
	
	public function __set($variable, $value)
	{
		$this->_options[$variable] = $value;
	}
	
	public function __isset($variable)
	{
		return isset($this->_options[$variable]);
	}
	
	public function __unset($variable)
	{
		if (isset($this->_options[$variable])) {
			unset($this->_options[$variable]);
		}
	}
}