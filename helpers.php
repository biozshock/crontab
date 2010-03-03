<?php
if (!function_exists('log_message')) {
	function log_message($serveity = 'get', $message = '') {
		static $messages;
		
		$messages[$serveity][] = $message;
		
		return $messages;
	}
}