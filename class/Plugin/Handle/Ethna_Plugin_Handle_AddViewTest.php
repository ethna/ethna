<?php
/**
 *  Ethna_Plugin_Handle_AddViewTest.php
 *
 *  @author     halt feits <halt.feits@gmail.com>
 *  @package    Ethna
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @version    $Id$
 */

/**
 *  add-view-test handler
 *
 *  @author     halt feits <halt.feits@gmail.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddViewTest extends Ethna_Plugin_Handle
{
    
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new view test to project:\n    {$this->id} [view] ([project-base-dir])\n";
    }

    /**
     *  add view test
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($view_name, $app_dir) = $r;

        $sg =& new Ethna_SkeltonGenerator();
        $r = $sg->generateViewTestSkelton($view_name, $app_dir);
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
        printf("usage:\nethna %s [view] ([project-base-dir])\n\n", $this->id);
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

        $r = Ethna_Controller::checkViewName($arg_list[0]);
        if (Ethna::isError($r)) {
            return $r;
        }
        if (is_dir($arg_list[1]) == false) {
            return Ethna::raiseError("no such directory [{$arg_list[1]}]");
        }

        return $arg_list;
    }
    
}

?>
