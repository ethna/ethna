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
        $r =& $this->_getopt(array('local', 'master', 'basedir=', 'channel=', 'type='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;
        $ret = array();
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
                case ($opt[0] == 't' || $opt[0] == '--type'):
                    $ret['type'] = $opt[1];
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

        // prepare PearWrapper object.
        $pear =& new Ethna_PearWrapper();
        $target = isset($args['target']) ? $args['target'] : null;
        $channel = isset($args['channel']) ? $args['channel'] : null;
        $basedir = isset($args['basedir']) ? realpath($args['basedir']) : getcwd();
        $r =& $pear->init($target, $basedir, $channel);
        if (Ethna::isError($r)) {
            return $r;
        }

        // get plugin list.
        $type = isset($args['type']) ? $args['type'] : null;
        $plugins_found = $this->_getFoundPluginList($pear, $type);
        if (Ethna::isError($plugins_found)) {
            return $plugins_found;
        }
        $plugins_installed = $this->_getInstalledPluginList($pear, $type);
        if (Ethna::isError($plugins_installed)) {
            return $plugins_installed;
        }

        // create table data.
        $data = array();
        $class_list = array_merge(array_keys($plugins_found), array_keys($plugins_installed));
        sort($class_list);
        $class_list = array_unique($class_list);
        foreach ($class_list as $class_name) {
            $tmp = array(null, null, null, null, null);

            // check found plugin.
            if (isset($plugins_found[$class_name])) {
                list($type, $name) = $plugins_found[$class_name];
                $tmp[0] = $type;
                $tmp[1] = $name;
                $tmp[2] = $class_name;
                $tmp[3] = '-';
                $tmp[4] = '-';
            }

            // check installed plugin.
            if (isset($plugins_installed[$class_name])) {
                list($type, $name, $pkg_name, $pkg_version) = $plugins_installed[$class_name];
                if ($tmp[0] === null) {
                    // this plugin is only in skelton
                    $tmp[0] = $type;
                    $tmp[1] = $name;
                    $tmp[2] = '-';
                    $tmp[3] = $pkg_name;
                    $tmp[4] = $pkg_version;
                } else {
                    $tmp[3] = $pkg_name;
                    $tmp[4] = $pkg_version;
                }
            }

            if ($tmp[0] !== null) {
                $data[] = $tmp;
            }
        }

        usort($data, array(&$this, '_sort'));
        $pear->displayTable('installed plugins',
                            array('type', 'name', 'class', 'package', 'version'),
                            $data);
        return true;
    }
    // }}}

    // {{{ _getInstalledPluginList()
    /**
     *  get a list of plugins under pear installation management.
     *
     *  @param  object  $pear   Ethna_PearWrapper object.
     *  @param  string  $_type   plugin type
     *  @return array   package list
     *  @access private
     */
    function &_getInstalledPluginList(&$pear, $_type = null)
    {
        // TODO: deal with a package with more than one plugin.
        $pkg_list =& $pear->getInstalledPackageList();
        if (Ethna::isError($pkg_list)) {
            return $pkg_list;
        }

        $ret = array();

        $plugin =& $pear->target_ctl->getPlugin();
        $appid = $pear->target_ctl->getAppId();
        $test_prefix = $pear->target == 'master' ? 'Ethna' : 'Skel';

        foreach ($pkg_list as $pkg_name) {
            list($prefix,, $type, $name) = explode('_', $pkg_name, 4);
            if (($_type === null || $_type == $type) && $prefix == $test_prefix) {
                list($class_name,,) = $plugin->getPluginNaming($type, $name, $appid);
                $pkg_version = $pear->getVersion($pkg_name);
                $ret[$class_name] = array($type, $name, $pkg_name, $pkg_version);
            }
        }
        return $ret;
    }
    // }}}

    // {{{ _getFoundPluginList()
    /**
     *  get a list of plugins found from controller.
     *  (a local plugin might be installed but still in only skelton.)
     *
     *  @param  object  $pear   Ethna_PearWrapper object.
     *  @param  string  $_type   plugin type
     *  @return array   package list
     *  @access private
     */
    function &_getFoundPluginList(&$pear, $_type = null)
    {
        $ret = array();

        $plugin =& $pear->target_ctl->getPlugin();
        $type_list = $_type === null ? $plugin->searchAllPluginType() : array($_type);

        foreach ($type_list as $type) {
            $plugin->searchAllPluginSrc($type);
            if (isset($plugin->src_registry[$type]) === false) {
                continue;
            }
            foreach ($plugin->src_registry[$type] as $name => $src) {
                if (empty($src)) {
                    continue;
                }
                list($appid,, $type, $name) = explode('_', $src[0], 4);
                if (($pear->target == 'master' && $appid == 'Ethna') || $appid != 'Ethna') {
                    // XXX: src is private! ([0] is class name)
                    $ret[$src[0]] = array($type, $name);
                }
            }
        }
        return $ret;
    }
    // }}}

    // {{{ _sort
    /**
     *  sort callback method
     */
    function _sort($a, $b)
    {
        $cmp_type = strcmp($a[0], $b[0]);
        if ($cmp_type !== 0) {
            return $cmp_type;
        }
        $cmp_name = strcmp($a[1], $b[1]);
        if ($cmp_name !== 0) {
            return $cmp_name;
        }
        return 0;
    }
    // }}}

    // {{{ getDescription()
    /**
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
list local or master plugins. if type (case sensitive) not specified, list all plugins:
    {$this->id} [-c|--channel=channel] [-b|--basedir=dir] [-l|--local] [-m|--master] [-t|--type=type]

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
ethna {$this->id} [-c|--channel=channel] [-b|--basedir=dir] [-l|--local] [-m|--master] [-t|--type=type]
EOS;
    }
    // }}}
}
// }}}

?>
