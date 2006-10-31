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
     *  add template 
     *
     *  @access public
     */
    function perform()
    {
        $args =& $this->_parseArgList();
        if (Ethna::isError($args)) {
            return $args;
        }

        if (isset($args['template']) === false) {
            return Ethna::raiseError('invalid number of arguments', 'usage');
        }
        $template = $args['template'];
        $basedir = isset($args['basedir']) ? realpath($args['basedir']) : getcwd();
        $skelfile = isset($args['skelfile']) ? $args['skelfile'] : null;

        $generator =& new Ethna_Generator();
        $r = $generator->generate('Template', $template, $basedir, $skelfile);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also following error message(s)\n\n");
            return $r;
        }

        return true;
    }

    // {{{ _parseArgList()
    /**
     * @access private
     */
    function &_parseArgList()
    {
        $r =& $this->_getopt(array('basedir=', 'skelfile='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        $ret = array();
        foreach ($opt_list as $opt) {
            switch (true) {
                case ($opt[0] == 'b' || $opt[0] == '--basedir'):
                    $ret['basedir'] = $opt[1];
                    break;
                case ($opt[0] == 's' || $opt[0] == '--skelfile'):
                    $ret['skelfile'] = $opt[1];
                    break;
            }
        }
        if (count($arg_list) == 1) {
            $ret['template'] = $arg_list[0];
        }

        return $ret;
    }
    // }}}

    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "add new template to project:\n    {$this->id} [--basedir=dir] [--skelfile=file] [template]\n";
    }

    /**
     *  show usage
     *
     *  @access public
     */
    function usage()
    {
        printf("usage:\nethna %s [--basedir=dir] [--skelfile=file] [template]\n", $this->id);
    }
}
// }}}
?>
