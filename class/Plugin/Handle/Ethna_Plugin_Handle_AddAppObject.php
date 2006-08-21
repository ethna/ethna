<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_AddAppObject.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Handle_AddAppObject
/**
 *  add-app-object handler
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddAppObject extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new app-object to project:\n    {$this->id} [table name] ([project-base-dir])\n";
    }

    /**
     *  add app-object
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($table_name, $app_dir) = $r;

        $sg =& new Ethna_SkeltonGenerator();
        $r = $sg->generateAppObjectSkelton($table_name, $app_dir);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
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
        printf("usage:\nethna %s [table name] ([project-base-dir])\n\n", $this->id);
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

        if (is_dir($arg_list[1]) == false) {
            return Ethna::raiseError("no such directory [{$arg_list[1]}]");
        }

        return $arg_list;
    }
}
// }}}
?>
