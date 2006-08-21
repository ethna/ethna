<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_AddTemplate.php
 *
 *  @author     nnno <nnno@nnno.jp> 
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 */

// {{{ Ethna_Plugin_Handle_AddTemplate
/**
 *  add-template handler
 *
 *  @author     nnno <nnno@nnno.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddTemplate extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new template to project:\n    {$this->id} [template] ([project-base-dir])\n";
    }

    /**
     *  add template 
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($template, $app_dir) = $r;

        $sg =& new Ethna_SkeltonGenerator();
        $r = $sg->generateTemplateSkelton($template, $app_dir);
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
        printf("usage:\nethna %s [template] ([project-base-dir])\n", $this->id);
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

        // TODO: check action name(?) - how it would be easy and pluggable
        if (is_dir($arg_list[1]) == false) {
            return Ethna::raiseError("no such directory [{$arg_list[1]}]");
        }

        return $arg_list;
    }
}
// }}}
?>
