<?php
// vim: foldmethod=marker
/**
 * Ethna_Plugin_Handle_AddTest.php
 *
 * @author  BoBpp <bobpp@users.sourceforge.jp>
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @package Ethna
 * @version $Id$
 */

// {{{ Ethna_Plugin_Handle_AddTest
/**
 * Ethna_Handle which generates Normal Test Case
 *
 * @author BoBpp <bobpp@users.sourceforge.jp>
 * @package Ethna
 */
class Ethna_Plugin_Handle_AddTest extends Ethna_Plugin_Handle
{
    /**
     * コマンドの概要を返す
     *
     * @access protected
     * @return string コマンド概要
     */
    function getDescription()
    {
         return <<<EOS
Create Normal UnitTestCase
    (If you want action(view) test, use add-[action|view]-test):
    {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [name]

EOS;
    }

     /**
      * コマンドの使用法を返す
      *
      * @access protected
      * @return string コマンドの使用方法
      */
    function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-b|--basedir=dir] [-s|--skelfile=file] [name]

EOS;
    }

    /**
     * コマンドの実装部分
     *
     * テストケースファイル生成を行う
     *
     * @access protected
     * @return mixed 実行結果: TRUE: 成功
     *                         Ethna_Error: エラー
     */
    function &perform()
    {
        // get args.
        $r = $this->_getopt(array('basedir=','skelfile='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($optlist, $arglist) = $r;

        $num = count($arglist);
        if ($num < 1 || $num > 3) {
            return Ethna::raiseError("Invalid Arguments.", 'usage');
        }

        if (isset($optlist['skelfile'])) {
            $skelfile = end($optlist['skelfile']);
        } else {
            $skelfile = null;
        }

        $baseDir = isset($optlist['basedir']) ? $optlist['basedir'] : getcwd();
        $name = $arglist[0];

        $r = Ethna_Generator::generate(
            'Test', $baseDir, $skelfile, $name
        );
        if (Ethna::isError($r)) {
            return $r;
        }

        $true = true;
        return $true;
    }
}
// }}}
