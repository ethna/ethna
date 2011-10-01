<?php
// vim: foldmethod=marker
/**
 *  CreatePlugin.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com> 
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_CreatePlugin
/**
 *  プラグインパッケージスケルトン生成クラス
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com> 
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_CreatePlugin extends Ethna_Plugin_Generator
{
    /**
     *  テンプレートのスケルトンを生成する
     *
     *  @access public
     *  @param  string  $basedir        ベースディレクトリ
     *  @param  array   $types          プラグインのtype (Validator, Handle等)
     *  @param  string  $forpackage     iniファイル生成フラグ 
     *  @param  string  $plugin_name    プラグイン名 
     *  @return true|Ethna_Error        true:成功 Ethna_Error:失敗
     */
    function generate($basedir, $types = array(), $forpackage = false, $plugin_name)
    {
        $plugin_dir = "$basedir/plugin";
        if (!$forpackage) {
            $chk_ctl = Ethna_Handle::getAppController(getcwd());
            if (Ethna::isError($chk_ctl)) {
                return Ethna::raiseError(
                           "ERROR: You are not in Ethna project. specify [-p|--plugin-package] option, or change directory to the Ethna Project\n"
                );
            }
            $plugin_dir = $chk_ctl->getDirectory('plugin');
        }

        //  create plugin directory
        if (!file_exists($plugin_dir)) {
            Ethna_Util::mkdir($plugin_dir, 0755);
        }

        //   type check.
        if (empty($types)) {
            return Ethna::raiseError('please specify plugin type.');
        }

        //
        //   type check
        //
        foreach ($types as $type) {
            switch (strtolower($type)) {
            case 'f':
            case 'v':
            case 'sm':
            case 'sb':
            case 'sf':
                break;
            default:
                return Ethna::raiseError("unknown plugin type: ${type}", 'usage');
            }
        }

        //
        //   Generate Plugin PHP File   
        //
        $plugin_name = ucfirst(strtolower($plugin_name));
        $lplugin_name = strtolower($plugin_name);
        $macro['plugin_name'] = $plugin_name;
        foreach ($types as $type) {
            $ltype = strtolower($type);
            $macro['plugin_type'] = $type;
            $plugin_file_skel = "plugin/skel.plugin.${ltype}.php";
        
            //   create directory
            switch ($type) {
            case 'f':
                $type = 'Filter';
                $pfilename = "${plugin_name}.php";
                break;
            case 'v':
                $type = 'Validator';
                $pfilename = "${plugin_name}.php";
                break;
            case 'sm':
                $type = 'Smarty';
                $pfilename = "modifier.${lplugin_name}.php";
                $macro['plugin_name'] = $lplugin_name;
                break;
            case 'sb':
                $type = 'Smarty';
                $pfilename = "block.${lplugin_name}.php";
                $macro['plugin_name'] = $lplugin_name;
                break;
            case 'sf':
                $type = 'Smarty';
                $pfilename = "function.${lplugin_name}.php";
                $macro['plugin_name'] = $lplugin_name;
                break;
            }
            $type_dir = "$plugin_dir/$type";
            if (!file_exists($type_dir)) {
                Ethna_Util::mkdir($type_dir, 0755);
            }

            $type_file_path = "$type_dir/${pfilename}";

            // generate
            if (file_exists($type_file_path)) {
                printf("file [%s] already exists -> skip\n", $type_file_path);
            } else if ($this->_generateFile($plugin_file_skel, $type_file_path, $macro) == false) {
                printf("[warning] file creation failed [%s]\n", $type_file_path);
            } else {
                printf("plugin php file successfully created [%s]\n", $type_file_path);
            }
        } 

        //   generate ini file
        if ($forpackage) {
            $ini_skel = 'plugin/skel.plugin.ini';
            $ini_file = strtolower($plugin_name) . '.ini';
            $ini_path = "$plugin_dir/$ini_file";
        
            if (file_exists($ini_path)) {
                printf("file [%s] already exists -> skip\n", $ini_file);
            } else if ($this->_generateFile($ini_skel, $ini_path, $macro) == false) {
                printf("[warning] file creation failed [%s]\n", $ini_file);
            } else {
                printf("plugin ini file successfully created [%s]\n", $ini_file);
            }
        }

        $true = true;
        return $true;
    }
}
// }}}
