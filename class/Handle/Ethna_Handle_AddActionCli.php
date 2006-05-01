<?php
// vim: foldmethod=marker
/**
 *	Ethna_Handle_AddActionCli.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */
include_once(ETHNA_BASE . '/class/Handle/Ethna_Handle_AddAction.php');

// {{{ Ethna_Handle_AddActionCli
/**
 *  add-action handler
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Handle_AddActionCli extends Ethna_Handle_AddAction
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new cli action (and an entry point) to project:\n    {$this->id} (-e) [action] ([project-base-dir])\n";
    }

    /**
     *  add action
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($action_name, $app_dir, $entry_point) = $r;

        $sg =& new Ethna_SkeltonGenerator();
        $r = $sg->generateActionSkelton($action_name, $app_dir, GATEWAY_CLI);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
        }

        if ($entry_point) {
            $r = $sg->generateCliSkelton($action_name, $app_dir);
            if (Ethna::isError($r)) {
                printf("error occurred while generating skelton. please see also following error message(s)\n\n");
                return $r;
            }
        }

        return true;
    }

    /**
     *  show usage
     *
     *  @access public
     */
    function usage()
    {
        printf("usage:\nethna %s (-e) [action] ([project-base-dir])\n  -e: add an entry point, too\n", $this->id);
    }

    /**
     *  check arguments
     *
     *  @access private
     */
    function _validateArgList()
    {
        $arg_list = array();
        if (count($this->arg_list) < 1) {
            return Ethna::raiseError('too few argments', 'usage');
        } else if (count($this->arg_list) > 3) {
            return Ethna::raiseError('too many argments', 'usage');
        }

        $getopt =& new Console_Getopt();
        $arg_list = $this->arg_list;
        array_unshift($arg_list, "dummy");
        $r = $getopt->getopt($arg_list, "e", array("entry-point"));
        if (Ethna::isError($r)) {
            return $r;
        }

        $entry_point = false;
        foreach ($r[0] as $opt) {
            if ($opt[0] == "e" || $opt[0] == "--entry-point") {
                $entry_point = true;
            }
        }
        if (count($r[1]) < 0) {
            return Ethna::raiseError('too few arguments', 'usage');
        } else if (count($r[1]) == 1) {
            $view = $r[1][0];
            $app_dir = getcwd();
        } else {
            $view = $r[1][0];
            $app_dir = $r[1][1];
        }

        // TODO: check view name(?) - how it would be easy and pluggable
        if (is_dir($app_dir) == false) {
            return Ethna::raiseError("no such directory [$app_dir]");
        }

        return array($view, $app_dir, $entry_point);
    }
}
// }}}
?>
