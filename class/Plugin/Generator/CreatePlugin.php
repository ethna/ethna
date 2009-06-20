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
     *  @param  string  $no_ini         iniファイル生成フラグ 
     *  @param  string  $plugin_name    プラグイン名 
     *  @return true|Ethna_Error        true:成功 Ethna_Error:失敗
     */
    function &generate($basedir, $types = array(), $no_ini = false, $plugin_name)
    {
        //  create plugin directory
        $plugin_dir = "$basedir/plugin";
        if (!file_exists($plugin_dir)) {
            Ethna_Util::mkdir($plugin_dir, 0755);
        } else {
            printf("directory [$plugin_dir] already exists -> skip.\n");
        }
        //   type check.
        if (empty($types)) {
            return Ethna::raiseError('please specify plugin type.');
        }

        //   generate ini file
        if ($no_ini == false) {
            $ini_skel = 'plugin/skel.plugin.ini';
            $ini_file = strtolower($plugin_name) . '.ini';
            $ini_path = "$plugin_dir/$ini_file";
        
            $macro['plugin_name'] = $plugin_name;
            if (file_exists($ini_path)) {
                printf("file [%s] already exists -> skip\n", $ini_file);
            } else if ($this->_generateFile($ini_skel, $ini_path, $macro) == false) {
                printf("[warning] file creation failed [%s]\n", $ini_file);
            } else {
                printf("plugin ini file successfully created [%s]\n", $ini_file);
            }
        }

        //
        //   Generate Plugin PHP File   
        //
        $plugin_name = ucfirst(strtolower($plugin_name));
        $lplugin_name = strtolower($plugin_name);
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
                break;
            case 'sb':
                $type = 'Smarty';
                $pfilename = "block.${lplugin_name}.php";
                break;
            case 'sf':
                $type = 'Smarty';
                $pfilename = "function.${lplugin_name}.php";
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

        $true = true;
        return $true;
    }
}
// }}}
?>
