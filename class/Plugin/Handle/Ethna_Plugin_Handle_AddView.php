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

// {{{ Ethna_Plugin_Handle_AddView
/**
 *  add-view handler
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddView extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new view (and template) to project:\n    {$this->id} (-t) [view] ([project-base-dir])\n";
    }

    /**
     *  add view
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($view, $app_dir, $template) = $r;

        $generator =& new Ethna_Generator();
        $r = $generator->generate('View', $view, $app_dir);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
        }

        if ($template) {
            $r = $generator->generate('Template', $view, $app_dir);
            if (Ethna::isError($r)) {
                printf("error occurred while generating skelton. please see also following error message(s)\n\n");
                return $r;
            }
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
        printf("usage:\nethna %s (-t) [view] ([project-base-dir])\n  -t: add template, too\n", $this->id);
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
        } else if (count($this->arg_list) > 3) {
            return Ethna::raiseError('too many arguments', 'usage');
        }

        $getopt =& new Console_Getopt();
        $arg_list = $this->arg_list;
        array_unshift($arg_list, "dummy");
        $r = $getopt->getopt($arg_list, "t", array("template"));
        if (Ethna::isError($r)) {
            return $r;
        }

        $template = false;
        foreach ($r[0] as $opt) {
            if ($opt[0] == "t" || $opt[0] == "--template") {
                $template = true;
            }
        }
        if (count($r[1]) < 0) {
            return Ethna::raiseError('too few arguments', 'usage');
        } else if (count($r[1]) == 1) {
            $view = $r[1][0];
            $app_dir = getcwd();
        } else {
            $view = $r[1][0];
            $app_dir = $r[1][1];
        }

        $r = Ethna_Controller::checkViewName($view);
        if (Ethna::isError($r)) {
            return $r;
        }
        if (is_dir($app_dir) == false) {
            return Ethna::raiseError("no such directory [$app_dir]");
        }

        return array($view, $app_dir, $template);
    }
}
// }}}
?>
