<?php
/**
 *	ethna_handle.php
 *
 *  Ethna Handle Gateway
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */
while (ob_get_level()) {
    ob_end_clean();
}

include_once('PEAR.php');
include_once('Console/Getopt.php');

// setup path seprator
if (!defined('PATH_SEPARATOR')) {
	if (OS_WINDOWS) {
		/** include_path separator(Windows) */
		define('PATH_SEPARATOR', ';');
	} else {
		/** include_path separator(Unix) */
		define('PATH_SEPARATOR', ':');
	}
}
$base = dirname(dirname(dirname(__FILE__)));
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "$base");

include_once('Ethna/Ethna.php');

// fetch arguments
$getopt =& new Console_Getopt();
$arg_list = $getopt->readPHPArgv();
array_shift($arg_list);

$ehm =& new Ethna_Handle_Manager();
if (count($arg_list) == 0) {
    usage($ehm);
    exit(1);
}

$id = array_shift($arg_list);

$handler =& $ehm->getHandler($id);
if (Ethna::isError($handler)) {
    printf("no such command: %s\n\n", $id);
    usage($ehm);
    exit(1);
}

// don't know what will happen:)
$handler->setArgList($arg_list);
$r = $handler->perform();
if (Ethna::isError($r)) {
    printf("error occured w/ command [%s]\n  -> %s\n\n", $id, $r->getMessage());
    if ($r->getCode() == 'usage') {
        $handler->usage();
    }
    exit(1);
}

/**
 *  usage
 */
function usage(&$ehm)
{
    $handler_list = $ehm->getHandlerList();
    printf("usage: ethna [command] [args...]\n\n");
    printf("available commands are as follows:\n\n");
    foreach ($handler_list as $handler) {
        printf("  %s -> %s\n", $handler->getId(), $handler->getDescription());
    }
}
?>
