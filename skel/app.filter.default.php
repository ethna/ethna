<?php
/**
 *	{$project_id}_Filter_ExecutionTime.php
 *
 *	@package	{$project_id}
 *
 *	$Id$
 */

/**
 *	実行時間計測フィルタの実装
 *
 *	@author		yourname
 *	@access		public
 *	@package	{$project_id}
 */
class {$project_id}_Filter_ExecutionTime extends Ethna_Filter
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	int		開始時間
	 */
	var	$stime;

	/**#@-*/


	/**
	 *	実行前フィルタ
	 *
	 *	@access	public
	 */
	function prefilter()
	{
		$stime = explode(' ', microtime());
		$stime = $stime[1] + $stime[0];
		$this->stime = $stime;
	}

	/**
	 *	実行後フィルタ
	 *
	 *	@access	public
	 */
	function postfilter()
	{
		$etime = explode(' ', microtime());
		$etime = $etime[1] + $etime[0];
		$time   = round(($etime - $this->stime), 4);

		print "\n<!-- page was processed in $time seconds -->\n";
	}
}
?>
