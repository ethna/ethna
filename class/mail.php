<?php
/**
 *	mail.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	メールテンプレートタイプ: 直接送信
 */
define('MAILSENDER_TYPE_DIRECT', 0);

/**
 *	メール送信クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_MailSender
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	array	メールテンプレート定義
	 */
	var	$def = array(
	);

	/**
	 *	@var	int		送信メールタイプ
	 */
	var	$type;

	/**
	 *	@var	object	Ethna_Backend	backendオブジェクト
	 */
	var $backend;

	/**
	 *	@var	object	Ethna_Config	設定オブジェクト
	 */
	var $config;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var $action_error;

	/**#@-*/

	/**
	 *	Ethna_MailSenderクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Backend	&$backend		backendオブジェクト
	 */
	function Ethna_MailSender(&$backend)
	{
		$this->backend =& $backend;
		$this->config =& $this->backend->getConfig();
		$this->action_error =& $this->backend->getActionError();
	}

	/**
	 *	メールを送信する
	 *
	 *	@access	public
	 *	@param	string	$to		メール送信先アドレス
	 *	@param	string	$type	メールテンプレートタイプ
	 *	@param	array	$macro	テンプレートマクロ($typeがMAILSENDER_TYPE_DIRECTの場合はメール送信内容)
	 *	@param	array	$attach	添付ファイル(array('content-type' => ..., 'content' => ...))
	 *	@return	string	$toがnullの場合テンプレートマクロ適用後のメール内容
	 */
	function send($to, $type, $macro, $attach = null)
	{
		// コンテンツ作成
		$c =& $this->backend->getController();

		if ($type != MAILSENDER_TYPE_DIRECT) {
			$smarty =& $c->getTemplateEngine();

			// 基本情報設定
			$smarty->assign("env_datetime", strftime('%Y年%m月%d日 %H時%M分%S秒'));
			$smarty->assign("env_useragent", $_SERVER["HTTP_USER_AGENT"]);
			$smarty->assign("env_remoteaddr", $_SERVER["REMOTE_ADDR"]);

			// デフォルトマクロ設定
			$macro = $this->_setDefaultMacro($macro);

			// ユーザ定義情報設定
			if (is_array($macro)) {
				foreach ($macro as $key => $value) {
					$smarty->assign($key, $value);
				}
			}

			$template = $this->def[$type];
			ob_start();
			$smarty->display("mail/$template");
			$mail = ob_get_contents();
			ob_end_clean();
		} else {
			$mail = $macro;
		}

		if (is_null($to)) {
			return $mail;
		}

		// 送信
		foreach (to_array($to) as $rcpt) {
			list($header, $body) = $this->_parse($mail);

			// multipart対応
			if ($attach != null) {
				$boundary = Etuna_Util::getRandom(); 

				$body = "This is a multi-part message in MIME format.\n\n" .
					"--$boundary\n" .
					"Content-Type: text/plain; charset=ISO-2022-JP\n\n" .
					"$body\n" .
					"--$boundary\n" .
					"Content-Type: " . $attach['content-type'] . "; name=\"" . $attach['name'] . "\"\n" .
					"Content-Transfer-Encoding: base64\n" . 
					"Content-Disposition: attachment; filename=\"" . $attach['name'] . "\"\n\n";
				$body .= chunk_split(base64_encode($attach['content']));
				$body .= "--$boundary--";
			}

			$body = str_replace("\r\n", "\n", $body);

			// 最低限必要なヘッダを追加
			if (array_key_exists('mime-version', $header) == false) {
				$header['mime-version'] = array('Mime-Version', '1.0');
			}
			if (array_key_exists('subject', $header) == false) {
				$header['subject'] = array('Subject', 'no subject in original');
			}
			if (array_key_exists('content-type', $header) == false) {
				if ($attach == null) {
					$header['content-type'] = array('Content-Type', 'text/plain; charset=ISO-2022-JP');
				} else {
					$header['content-type'] = array('Content-Type', "multipart/mixed; boundary=\"$boundary\"");
				}
			}

			$header_line = "";
			foreach ($header as $key => $value) {
				if ($key == 'subject') {
					// should be added by mail()
					continue;
				}
				if ($header_line != "") {
					$header_line = "$header_line\n";
				}
				$header_line .= $value[0] . ": " . $value[1];
			}

			mail($rcpt, $header['subject'][1], $body, $header_line);
		}
	}

	/**
	 *	アプリケーション固有のマクロを設定する
	 *
	 *	@access	protected
	 *	@param	array	$macro	ユーザ定義マクロ
	 *	@return	array	アプリケーション固有処理済みマクロ
	 */
	function _setDefaultMacro($macro)
	{
		return $macro;
	}

	/**
	 *	テンプレートメールのヘッダ情報を取得する
	 *
	 *	@access	private
	 *	@param	string	$mail	メールテンプレート
	 *	@return	array	ヘッダ, 本文
	 */
	function _parse($mail)
	{
		list($header_line, $body) = preg_split('/\r?\n\r?\n/', $mail, 2);
		$header_line .= "\n";

		$header_lines = explode("\n", $header_line);
		$header = array();
		foreach ($header_lines as $h) {
			if (strstr($h, ':') == false) {
				continue;
			}
			list($key, $value) = preg_split('/\s*:\s*/', $h, 2);
			$i = strtolower($key);
			$header[$i] = array();
			$header[$i][] = $key;
			$header[$i][] = preg_replace('/([^\x00-\x7f]+)/e', "mb_encode_mimeheader('$1')", $value);
		}

		$body = mb_convert_encoding($body, "ISO-2022-JP", "EUC-JP");

		return array($header, $body);
	}
}
?>
