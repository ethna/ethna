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
     *  add project:)
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_getopt(array('basedir='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // app_id
        $app_id = array_shift($arg_list);
        $r = Ethna_Controller::checkAppId($app_id);
        if (Ethna::isError($r)) {
            return $r;
        }

        // basedir
        if (isset($opt_list['basedir'])) {
            $basedir = realpath(end($opt_list['basedir']));
        } else {
            $basedir = getcwd();
        }

        $r = Ethna_Generator::generate('Project', null, $app_id, $basedir);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also error messages given above\n\n");
            return $r;
        }

        printf("\nproject skelton for [%s] is successfully generated at [%s]\n\n", $app_id, $basedir);
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
add new project:
    {$this->id} [-b|--basedir=dir] [project-id]

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
ethna {$this->id} [-b|--basedir=dir] [project-id]
EOS;
    }
}
// }}}
?>
