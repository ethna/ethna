<?php
// vim: foldmethod=marker
/**
 *  AddProject.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Handle_AddProject
/**
 *  add-project handler
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_AddProject extends Ethna_Plugin_Handle
{
    /**
     *  add project:)
     *
     *  @access public
     */
    function perform()
    {
        $r = $this->_getopt(array('basedir=', 'skeldir=', 'locale=', 'encoding='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // app_id
        $app_id = array_shift($arg_list);
        if ($app_id == null) {
            return Ethna::raiseError('Application id isn\'t set.', 'usage');
        }
        $r = Ethna_Controller::checkAppId($app_id);
        if (Ethna::isError($r)) {
            return $r;
        }

        // basedir
        if (isset($opt_list['basedir'])) {
            $dir = end($opt_list['basedir']);
            $basedir = realpath($dir);
            if ($basedir === false) {  //  e.x file does not exist
                $basedir = $dir;
            }
        } else {
            $basedir = sprintf("%s/%s", getcwd(), strtolower($app_id));
        }

        // skeldir
        if (isset($opt_list['skeldir'])) {
            $selected_dir = end($opt_list['skeldir']);
            $skeldir = realpath($selected_dir);
            if ($skeldir == false || is_dir($skeldir) == false || file_exists($skeldir) == false) {
                return Ethna::raiseError("You specified skeldir, but invalid : $selected_dir", 'usage');
            }
        } else {
            $skeldir = null;
        }

        // locale
        if (isset($opt_list['locale'])) {
            $locale = end($opt_list['locale']);
            if (!preg_match('/^[A-Za-z_]+$/', $locale)) {
                return Ethna::raiseError("You specified locale, but invalid : $locale", 'usage');
            }
        } else {
            $locale = 'ja_JP';  //  default locale. 
        }

        // encoding
        if (isset($opt_list['encoding'])) {
            $encoding = end($opt_list['encoding']);
            if (function_exists('mb_list_encodings')) {
                $supported_enc = mb_list_encodings();
                if (!in_array($encoding, $supported_enc)) {
                    return Ethna::raiseError("Unknown Encoding : $encoding", 'usage');
                }
            }
        } else {
            $encoding = 'UTF-8';  //  default encoding. 
        }

        $r = Ethna_Generator::generate('Project', null, $app_id, $basedir, $skeldir, $locale, $encoding);
        if (Ethna::isError($r)) {
            printf("error occurred while generating skelton. please see also error messages given above\n\n");
            return $r;
        }

        printf("\nproject skelton for [%s] is successfully generated at [%s]\n\n", $app_id, $basedir);
        return true;
    }

    /**
     *  get handler's description
     *
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
add new project:
    {$this->id} [-b|--basedir=dir] [-s|--skeldir] [-l|--locale] [-e|--encoding] [Application id]

EOS;
    }

    /**
     *  get usage
     *
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skeldir] [-l|--locale] [-e|--encoding] [Application id]
EOS;
    }
}
// }}}
