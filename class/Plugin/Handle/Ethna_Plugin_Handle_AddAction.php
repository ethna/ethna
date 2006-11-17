<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_AddAction.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Handle_AddAction
/**
 *  add-action handler
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddAction extends Ethna_Plugin_Handle
{
    /**
     *  add action
     *
     *  @access public
     */
    function perform()
    {
        $r =& $this->_getopt(array('basedir=', 'skelfile=', 'gateway='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // action_name
        $action_name = array_shift($arg_list);
        $r =& Ethna_Controller::checkActionName($action_name);
        if (Ethna::isError($r)) {
            return $r;
        }

        $ret =& $this->_perform('Action', $action_name, $opt_list);
        return $ret;
    }

    /**
     *  @access protected
     */
    function &_perform($target, $target_name, $opt_list)
    {
        // basedir
        if (isset($opt_list['basedir'])) {
            $basedir = realpath(end($opt_list['basedir']));
        } else {
            $basedir = getcwd();
        }

        // skelfile
        if (isset($opt_list['skelfile'])) {
            $skelfile = end($opt_list['skelfile']);
        } else {
            $skelfile = null;
        }
        
        // gateway
        if (isset($opt_list['gateway'])) {
            $gateway = 'GATEWAY_' . strtoupper(end($opt_list['gateway']));
            if (defined($gateway)) {
                $gateway = constant($gateway);
            } else {
                return Ethna::raiseError('unknown gateway', 'usage');
            }
        } else {
            $gateway = GATEWAY_WWW;
        }
        
        $r =& Ethna_Generator::generate($target, $basedir,
                                        $target_name, $skelfile, $gateway);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
        }

        $true = true;
        return $true;
    }

    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
add new action to project:
    {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-g|--gateway=www|cli|xmlrpc] [action]

EOS;
    }

    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-g|--gateway=www|cli|xmlrpc] [action]
EOS;
    }
}
// }}}
?>
