<?php
/**
 *  AddViewTest.php
 *
 *  @author     halt feits <halt.feits@gmail.com>
 *  @package    Ethna
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @version    $Id$
 */

require_once 'Ethna/class/Plugin/Handle/AddView.php';

// {{{ Ethna_Plugin_Handle_AddViewTest
/**
 *  add-view-test handler
 *
 *  @author     halt feits <halt.feits@gmail.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddViewTest extends Ethna_Plugin_Handle_AddView
{
    /**
     *  add view test
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_getopt(array('basedir=', 'skelfile='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // view_name
        $view_name = array_shift($arg_list);
        if ($view_name == null) {
            return Ethna::raiseError('view name isn\'t set.', 'usage');
        }
        $r = Ethna_Controller::checkViewName($view_name);
        if (Ethna::isError($r)) {
            return $r;
        }

        $ret = $this->_perform('ViewTest', $view_name, $opt_list);
        return $ret;
    }

    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
add new view test to project:
    {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [view]

EOS;
    }

    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [view]
EOS;
    }
}
// }}}
