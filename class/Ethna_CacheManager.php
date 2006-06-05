<?php
// vim: foldmethod=marker tabstop=4 shiftwidth=4 autoindent
/**
 *	Ethna_CacheManager.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *	@version    $Id$
 */

/**
 *	キャッシュマネージャクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_CacheManager
{
	/**#@+	@access	private	*/

	/**	@var	string	現在のネームスペース */
	var $namespace = '';

	/**	@var	object	Ethna_Backend		backendオブジェクト */
	var $backend;

	/**	@var	object	Ethna_Config		設定オブジェクト	*/
	var $config;

	/**#@-*/

	/**
	 *	Ethna_CacheMaangerクラスのインスタンスを取得する
	 *
	 *	@access	public
	 *	@param	string	$type	キャッシュタイプ('localfile', 'memcache'...)
	 *	@return	object Ethna_CacheMaanger	Ethna_CacheManagerオブジェクト
	 */
	function &getInstance($type)
	{
		static $instance = array();

		if (array_key_exists($type, $instance)) {
			return $instance[$type];
		}

        // TODO: use plugin
		$class_name = sprintf("Ethna_CacheManager_%s", ucfirst(strtolower($type)));
		$class_file = sprintf("%s/CacheManager/%s.php", dirname(__FILE__), $class_name);
		if (file_exists($class_file) == false) {
			$retval = Ethna::raiseError("invalid cache type: no such file ($class_file)", E_CACHE_INVALID_TYPE);
			return $retval;
		}
		include_once($class_file);
		if (class_exists($class_name) == false) {
			$retval = Ethna::raiseError("invalid cache type: class is not defined ($class_name)", E_CACHE_INVALID_TYPE);
			return $retval;
		}

		$instance[$type] =& new $class_name();

		return $instance[$type];
	}
    
    /**
     *  Ethna_CacheManagerクラスのコンストラクタ
     *
     *  @access public
     */
    function Ethna_CacheManager()
    {
        $this->controller =& Ethna_Controller::getInstance();
        $this->backend =& $this->controller->getBackend();
        $this->config =& $this->controller->getConfig();
    }

	/**
	 *	キャッシュネームスペースを取得する
	 *
	 *	@access	public
	 *	@return	string	現在のキャッシュネームスペース
	 */
	function getNamespace($namespace)
	{
		return $this->namespace;
	}

	/**
	 *	キャッシュネームスペースを設定する
	 *
	 *	@access	public
	 *	@param	string	$namespace	ネームスペース
	 */
	function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 *	キャッシュに設定された値を取得する
	 *
	 *	キャッシュに値が設定されている場合はキャッシュ値
	 *	が戻り値となる。キャッシュに値が無い場合やlifetime
	 *	を過ぎている場合、エラーが発生した場合はPEAR_Error
	 *	オブジェクトが戻り値となる。
	 *
	 *	@access	public
	 *	@param	string	$key		キャッシュキー
	 *	@param	int		$lifetime	キャッシュ有効期間
	 *	@param	string	$namespace	キャッシュネームスペース
	 *	@return	mixed	キャッシュ値
	 */
	function get($key, $lifetime = null, $namespace = null)
	{
	}

	/**
	 *	キャッシュの最終更新日時を取得する
	 *
	 *	@access	public
	 *	@param	string	$key		キャッシュキー
	 *	@param	string	$namespace	キャッシュネームスペース
	 *	@return	int		最終更新日時(unixtime)
	 */
	function getLastModified($key, $namespace = null)
	{
	}

	/**
	 *	キャッシュに値を設定する
	 *
	 *	@access	public
	 *	@param	string	$key		キャッシュキー
	 *	@param	mixed	$value		キャッシュ値
	 *	@param	int		$timestamp	キャッシュ最終更新時刻(unixtime)
	 *	@param	string	$namespace	キャッシュネームスペース
	 */
	function set($key, $value, $timestamp = null, $namespace = null)
	{
	}

	/**
	 *	値がキャッシュされているかどうかを取得する
	 *
	 *	@access	public
	 *	@param	string	$key		キャッシュキー
	 *	@param	int		$lifetime	キャッシュ有効期間
	 *	@param	string	$namespace	キャッシュネームスペース
	 */
	function isCached($key, $timestamp = null, $namespace = null)
	{
	}

	/**
	 *	キャッシュから値を削除する
	 *
	 *	@access	public
	 *	@param	string	$key		キャッシュキー
	 *	@param	string	$namespace	キャッシュネームスペース
	 */
	function clear($key, $namespace = null)
	{
	}

	/**
	 *	キャッシュデータをロックする
	 *
	 *	@access	public
	 *	@param	string	$key		キャッシュキー
	 *	@param	int		$timeout	ロックタイムアウト
	 *	@param	string	$namespace	キャッシュネームスペース
	 *	@return	bool	true:成功 false:失敗
	 */
	function lock($key, $timeout = 5, $namespace = null)
	{
		return false;
	}

	/**
	 *	キャッシュデータのロックを解除する
	 *
	 *	@access	public
	 *	@param	string	$key		キャッシュキー
	 *	@param	string	$namespace	キャッシュネームスペース
	 *	@return	bool	true:成功 false:失敗
	 */
	function unlock($key, $namespace = null)
	{
		return false;
	}

	/**
	 * 圧縮フラグを立てる
	 *
	 * MySQLなどいくつかの子クラスで有効
	 * 
	 * @access public
	 * @param bool $flag フラグ
	 */
	function setCompress($flag) {
		return false;
	}
}
?>
