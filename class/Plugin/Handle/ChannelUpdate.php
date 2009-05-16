<?php
// vim: foldmethod=marker
/**
 *  ChannelUpdate.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Ethna_PearWrapper.php';

// {{{ Ethna_Plugin_Handle_ChannelUpdate
/**
 *  info-plugin handler
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_ChannelUpdate extends Ethna_Plugin_Handle
{
    // {{{ _parseArgList()
    /**
     * @access private
     */
    function &_parseArgList()
    {
        $r =& $this->_getopt(array('local', 'master', 'basedir=',
                                   'channel=', 'pearopt='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;
        $ret = array();

        // options
        $ret['target'] = isset($opt_list['master']) ? 'master' : 'local';
        if (isset($opt_list['basedir'])) {
            $ret['basedir'] = end($opt_list['basedir']);
        }
        if (isset($opt_list['channel'])) {
            $ret['channel'] = end($opt_list['channel']);
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
        if (isset($args['pearopt'])) {
            $pear->setPearOpt($args['pearopt']);
        }

        $target = isset($args['target']) ? $args['target'] : null;
        $channel = isset($args['channel']) ? $args['channel'] : null;
        $basedir = isset($args['basedir']) ? realpath($args['basedir']) : getcwd();

        $r =& $pear->init($target, $basedir, $channel);
        if (Ethna::isError($r)) {
            return $r;
        }

        $r =& $pear->doClearCache();
        if (Ethna::isError($r)) {
            return $r;
        }

        $r =& $pear->doChannelUpdate();
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
update package repositry channel:
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
ethna {$this->id} [-c|--channel=channel] [-b|--basedir=dir] [-l|--local] [-m|--master] [type name]
EOS;
    }
    // }}}
}
// }}}
?>
