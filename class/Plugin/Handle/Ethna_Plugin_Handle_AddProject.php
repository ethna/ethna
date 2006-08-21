<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_AddProject.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Handle_AddProject
/**
 *  add-project handler
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddProject extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new project:\n    {$this->id} [project-id] ([project-base-dir])\n";
    }

    /**
     *  add project:)
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($app_id, $app_dir) = $r;

        $sg =& new Ethna_SkeltonGenerator();
        $r = $sg->generateProjectSkelton($app_dir, $app_id);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also error messages given above\n\n");
            return $r;
        }

        printf("\nproject skelton for [%s] is successfully generated at [%s]\n\n", $app_id, $app_dir);
        return true;
    }

    /**
     *  show usage
     *
     *  @access public
     */
    function usage()
    {
        printf("usage:\nethna %s [project-id] ([project-base-dir])\n\n", $this->id);
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
            return Ethna::raiseError('too few arguments', 'usage');
        } else if (count($this->arg_list) > 2) {
            return Ethna::raiseError('too many arguments', 'usage');
        } else if (count($this->arg_list) == 1) {
            $arg_list[] = $this->arg_list[0];
            $arg_list[] = getcwd();
        } else {
            $arg_list = $this->arg_list;
        }

        $r = Ethna_Controller::checkAppId($arg_list[0]);
        if (Ethna::isError($r)) {
            return $r;
        }
        if (is_dir($arg_list[1]) == false) {
            return Ethna::raiseError("no such directory [{$arg_list[1]}]");
        }

        return $arg_list;
    }
}
// }}}
?>
