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
			array("app/view", 0755),
			array("bin", 0755),
			array("etc", 0755),
			array("lib", 0755),
			array("locale", 0755),
			array("locale/ja", 0755),
			array("locale/ja/LC_MESSAGES", 0755),
			array("log", 0777),
			array("schema", 0755),
			array("skel", 0755),
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

		$macro['action_class'] = '{$action_class}';
		$macro['action_form'] = '{$action_form}';
		$macro['action_name'] = '{$action_name}';
		$macro['action_path'] = '{$action_path}';
		$macro['forward_name'] = '{$forward_name}';
		$macro['preforward_name'] = '{$preforward_name}';
		$macro['preforward_path'] = '{$preforward_path}';

		if ($this->_generateFile("www.index.php", "$basedir/www/index.php", $macro) == false ||
			$this->_generateFile("www.info.php", "$basedir/www/info.php", $macro) == false ||
			$this->_generateFile("app.controller.php", sprintf("$basedir/app/%s_Controller.php", $macro['project_id']), $macro) == false ||
			$this->_generateFile("app.error.php", sprintf("$basedir/app/%s_Error.php", $macro['project_id']), $macro) == false ||
			$this->_generateFile("app.action.default.php", "$basedir/app/action/Index.php", $macro) == false ||
			$this->_generateFile("app.view.default.php", "$basedir/app/view/Index.php", $macro) == false ||
			$this->_generateFile("bin.generate_action_script.php", "$basedir/bin/generate_action_script.php", $macro) == false ||
			$this->_generateFile("bin.generate_view_script.php", "$basedir/bin/generate_view_script.php", $macro) == false ||
			$this->_generateFile("etc.ini.php", sprintf("$basedir/etc/%s-ini.php", $macro['project_prefix']), $macro) == false ||
			$this->_generateFile("skel.action.php", sprintf("$basedir/skel/skel.action.php"), $macro) == false ||
			$this->_generateFile("skel.view.php", sprintf("$basedir/skel/skel.view.php"), $macro) == false ||
			$this->_generateFile("template.index.tpl", sprintf("$basedir/template/ja/index.tpl"), $macro) == false) {
			return false;
		}

		return true;
	}

	/**
	 *	アクションのスケルトンを生成する
	 *
	 *	@access	public
	 *	@param	string	$action_name	アクション名
	 *	@return	bool	true:成功 false:失敗
	 */
	function generateActionSkelton($action_name)
	{
		$c =& Ethna_Controller::getInstance();

		$action_dir = $c->getActiondir();
		$action_class = $c->getDefaultActionClass($action_name, false);
		$action_form = $c->getDefaultFormClass($action_name, false);
		$action_path = $c->getDefaultActionPath($action_name, false);

		$macro = array();
		$macro['project_id'] = $c->getAppId();
		$macro['action_name'] = $action_name;
		$macro['action_class'] = $action_class;
		$macro['action_form'] = $action_form;
		$macro['action_path'] = $action_path;

		$this->_mkdir(dirname("$action_dir$action_path"), 0755);

		if ($this->_generateFile("skel.action.php", "$action_dir$action_path", $macro) == false) {
			printf("[warning] file creation failed [%s]\n", "$action_dir$action_path");
		} else {
			printf("action script(s) successfully created [%s]\n", "$action_dir$action_path");
		}
	}

	/**
	 *	ビューのスケルトンを生成する
	 *
	 *	@access	public
	 *	@param	string	$forward_name	アクション名
	 *	@return	bool	true:成功 false:失敗
	 */
	function generateViewSkelton($forward_name)
	{
		$c =& Ethna_Controller::getInstance();

		$view_dir = $c->getViewdir();
		$view_class = $c->getDefaultViewClass($forward_name, false);
		$view_path = $c->getDefaultViewPath($forward_name, false);

		$macro = array();
		$macro['project_id'] = $c->getAppId();
		$macro['forward_name'] = $forward_name;
		$macro['view_class'] = $view_class;
		$macro['view_path'] = $view_path;

		$this->_mkdir(dirname("$view_dir/$view_path"), 0755);

		if ($this->_generateFile("skel.view.php", "$view_dir$view_path", $macro) == false) {
			printf("[warning] file creation failed [%s]\n", "$view_dir$view_path");
		} else {
			printf("view script(s) successfully created [%s]\n", "$view_dir$view_path");
		}
	}

	/**
	 *	mkdir -p
	 *
	 *	@access	private
	 *	@param	string	$dir	作成するディレクトリ
	 *	@param	int		$mode	パーミッション
	 *	@return	bool	true:成功 false:失敗
	 */
	function _mkdir($dir, $mode)
	{
		if (@is_dir($dir)) {
			return true;
		}

		$parent = dirname($dir);
		if ($dir == $parent) {
			return true;
		}
		if (is_dir($parent) == false) {
			$this->_mkdir($parent, $mode);
		}

		return mkdir($dir, $mode);
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
		$c =& Ethna_Controller::getInstance();
		if (is_object($c)) {
			$base = $c->getBasedir();
			if (file_exists("$base/skel/$skel") == false) {
				$base = null;
			}
		}
		if (is_null($base)) {
			$base = dirname(dirname(__FILE__));
		}

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
