<?php
/**
 * File for crontab to write
 * @var string
 */
$config['cronfile'] = APPPATH . '/crontab';

/**
 * PHP executable path
 * @var string
 */
$config['php_path'] = '/usr/bin/php';

/**
 * Crontab default format for date() function
 * @var string
 */
$config['crontime'] = 's i G j n';

/**
 * CLI scripts path
 * @var string
 */
$config['cli_path'] = APPPATH;

/**
 * Time offset.
 * 
 * This config variable can be used when your server has different
 * timezone from timezone of tasks yo0u want to be cheduled.
 * e.g. server is in -5 timezone. backend in +1 timezone.
 * in backend you setting cronjob for 00:00:00,
 * but for server this would be 14:00:00 
 * @var int
 */
$config['time_offset'] = -6;

$config['cronjob_renew_feed'] = '/cli/renew_feed.php';