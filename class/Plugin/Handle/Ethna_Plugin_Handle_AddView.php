<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_AddView.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Plugin/Handle/Ethna_Plugin_Handle_AddAction.php';

// {{{ Ethna_Plugin_Handle_AddView
/**
 *  add-view handler
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddView extends Ethna_Plugin_Handle_AddAction
{
    /**
     *  add view
     *
     *  @access public
     */
    function perform()
    {
        $r =& $this->_getopt(array('basedir=', 'skelfile=', 'template'));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // view_name
        $view_name = array_shift($arg_list);
        $r =& Ethna_Controller::checkViewName($view_name);
        if (Ethna::isError($r)) {
            return $r;
        }

        // add view
        $ret =& $this->_perform('View', $view_name, $opt_list);
        if (Ethna::isError($ret) || $ret === false) { 
            return $ret;
        }

        // add template
        if (isset($opt_list['template'])) {
            $ret =& $this->_perform('Template', $view_name, $opt_list);
            if (Ethna::isError($ret) || $ret === false) { 
                return $ret;
            }
        }

        return true;
    }

    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
add new view to project:
    {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-t|--template] [view]

EOS;
    }

    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-t|--template] [view]
EOS;
    }
}
// }}}
?>
