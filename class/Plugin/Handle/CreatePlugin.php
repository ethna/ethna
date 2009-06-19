<?php
// vim: foldmethod=marker
/**
 *  CreatePlugin.php
 *
 *  please go to http://ethna.jp/ethna-document-dev_guide-pearchannel.html
 *  for more info.
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Handle_CreatePlugin
/**
 *  create Ethna Plugin Skelton handler.
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_CreatePlugin extends Ethna_Plugin_Handle
{
    // {{{ perform()
    /**
     * @access public
     */
    function perform()
    {
        $r =& $this->_getopt(
            array(
                'basedir=',
                'type=',
                'noini',
            )
        ); 
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        //  plugin name
        $plugin_name = array_shift($arg_list);
        if (empty($plugin_name)) {
            return Ethna::raiseError('Please specify plugin Name.', 'usage');
        }

        //  plugin types
        $type = end($opt_list['type']);
        $types = explode(',', $type);
        if (empty($type)) {
            $types = array('v', 'f', 'sm'); // Validator, Filter, Smarty modifier.
        } 

        //  basedir
        if (isset($opt_list['basedir'])) {
            $basedir = realpath(end($opt_list['basedir']));
        } else {
            $basedir = getcwd();
        }

        //  no-ini file flag.
        $no_ini = (isset($opt_list['noini'])) ? true : false; 

        $r = Ethna_Generator::generate('CreatePlugin', NULL, $basedir, $types, $no_ini, $plugin_name);
        if (Ethna::isError($r)) {
            printf("error occurred while generating plugin skelton. please see also error messages given above\n\n");
            return $r;
        }
        printf("\nplugin skelton for [%s] is successfully generated at [%s]\n\n", $plugin_name, "$basedir/$plugin_name");
        return true;
    }
    // }}}

    // {{{ getDescription()
    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-t|--type=f,v,sb,sf,sm...] [-n|--noini] plugin-name
    type is as follows (separated by comma):
        f = Filter,
        v = Validator,
        sb = Smarty block,
        sf = Smarty function,
        sm = Smarty modifier
EOS;
    }
    // }}}

    // {{{ getDescription()
    /**
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
make plugin package:
    {$this->id} [-b|--basedir=dir] [-t|--type=f,v,sb,sf,sm...] [-n|--noini] plugin-name
EOS;
    }
    // }}}
}
// }}}
?>
