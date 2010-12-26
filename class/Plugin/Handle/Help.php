<?php
// vim: foldmethod=marker
/**
 *  Plugin_Handle_Help.php
 *
 *  @author     Tomoyuki MARUTA <maru_cc@users.sourceforge.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Handle_Help
/**
 *  add-action handler
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_Help extends Ethna_Plugin_Handle
{
    /**
     *  show help
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_getopt();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // action_name
        $handle_name = array_shift($arg_list);
        if (!strlen($handle_name)) {
            $handler_list = $this->eh->getHandlerList();
            printf("usage: ethna [option] [command] [args...]\n\n");
            printf("available options are as follows:\n\n");
            printf("  -v, --version    show version and exit\n");
            printf("\navailable commands are as follows:\n\n");
            foreach ($handler_list as $handler) {
                printf("  %s\n", $handler->getId());
            }
            return true;
        }

        // getHandler
        $handler = $this->eh->getHandler($handle_name);
        if (Ethna::isError($handler) || $handler === false) { 
            // command not found
            return Ethna::raiseError('command not found.', 'usage');
        }

        echo $handler->getDescription();

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
help:
    {$this->id} [command_name]

EOS;
    }

    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [command_name]
EOS;
    }
}
// }}}
