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
        //
        //  '-w[with-unittest]' and '-u[unittestskel]' option
        //  are not intuisive, but I dare to define them because
        //  -t and -s option are reserved by add-[action|view] handle
        //  and Console_Getopt cannot interpret two-character option.
        //
        $r =& $this->_getopt(
                  array('basedir=',
                        'skelfile=',
                        'template',
                        'with-unittest',
                        'unittestskel=',
                  )
              );
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // view_name
        $view_name = array_shift($arg_list);
        if ($view_name == null) {
            return Ethna::raiseError('view name isn\'t set.', 'usage');
        }
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
    {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-t|--template] [-w|--with-unittest] [-u|--unittestskel=file] [view]
    NOTICE: "-w" and "-u" options are ignored when you specify -t option.

EOS;
    }

    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-t|--template] [-w|--with-unittest] [-u|--unittestskel=file] [view]
    NOTICE: "-w" and "-u" options are ignored when you specify -t option.
EOS;
    }
}
// }}}
?>
