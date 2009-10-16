<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_AddTemplate.php
 *
 *  @author     nnno <nnno@nnno.jp> 
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 */

require_once ETHNA_BASE . '/class/Plugin/Handle/Ethna_Plugin_Handle_AddView.php';

// {{{ Ethna_Plugin_Handle_AddTemplate
/**
 *  add-template handler
 *
 *  @author     nnno <nnno@nnno.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddTemplate extends Ethna_Plugin_Handle_AddView
{
    /**
     *  add template 
     *
     *  @access public
     */
    function perform()
    {
        $r =& $this->_getopt(
                  array('basedir=',
                        'skelfile=',
                        'locale=',
                        'encoding=',
                  )
              ); 
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // template
        $template = array_shift($arg_list);
        if ($template == null) {
            return Ethna::raiseError('template name isn\'t set.', 'usage');
        }
        $r =& Ethna_Controller::checkViewName($template); // XXX: use checkViewName().
        if (Ethna::isError($r)) {
            return $r;
        }

        // add template
        $ret =& $this->_performTemplate($template, $opt_list);
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
add new template to project:
    {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-l|--locale=locale] [-e|--encoding] [template]

EOS;
    }

    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [-l|--locale=locale] [-e|--encoding] [template]
EOS;
    }
}
// }}}
?>
