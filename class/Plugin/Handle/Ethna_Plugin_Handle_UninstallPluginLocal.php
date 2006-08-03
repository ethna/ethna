<?php
// vim: foldmethod=marker
/**
 *	Ethna_Plugin_Handle_UninstallPluginLocal.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

include_once(ETHNA_BASE . '/class/Ethna_PearWrapper.php');

// {{{ Ethna_Plugin_Handle_UninstallPluginLocal
/**
 *  uninstall-plugin-local handler
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Plugin_Handle_UninstallPluginLocal extends Ethna_Plugin_Handle
{
    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return "uninstall local plugin from project directory:\n    {$this->id} [plugin type] [plugin name] ([project-base-dir])\n";
    }

    /**
     *  uninstall-plugin-local
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_validateArgList();
        if (Ethna::isError($r)) {
            return $r;
        }
        list($plugin_type, $plugin_name, $app_dir) = $r;
        $package = "Skel_Plugin_{$plugin_type}_{$plugin_name}";

        // initialize
        $pear =& new Ethna_PearWrapper();
        $r =& $pear->init('local', $app_dir);
        if (Ethna::isError($r)) {
            return $r;
        }

        // get ctype, cname
        $cpackage = $pear->getCanonicalPackageName($package);
        if (Ethna::isError($cpackage)) {
            return $cpackage;
        }
        list(,, $ctype, $cname) = explode('_', $cpackage, 4);

        // uninstall
        $r = $pear->doUninstall($package);
        if (Ethna::isError($r)) {
            return $r;
        }

        // delete generated plugin
        $ok = $pear->confirmDialog('delete plugin generated from skelton? (could delete locally modified files)');
        if ($ok) {
            $sg =& new Ethna_SkeltonGenerator();
            $r = $sg->deletePlugin($ctype, $cname, $app_dir);
            if (Ethna::isError($r)) {
                printf("error occurred while deleting plugin. please see also following error message(s)\n\n");
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
        printf("usage:\nethna %s [plugin type] [plugin name] ([project-base-dir])\n\n", $this->id);
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
        } else if (count($this->arg_list) > 3) {
            return Ethna::raiseError('too many argments', 'usage');
        } else if (count($this->arg_list) == 2) {
            $arg_list[] = $this->arg_list[0];
            $arg_list[] = $this->arg_list[1];
            $arg_list[] = getcwd();
        } else {
            $arg_list = $this->arg_list;
        }

        return $arg_list;
    }
}
// }}}
?>
