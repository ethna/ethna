<?php
// vim: foldmethod=marker
/**
 *  Ethna_MockProject.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

define('ETHNA_TEST_DIR', ETHNA_BASE . '/test');
define('ETHNA_TEST_PROJECT', 'mockproject');
define('ETHNA_TEST_SKELDIR', ETHNA_TEST_DIR . '/skel/');

/**
 *  ethna command Emulator Class. 
 *  
 *  @access public
 */
class Ethna_MockProject
{
    var $basedir;
    var $skel_dir;
    var $project_name;
    var $proj_basedir;
    var $is_created;

    /*
     *  コンストラクタ
     * 
     *  @param $basedir プロジェクトベースディレクトリ  
     *  @param $project_name プロジェクト名
     *  @param $skel_dir スケルトンディレクトリ
     *  @access public
     */
    function Ethna_MockProject($basedir = ETHNA_TEST_DIR,
                               $project_name = ETHNA_TEST_PROJECT,
                               $skel_dir = ETHNA_TEST_SKELDIR)
    {
        $this->basedir = $basedir;
        $this->skel_dir = $skel_dir;
        $this->project_name = $project_name;
        $this->proj_basedir = "${basedir}/${project_name}";
        $this->is_created = false;
    }

    /*
     *  プロジェクトを作成します。
     *  ethna add-project コマンドをエミュレートします。
     * 
     *  @access public
     *  @return 成功したらtrue, 失敗したらEthna_Error 
     */
    function create()
    {
        $this->is_created = true;

        if (!is_dir($this->proj_basedir)) {
            do {
                sleep(0.1);
                $r = Ethna_Util::mkdir($this->proj_basedir, 0775);
            } while ($r == false || is_dir($this->proj_basedir) == false);
        }

        //  fire ethna add-project command
        $id = 'add-project';
        $options = array(
                       '-b',
                       $this->basedir . '/' . $this->project_name,
                       '-s',
                       $this->skel_dir, 
                       $this->project_name,
                   );
        $r = $this->runCmd($id, $options); 
        if (Ethna::isError($r)) {
            return $r;
        }

        return true;
    } 

    /*
     *  作成したプロジェクトに対してコマンドを
     *  実行することで、ethna コマンドをエミュレートします。
     *  (プロジェクトがない場合は作成されます)
     * 
     *  @access public
     *  @param string $id  コマンドID (e.x add-action)
     *  @param array  $options コマンドラインオプション
     *                e.x ethna add-action -b /tmp test の場合
     *                    array('-b', '/tmp', 'test') を指定
     *  @return 成功したらtrue, 失敗したらEthna_Error 
     */
    function runCmd($id, $options = array())
    {
        if (($r = $this->create_ifnot_exists()) !== true) {
            return $r;
        }

        //   supplement basedir option.
        $in_basedir_opt = false;
        foreach ($options as $opt) {
            if ($opt == '-b' || $opt == '--basedir') {
                $in_basedir_opt = true;
            }
        }
        if (!$in_basedir_opt) { 
            $base_opt = array('-b', $this->proj_basedir);
            $options = array_merge($base_opt, $options);
        }

        $eh =& new Ethna_Handle();
        $handler =& $eh->getHandler($id);
        if (Ethna::isError($handler)) {
            return $r;
        }

        ob_start(); //  supress output.
        $handler->setArgList($options);
        $r = $handler->perform();
        ob_end_clean();

        if (Ethna::isError($r)) {
            return $r;
        }

        return true;
    }

    /*
     *  作成したプロジェクトのコントローラクラス
     *  のインスタンスを取得します。
     *  (プロジェクトがない場合は作成されます)
     * 
     *  @access public
     *  @return Ethna_Controller コントローラクラスのインスタンス
     *          失敗したらEthna_Error 
     */
    function &getController()
    {
        if (($r = $this->create_ifnot_exists()) !== true) {
            return $r;
        }
        return Ethna_Handle::getAppController($this->proj_basedir);
    }

    /*
     *  作成したプロジェクトのベースディレクトリを取得します。
     *
     *  @access public 
     *  @return string  プロジェクトのベースディレクトリ
     */
    function getBaseDir()
    {
        return $this->proj_basedir;
    }

    /*
     *  プロジェクトを削除します。
     *
     *  @access public 
     */
    function delete()
    {
        Ethna_Util::purgeDir($this->proj_basedir);
    }

    /*
     *  プロジェクトが既に作成されているかをチェックし,
     *  存在しない場合は作成します。
     *
     *  @access private
     *  @return boolean  既に作成している場合はtrue.
     *                   プロジェクトの作成に失敗したらEthna_Error 
     */
    function create_ifnot_exists()
    {
        if ($this->is_created === false) {
            $r = $this->create();
            if (Ethna::isError($r)) {
                return $r;
            }
        }
        return true;
    }
}

?>
