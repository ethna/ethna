<?php
// vim: foldmethod=marker
/**
 *	Ethna_Plugin_Handle_ListPlugin.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

include_once(ETHNA_BASE . '/class/Ethna_PearWrapper.php');

// {{{ Ethna_Plugin_Handle_ListPlugin
/**
 *  list-plugin handler
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Plugin_Handle_ListPlugin extends Ethna_Plugin_Handle
{
    // {{{ _parseArgList()
    /**
     * @access private
     */
    function &_parseArgList()
    {
        $r =& $this->_getopt(array('local', 'master', 'basedir=', 'channel='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        $ret = array();

        // options
        foreach ($opt_list as $opt) {
            switch (true) {
                case ($opt[0] == 'l' || $opt[0] == '--local'):
                    $ret['target'] = 'local';
                    break;

                case ($opt[0] == 'm' || $opt[0] == '--master'):
                    $ret['target'] = 'master';
                    break;

                case ($opt[0] == 'b' || $opt[0] == '--basedir'):
                    $ret['basedir'] = $opt[1];
                    break;

                case ($opt[0] == 'c' || $opt[0] == '--channel'):
                    $ret['channel'] = $opt[1];
                    break;
            }
        }

        return $ret;
    }
    // }}}

    // {{{ perform()
    /**
     *  @access public
     */
    function perform()
    {
        $args =& $this->_parseArgList();
        if (Ethna::isError($args)) {
            return $args;
        }
        $pear =& new Ethna_PearWrapper();

        // list installed packages.
        $target = isset($args['target']) ? $args['target'] : 'master';
        $channel = isset($args['channel']) ? $args['channel'] : null;
        $basedir = isset($args['basedir']) ? realpath($args['basedir']) : getcwd();

        $r =& $pear->init($target, $basedir, $channel);
        if (Ethna::isError($r)) {
            return $r;
        }
        $r =& $pear->doList();
        if (Ethna::isError($r)) {
            return $r;
        }

        return true;
    }
    // }}}

    // {{{ getDescription()
    /**
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
list installed plugins:
    {$this->id} [-c|--channel=channel] [-b|--basedir=dir] [-l|--local] [-m|--master]

EOS;
    }
    // }}}

    // {{{ getUsage()
    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-c|--channel=channel] [-b|--basedir=dir] [-l|--local] [-m|--master]
EOS;
    }
    // }}}
}
// }}}

?>
