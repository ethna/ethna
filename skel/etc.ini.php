<?php
/*
 * {$project_prefix}-ini.php
 *
 * update:
 */
$config = array(
	'debug'	=> false,
	'log_facility'  => 'echo',
	'log_level' => 'warning',
	'log_option' => 'pid,function,pos',
	'log_alert_level' => 'crit',
	'log_alert_mailaddress' => '',
	'log_filter_do' => '',
	'log_filter_ignore' => 'Undefined index.*%%.*tpl',
);
?>
