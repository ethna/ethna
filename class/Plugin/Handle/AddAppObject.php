<?php
// vim: foldmethod=marker
/**
 *  AddAppObject.php
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
     *  add app-object
     *
     *  @access public
     */
    function perform()
    {
        return $this->_perform('AppObject');
    }

    /**
     *  @access protected
     */
    function _perform($target)
    {
        $r =& $this->_getopt(array('basedir='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // table_name
        $table_name = array_shift($arg_list);
        if ($table_name == null) {
            return Ethna::raiseError('table name isn\'t set.', 'usage');
        }

        // basedir
        if (isset($opt_list['basedir'])) {
            $basedir = realpath(end($opt_list['basedir']));
        } else {
            $basedir = getcwd();
        }

        $r =& Ethna_Generator::generate($target, $basedir, $table_name);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
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
add new app-object to project:
    {$this->id} [-b|--basedir=dir] [table name]

EOS;
    }

    /**
     *  get usage
     *
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [table name]
EOS;
    }
}
// }}}
?>
