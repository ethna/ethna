<?php
/**
 *	config.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	設定クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Config
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	Ethna_Controller	controllerオブジェクト
	 */
	var	$controller;
	
	/**
	 *	@var	array	設定内容
	 */
	var	$config = null;

	/**#@-*/


	/**
	 *	Ethna_Configクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	controllerオブジェクト
	 */
	function Ethna_Config(&$controller)
	{
		$this->controller =& $controller;

		// 設定ファイルの読み込み
		if ($this->_getConfig() == false) {
			// この時点ではlogging等は出来ない
			$fp = fopen("php://stderr", "r");
			fputs($fp, sprintf("error occured while reading config file(s)\n"));
			fclose($fp);
			$this->controller->fatal();
		}
	}

	/**
	 *	設定値へのアクセサ(R)
	 *
	 *	@access	public
	 *	@param	string	$key	設定項目名
	 *	@return	string	設定値
	 */
	function get($key)
	{
		if (isset($this->config[$key]) == false) {
			return null;
		}
		return $this->config[$key];
	}

	/**
	 *	設定値へのアクセサ(W)
	 *
	 *	@access	public
	 *	@param	string	$key	設定項目名
	 *	@param	string	$value	設定値
	 */
	function set($key, $value)
	{
		$this->config[$key] = $value;
	}

	/**
	 *	設定ファイルを更新する
	 *
	 *	@access	public
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function update()
	{
		return $this->_setConfig();
	}

	/**
	 *	設定ファイルを読み込む
	 *
	 *	@access	private
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function _getConfig()
	{
		$config = array();
		$file = $this->_getConfigFile();
		if (file_exists($file)) {
			$lh = Ethna_Util::lockFile($file, 'r');
			if ($lh == false) {
				return false;
			}

			include($file);

			Ethna_Util::unlockFile($lh);
		}

		// デフォルト値設定
		if (isset($config['url']) == false) {
			$config['url'] = sprintf("http://%s", $_SERVER['HTTP_HOST']);
		}
		if (isset($config['dsn']) == false) {
			$config['dsn'] = "";
		}
		if (isset($config['log_facility']) == false) {
			$config['log_facility'] = "";
		}
		if (isset($config['log_level']) == false) {
			$config['log_level'] = "";
		}
		if (isset($config['log_option']) == false) {
			$config['log_option'] = "";
		}

		$this->config = $config;

		return true;
	}

	/**
	 *	設定ファイルに書き込む
	 *
	 *	@access	private
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function _setConfig()
	{
		$file = $this->_getConfigFile();

		$lh = Ethna_Util::lockFile($file, 'w');
		if ($lh == false) {
			return false;
		}

		$fp = fopen($file, 'w');
		if ($fp == null) {
			return false;
		}
		fwrite($fp, "<?php\n");
		fwrite($fp, sprintf("/*\n * %s\n *\n * update: %s\n */\n", basename($file), strftime('%Y/%m/%d %H:%M:%S')));
		fwrite($fp, "\$config = array(\n");
		foreach ($this->config as $key => $value) {
			fputs($fp, "\t'$key' => '$value',\n");
		}
		fwrite($fp, ");\n?>\n");
		fclose($fp);

		Ethna_Util::unlockFile($lh);

		return true;
	}

	/**
	 *	設定ファイル名を取得する
	 *
	 *	@access	private
	 *	@return	string	設定ファイルへのフルパス名
	 */
	function _getConfigFile()
	{
		return $this->controller->getDirectory('etc') . '/' . strtolower($this->controller->getAppId()) . '-ini.php';
	}
}
?>
