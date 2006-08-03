<?php
// vim: foldmethod=marker
/**
 *	Ethna_Plugin_Handle_UninstallPluginMaster.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

include_once(ETHNA_BASE . '/class/Ethna_PearWrapper.php');

// {{{ Ethna_Plugin_Handle_UninstallPluginMaster
/**
 *  uninstall-plugin-master handler
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Plugin_Handle_UninstallPluginMaster extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "uninstall master plugin from Ethna directory:\n    {$this->id} [plugin type] [plugin name]\n";
    }

    /**
     *  uninstall-plugin-master
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($plugin_type, $plugin_name) = $r;
        $package = "Ethna_Plugin_{$plugin_type}_{$plugin_name}";

        // uninstall
        $pear =& new Ethna_PearWrapper();
        $r =& $pear->init('master');
        if (Ethna::isError($r)) {
            return $r;
        }
        $r =& $pear->doUninstall($package);
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
        printf("usage:\nethna %s [plugin type] [plugin name]\n\n", $this->id);
    }

    /**
     *  check arguments
     *
     *  @access private
     */
    function _validateArgList()
    {
        $arg_list = array();
        if (count($this->arg_list) < 2) {
            return Ethna::raiseError('too few argments', 'usage');
        } else if (count($this->arg_list) > 2) {
            return Ethna::raiseError('too many argments', 'usage');
        } else {
            $arg_list = $this->arg_list;
        }

        return $arg_list;
    }
}
// }}}
?>
