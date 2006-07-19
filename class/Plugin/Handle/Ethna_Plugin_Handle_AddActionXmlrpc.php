<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_AddActionXmlrpc.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */
include_once(ETHNA_BASE . '/class/Plugin/Handle/Ethna_Plugin_Handle_AddAction.php');

// {{{ Ethna_Plugin_Handle_AddActionXmlrpc
/**
 *  add-action handler
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddActionXmlrpc extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new xmlrpc action to project:\n    {$this->id} [action] ([project-base-dir])\n";
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
        list($action_name, $app_dir) = $r;

        $sg =& new Ethna_SkeltonGenerator();
        $r = $sg->generateActionSkelton($action_name, $app_dir, GATEWAY_XMLRPC);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
        }

        return true;
    }
}
// }}}
?>
