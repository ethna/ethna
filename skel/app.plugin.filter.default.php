<?php
/**
 *  {$project_id}_Plugin_Filter_ExecutionTime.php
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/**
 *  実行時間計測フィルタプラグインの実装
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$project_id}_Plugin_Filter_ExecutionTime extends Ethna_Plugin_Filter
{
    /**#@+
     *  @access private
     */

    /**
     *  @var    int     開始時間
     */
    var $stime;

    /**#@-*/


    /**
     *  実行前フィルタ
     *
     *  @access public
     */
    function preFilter()
    {
        $stime = explode(' ', microtime());
        $stime = $stime[1] + $stime[0];
        $this->stime = $stime;
    }

    /**
     *  アクション実行前フィルタ
     *
     *  @access public
     *  @param  string  $action_name    実行されるアクション名
     *  @return string  null:正常終了 (string):実行するアクション名を変更
     */
    function preActionFilter($action_name)
    {
        return null;
    }

    /**
     *  アクション実行後フィルタ
     *
     *  @access public
     *  @param  string  $action_name    実行されたアクション名
     *  @param  string  $forward_name   実行されたアクションからの戻り値
     *  @return string  null:正常終了 (string):遷移名を変更
     */
    function postActionFilter($action_name, $forward_name)
    {
        return null;
    }

    /**
     *  実行後フィルタ
     *
     *  @access public
     */
    function postFilter()
    {
        $etime = explode(' ', microtime());
        $etime = $etime[1] + $etime[0];
        $time   = round(($etime - $this->stime), 4);

        print "\n<!-- page was processed in $time seconds -->\n";
    }
}
?>
