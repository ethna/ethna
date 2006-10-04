<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_ClearCache.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

include_once(ETHNA_BASE . '/class/Ethna_PearWrapper.php');

// {{{ Ethna_Plugin_Handle_ClearCache
/**
 *  clear-cache handler
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_ClearCache extends Ethna_Plugin_Handle
{
    /**
     *  clear cache files.
     *
     *  @access public
     */
    function perform()
    {
        $args =& $this->_parseArgList();
        if (Ethna::isError($args)) {
            return $args;
        }
        $basedir = isset($args['basedir']) ? realpath($args['basedir']) : getcwd();
        $target  = $args['target'];
        if (count($target) == 0) {
            return Ethna::raiseError('give a target to be cleared', 'usage');
        }

        $controller =& Ethna_Handle::getAppController($basedir);
        $tmp_dir = $controller->getDirectory('tmp');

        if (in_array('smarty', $target) || in_array('any-tmp-files', $target)) {
            $renderer =& $controller->getRenderer();
            // TODO: implement Ethna_Renderer::clear_cache();
            if (strtolower(get_class($renderer)) == "ethna_renderer_smarty") {
                $renderer->engine->clear_all_cache();
                $renderer->engine->clear_compiled_tpl();
            }
        }

        if (in_array('cachemanager', $target) || in_array('any-tmp-files', $target)) {
            // TODO: implement Ethna_Plugin_Cachemanager::clear_cache();
            $cache_dir = sprintf("%s/cache", $tmp_dir);
            Ethna_Util::purgeDir($cache_dir);
        }

        if (in_array('pear', $target) || in_array('any-tmp-files', $target)) {
            $pear =& new Ethna_PearWrapper();
            $r =& $pear->init($target, $basedir, $channel);
            if (Ethna::isError($r)) {
                return $r;
            }
            $r =& $pear->doClearCache();
            if (Ethna::isError($r)) {
                return $r;
            }
        }

        if (in_array('any-tmp-files', $target)) {
            // purge only entries in tmp.
            if ($dh = opendir($tmp_dir)) {
                while (($entry = readdir($dh)) !== false) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    Ethna_Util::purgeDir("{$tmp_dir}/{$entry}");
                }
                closedir($dh);
            }
        }

        return true;
    }

    // {{{ _parseArgList()
    /**
     * @access private
     */
    function &_parseArgList()
    {
        $r =& $this->_getopt(array('basedir=', 'any-tmp-files', 'smarty', 'pear', 'cachemanager'));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        $ret = array('target' => array());
        foreach ($opt_list as $opt) {
            switch (true) {
                case ($opt[0] == 'b' || $opt[0] == '--basedir'):
                    $ret['basedir'] = $opt[1];
                    break;
                case ($opt[0] == 'a' || $opt[0] == '--any-tmp-files'):
                    $ret['target'][] = 'any-tmp-files';
                    break;
                case ($opt[0] == 's' || $opt[0] == '--smarty'):
                    $ret['target'][] = 'smarty';
                    break;
                case ($opt[0] == 'p' || $opt[0] == '--pear'):
                    $ret['target'][] = 'pear';
                    break;
                case ($opt[0] == 'c' || $opt[0] == '--cachemanager'):
                    $ret['target'][] = 'cachemanager';
                    break;
            }
        }
        return $ret;
    }
    // }}}

    // {{{ getDescription()
    /**
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
clear project's cache files:
    {$this->id} [-b|--basedir=dir] [-a|--any-tmp-files] [-s|--smarty] [-p|--pear] [-c|--cachemanager]

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
ethna {$this->id} [-b|--basedir=dir] [-a|--any-tmp-files] [-s|--smarty] [-p|--pear] [-c|--cachemanager]
EOS;
    }
    // }}}
}
// }}}
?>
