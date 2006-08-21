<?php
// vim: foldmethod=marker
/**
 *	Ethna_Plugin_Handle_ListPluginMaster.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

include_once(ETHNA_BASE . '/class/Ethna_PearWrapper.php');

// {{{ Ethna_Plugin_Handle_ListPluginMaster
/**
 *  list-plugin-master handler
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Plugin_Handle_ListPluginMaster extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "list master plugins installed on Ethna directory:\n    {$this->id}\n";
    }

    /**
     *  list-plugin-master
     *  TODO: pear list しているだけなのを plugin specific にする
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }

        // list
        $pear =& new Ethna_PearWrapper();
        $r =& $pear->init('master');
        if (Ethna::isError($r)) {
            return $r;
        }
        $r =& $pear->doList();
        if (Ethna::isError($r)) {
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
        printf("usage:\nethna %s\n\n", $this->id);
    }

    /**
     *  check arguments
     *
     *  @access private
     */
    function _validateArgList()
    {
        $arg_list = array();
        if (count($this->arg_list) < 0) {
            return Ethna::raiseError('too few arguments', 'usage');
        } else if (count($this->arg_list) > 0) {
            return Ethna::raiseError('too many arguments', 'usage');
        } else {
            $arg_list = $this->arg_list;
        }

        return $arg_list;
    }
}
// }}}
?>
