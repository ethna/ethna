<?php
// vim: foldmethod=marker
/**
 *	Ethna_Error.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Error
/**
 *	エラークラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Error extends PEAR_Error
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	Ethna_I18N	i18nオブジェクト
	 */
	var $i18n;

	/**
	 *	@var	object	Ethna_Logger	loggerオブジェクト
	 */
	var $logger;

	/**#@-*/

	/**
	 *	Ethna_Errorクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	int		$level				エラーレベル
	 *	@param	string	$message			エラーメッセージ
	 *	@param	int		$code				エラーコード
	 *	@param	array	$userinfo			エラー追加情報(エラーコード以降の全ての引数)
	 */
	function Ethna_Error($message = null, $code = null, $mode = null, $options = null)
	{
		$controller =& getController();
		$this->i18n =& $controller->getI18N();

		// $options以降の引数->$userinfo
		if (func_num_args() > 4) {
			$userinfo = array_slice(func_get_args(), 4);
			if (count($userinfo) == 1) {
				if (is_array($userinfo[0])) {
					$userinfo = $userinfo[0];
				} else if (is_null($userinfo[0])) {
					$userinfo = array();
				}
			}
		} else {
			$userinfo = array();
		}

		// メッセージ補正処理
		if (is_null($message)) {
			// $codeからメッセージを取得する
			$message = $controller->getErrorMessage($code);
			if (is_null($message)) {
				$message = 'unkown error';
			}
		}

		parent::PEAR_Error($message, $code, $mode, $options, $userinfo);

		// Ethnaフレームワークのエラーハンドラ(PEAR_Errorのコールバックとは異なる)
		Ethna::handleError($this);
	}

	/**
	 *	levelへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	int		エラーレベル
	 */
	function getLevel()
	{
		return $this->level;
	}

	/**
	 *	messageへのアクセサ(R)
	 *
	 *	PEAR_Error::getMessage()をオーバーライドして以下の処理を行う
	 *	- エラーメッセージのi18n処理
	 *	- $userinfoとして渡されたデータによるvsprintf()処理
	 *
	 *	@access	public
	 *	@return	string	エラーメッセージ
	 */
	function getMessage()
	{
		$tmp_message = $this->i18n->get($this->message);
		$tmp_userinfo = to_array($this->userinfo);
		$tmp_message_arg_list = array();
		for ($i = 0; $i < count($tmp_userinfo); $i++) {
			$tmp_message_arg_list[] = $this->i18n->get($tmp_userinfo[$i]);
		}
		return vsprintf($tmp_message, $tmp_message_arg_list);
	}

	/**
	 *	エラー追加情報へのアクセサ(R)
	 *
	 *	PEAR_Error::getUserInfo()をオーバーライドして、配列の個々の
	 *	エントリへのアクセスをサポート
	 *
	 *	@access	public
	 *	@param	int		$n		エラー追加情報のインデックス(省略可)
	 *	@return	mixed	message引数
	 */
	function getUserInfo($n = null)
	{
		if (is_null($n)) {
			return $this->userinfo;
		}

		if (isset($this->userinfo[$n])) {
			return $this->userinfo[$n];
		} else {
			return null;
		}
	}

	/**
	 *	エラー追加情報へのアクセサ(W)
	 *
	 *	PEAR_Error::addUserInfo()をオーバーライド
	 *
	 *	@access	public
	 *	@param	string	$info	追加するエラー情報
	 */
	function addUserInfo($info)
	{
		$this->userinfo[] = $info;
	}
}
// }}}
?>
