<?php
// vim: foldmethod=marker
/**
 *	Ethna_SkeltonGenerator.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_SkeltonGenerator
/**
 *	スケルトン生成クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_SkeltonGenerator
{
	/**
	 *	プロジェクトスケルトンを生成する
	 *
	 *	@access	public
	 *	@param	string	$basedir	プロジェクトベースディレクトリ
	 *	@param	string	$id			プロジェクトID
	 *	@return	bool	true:成功 false:失敗
	 */
	function generateProjectSkelton($basedir, $id)
	{
		$dir_list = array(
			array("app", 0755),
			array("app/action", 0755),
			array("bin", 0755),
			array("etc", 0755),
			array("lib", 0755),
			array("locale", 0755),
			array("locale/ja", 0755),
			array("locale/ja/LC_MESSAGES", 0755),
			array("log", 0777),
			array("schema", 0755),
			array("template", 0755),
			array("template/ja", 0755),
			array("tmp", 0777),
			array("www", 0755),
		);

		$basedir = sprintf("%s/%s", $basedir, strtolower($id));

		// ディレクトリ作成
		if (is_dir($basedir) == false) {
			if (mkdir($basedir, 0755) == false) {
				return false;
			}
		}
		foreach ($dir_list as $dir) {
			$mode = $dir[1];
			$dir = $dir[0];
			$target = "$basedir/$dir";
			if (is_dir($target)) {
				printf("%s already exists -> skipping...\n", $target);
				continue;
			}
			if (mkdir($target, $mode) == false) {
				return false;
			} else {
				printf("proejct sub directory created [%s]\n", $target);
			}
			if (chmod($target, $mode) == false) {
				return false;
			}
		}

		// スケルトンファイル作成
		$macro['application_id'] = strtoupper($id);
		$macro['project_id'] = ucfirst($id);
		$macro['project_prefix'] = strtolower($id);
		$macro['basedir'] = realpath($basedir);

		if ($this->_generateFile("www.index.php", "$basedir/www/index.php", $macro) == false ||
			$this->_generateFile("www.info.php", "$basedir/www/info.php", $macro) == false ||
			$this->_generateFile("app.controller.php", sprintf("$basedir/app/%s_Controller.php", $macro['project_id']), $macro) == false ||
			$this->_generateFile("app.error.php", sprintf("$basedir/app/%s_Error.php", $macro['project_id']), $macro) == false ||
			$this->_generateFile("app.action.default.php", sprintf("$basedir/app/action/Index.php", $macro['project_id']), $macro) == false ||
			$this->_generateFile("etc.ini.php", sprintf("$basedir/etc/%s-ini.php", $macro['project_prefix']), $macro) == false ||
			$this->_generateFile("template.index.tpl", sprintf("$basedir/template/ja/index.tpl"), $macro) == false) {
			return false;
		}

		return true;
	}

	/**
	 *	スケルトンファイルにマクロを適用してファイルを生成する
	 *
	 *	ethnaライブラリのディレクトリ構造が変更されていないことが前提
	 *	となっている点に注意
	 *
	 *	@access	private
	 *	@param	string	$skel		スケルトンファイル
	 *	@param	string	$entity		生成ファイル名
	 *	@param	array	$macro		置換マクロ
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function _generateFile($skel, $entity, $macro)
	{
		$base = dirname(dirname(__FILE__));
		$rfp = fopen("$base/skel/$skel", "r");
		if ($rfp == null) {
			return false;
		}
		$wfp = fopen($entity, "w");
		if ($wfp == null) {
			fclose($rfp);
			return false;
		}

		for (;;) {
			$s = fread($rfp, 4096);
			if (strlen($s) == 0) {
				break;
			}

			foreach ($macro as $k => $v) {
				$s = preg_replace("/{\\\$$k}/", $v, $s);
			}
			fwrite($wfp, $s);
		}

		fclose($wfp);
		fclose($rfp);

		$st = stat("$base/skel/$skel");
		if (chmod($entity, $st[2]) == false) {
			return false;
		}

		return true;
	}
}
// }}}
?>
