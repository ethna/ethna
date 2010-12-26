<?php
// vim: foldmethod=marker
/**
 *  AddAction.php
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
        //
        //  '-w[with-unittest]' and '-u[unittestskel]' option
        //  are not intuisive, but I dare to define them because
        //  -t and -s option are reserved by add-[action|view] handle
        //  and Ethna_Getopt cannot interpret two-character option.
        //
        $r = $this->_getopt(
                  array('basedir=',
                        'skelfile=',
                        'gateway=',
                        'with-unittest',
                        'unittestskel=',
                  )
             );
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // action_name
        $action_name = array_shift($arg_list);
        if ($action_name == null) {
            return Ethna::raiseError('action name isn\'t set.', 'usage');
        }
        $r = Ethna_Controller::checkActionName($action_name);
        if (Ethna::isError($r)) {
            return $r;
        }

        $ret = $this->_perform('Action', $action_name, $opt_list);
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
        
        //  possible target is Action, View.
        $r = Ethna_Generator::generate($target, $basedir,
                                        $target_name, $skelfile, $gateway);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
        }

        //
        //  if specified, generate corresponding testcase,
        //  except for template.
        //
        if ($target != 'Template' && isset($opt_list['with-unittest'])) {
            $testskel = (isset($opt_list['unittestskel']))
                      ? end($opt_list['unittestskel'])
                      : null;
            $r = Ethna_Generator::generate("{$target}Test", $basedir, $target_name, $testskel, $gateway);
            if (Ethna::isError($r)) {
                printf("error occurred while generating action test skelton. please see also following error message(s)\n\n");
                return $r;
            }
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
    {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-g|--gateway=www|cli|xmlrpc] [-w|--with-unittest] [-u|--unittestskel=file] [action]

EOS;
    }

    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-g|--gateway=www|cli|xmlrpc] [-w|--with-unittest] [-u|--unittestskel=file] [action]

EOS;
    }
}
// }}}
