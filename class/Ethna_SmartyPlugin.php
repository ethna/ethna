<?php
/**
 *	Ethna_SmartyPlugin.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	smarty modifier:number_format()
 *
 *	number_format()関数のwrapper
 *
 *	sample:
 *	<code>
 *	{"12345"|number_format}
 *	</code>
 *	<code>
 *	12,345
 *	</code>
 *
 *	@param	string	$string	フォーマット対象文字列
 *	@return	string	フォーマット済み文字列
 */
function smarty_modifier_number_format($string)
{
	if ($string === "" || $string == null) {
		return "";
	}
	return number_format($string);
}

/**
 *	smarty modifier:strftime()
 *
 *	strftime()関数のwrapper
 *
 *	sample:
 *	<code>
 *	{"2004/01/01 01:01:01"|strftime:"%Y年%m月%d日"}
 *	</code>
 *	<code>
 *	2004年01月01日
 *	</code>
 *
 *	@param	string	$string	フォーマット対象文字列
 *	@param	string	$format	書式指定文字列(strftime()関数参照)
 *	@return	string	フォーマット済み文字列
 */
function smarty_modifier_strftime($string, $format)
{
	if ($string === "" || $string == null) {
		return "";
	}
	return strftime($format, strtotime($string));
}

/**
 *	smarty modifier:count()
 *
 *	count()関数のwrapper
 *
 *	sample:
 *	<code>
 *	$smarty->assign("array", array(1, 2, 3));
 *
 *	{$array|@count}
 *	</code>
 *	<code>
 *	3
 *	</code>
 *
 *	@param	array	$array	対象となる配列
 *	@return	int		配列の要素数
 */
function smarty_modifier_count($array)
{
	return count($array);
}

/**
 *	smarty modifier:join()
 *
 *	join()関数のwrapper
 *
 *	sample:
 *	<code>
 *	$smarty->assign("array", array(1, 2, 3));
 *
 *	{$array|@join:":"}
 *	</code>
 *	<code>
 *	1:2:3
 *	</code>
 *
 *	@param	array	$array	join対象の配列
 *	@param	string	$glue	連結文字列
 *	@return	string	連結後の文字列
 */
function smarty_modifier_join($array, $glue)
{
	if (is_array($array) == false) {
		return $array;
	}
	return implode($glue, $array);
}

/**
 *	smarty modifier:filter()
 *
 *	指定された連想配列のうち$keyで指定された要素のみを配列に再構成する
 *
 *	sample:
 *	<code>
 *	$smarty->assign("array", array(
 *		array("foo" => 1, "bar" => 4),
 *		array("foo" => 2, "bar" => 5),
 *		array("foo" => 3, "bar" => 6),
 *	));
 *
 *	{$array|@filter:"foo"|@join:","}
 *	</code>
 *	<code>
 *	1,2,3
 *	</code>
 *	
 *	@param	array	$array	filter対象となる配列
 *	@param	string	$key	抜き出して配列を構成する連想配列のキー
 *	@return	array	再構成された配列
 */
function smarty_modifier_filter($array, $key)
{
	if (is_array($array) == false) {
		return $array;
	}
	$tmp = array();
	foreach ($array as $v) {
		if (isset($v[$key]) == false) {
			continue;
		}
		$tmp[] = $v[$key];
	}
	return $tmp;
}

/**
 *	smarty modifier:unique()
 *
 *	unique()関数のwrapper
 *
 *	sample:
 *	<code>
 *	$smarty->assign("array1", array("a", "a", "b", "a", "b", "c"));
 *	$smarty->assign("array2", array(
 *		array("foo" => 1, "bar" => 4),
 *		array("foo" => 1, "bar" => 4),
 *		array("foo" => 1, "bar" => 4),
 *		array("foo" => 2, "bar" => 5),
 *		array("foo" => 3, "bar" => 6),
 *		array("foo" => 2, "bar" => 5),
 *	));
 *
 *	{$array1|@unique}
 *	{$array2|@unique:"foo"}
 *	</code>
 *	<code>
 *	abc
 *	123
 *	</code>
 *	
 *	@param	array	$array	処理対象となる配列
 *	@param	key		$key	処理対象となるキー(nullなら配列要素)
 *	@return	array	再構成された配列
 */
function smarty_modifier_unique($array, $key = null)
{
	if (is_array($array) == false) {
		return $array;
	}
	if ($key != null) {
		$tmp = array();
		foreach ($array as $v) {
			if (isset($v[$key]) == false) {
				continue;
			}
			$tmp[$v[$key]] = $v;
		}
		return $tmp;
	} else {
		return array_unique($array);
	}
}

/**
 *	smarty modifier:文字列のwordwrap処理(EUC-JP対応)
 *
 *	sample:
 *	<code>
 *	{"あいうaえaおaかきaaaくけこ"|wordrap_i18n:8}
 *	</code>
 *	<code>
 *	あいうa
 *	えaおaか
 *	きaaaく
 *	けこ
 *	</code>
 *
 *	@param	string	$string	wordwrapする文字列
 *	@param	string	$break	改行文字
 *	@param	int		$width	wordwrap幅(半角$width文字でwordwrapする)
 *	@param	int		$indent	インデント幅(半角$indent文字)
 *	@return	string	wordwrap処理された文字列
 */
function smarty_modifier_wordwrap_i18n($string, $width, $break = "\n", $indent = 0)
{
	$r = "";
	$i = "$break" . str_repeat(" ", $indent);
	$tmp = $string;
	do {
		$n = strpos($tmp, $break);
		if ($n !== false && $n < $width) {
			$s = substr($tmp, 0, $n);
			$r .= $s . $i;
			$tmp = substr($tmp, strlen($s) + strlen($break));
			continue;
		}

		$s = mb_strimwidth($tmp, 0, $width, "", "EUC-JP");

		// EUC-JPのみ対応
		$n = strlen($s);
		if ($n >= $width && $tmp{$n} != "" && $tmp{$n} != " ") {
			while ((ord($s{$n-1}) & 0x80) == 0) {
				if ($s{$n-1} == " " || $n == 0) {
					break;
				}
				$n--;
			}
		}
		$s = substr($s, 0, $n);

		$r .= $s . $i;
		$tmp = substr($tmp, strlen($s));
	} while (strlen($s) > 0);

	$r = preg_replace('/\s+$/', '', $r);

	return $r;
}

/**
 *	smarty modifier:文字列切り詰め処理(i18n対応)
 *
 *	sample:
 *	<code>
 *	{"日本語です"|truncate_i18n:5:"..."}
 *	</code>
 *	<code>
 *	日本...
 *	</code>
 *
 *	@param	int		$len		最大文字幅
 *	@param	string	$postfix	末尾に付加する文字列
 */
function smarty_modifier_truncate_i18n($string, $len = 80, $postfix = "...")
{
	return mb_strimwidth($string, 0, $len, $postfix, 'EUC-JP');
}

/**
 *	smarty modifier:i18nフィルタ
 *
 *	sample:
 *	<code>
 *	{"english"|i18n}
 *	</code>
 *	<code>
 *	英語
 *	</code>
 *
 *	@param	string	$string	i18n処理対象の文字列
 *	@return	string	ロケールに対応したメッセージ
 */
function smarty_modifier_i18n($string)
{
	$c =& Ethna_Controller::getInstance();

	$i18n =& $c->getI18N();

	return $i18n->get($string);
}

/**
 *	smarty modifier:チェックボックス用フィルタ
 *
 *	sample:
 *	<code>
 *	<input type="checkbox" name="test" {""|checkbox}>
 *	<input type="checkbox" name="test" {"1"|checkbox}>
 *	</code>
 *	<code>
 *	<input type="checkbox" name="test">
 *	<input type="checkbox" name="test" checkbox>
 *	</code>
 *
 *	@param	string	$string	チェックボックスに渡されたフォーム値
 *	@return	string	$stringが空文字列あるいは0以外の場合は"checked"
 */
function smarty_modifier_checkbox($string)
{
	if ($string != "" && $string != 0) {
		return "checked";
	}
}

/**
 *	smarty modifier:セレクトボックス用フィルタ
 *
 *	単純なセレクトボックスの場合はsmarty関数"select"を利用することで
 *	タグを省略可能
 *
 *	sample:
 *	<code>
 *	$smarty->assign("form", 1);
 *
 *	<option value="1" {$form|select:"1"}>foo</option>
 *	<option value="2" {$form|select:"2"}>bar</option>
 *	</code>
 *	<code>
 *	<option value="1" selected>foo</option>
 *	<option value="2" >bar</option>
 *	</code>
 *
 *	@param	string	$string	セレクトボックスに渡されたフォーム値
 *	@param	string	$value	<option>タグに指定されている値
 *	@return	string	$stringが$valueにマッチする場合は"selected"
 */
function smarty_modifier_select($string, $value)
{
	if ($string == $value) {
		print "selected";
	}
}

/**
 *	smarty modifier:フォーム値出力フィルタ
 *
 *	フォーム名を変数で指定してフォーム値を取得したい場合に使用する
 *
 *	sample:
 *	<code>
 *	$this->af->set('foo', 'bar);
 *	$smarty->assign('key', 'foo');
 *	{$key|form_value}
 *	</code>
 *	<code>
 *	bar
 *	</code>
 *
 *	@param	string	$string	フォーム項目名
 *	@return	string	フォーム値
 */
function smarty_modifier_form_value($string)
{
	$c =& Ethna_Controller::getInstance();
	$af =& $c->getActionForm();

	$elts = explode(".", $string);
	$r = $af->get($elts[0]);
	for ($i = 1; $i < count($elts); $i++) {
		$r = $r[$elts[$i]];
	}

	return htmlspecialchars($r);
}

/**
 *	smarty function:指定されたフォーム項目でエラーが発生しているかどうかを返す
 *
 *	sample:
 *	<code>
 *  {if is_error('name')}
 *  エラー
 *  {/if}
 *	</code>
 *
 *	@param	string	$name	フォーム項目名
 */
function smarty_function_is_error($params, &$smarty)
{
	$c =& Ethna_Controller::getInstance();

	extract($params);

	$action_error =& $c->getActionError();

	return $action_error->isError($name);
}

/**
 *	smarty function:指定されたフォーム項目に対応するエラーメッセージを出力する
 *
 *	sample:
 *	<code>
 *	<input type="text" name="foo">{message name="foo"}
 *	</code>
 *	<code>
 *	<input type="text" name="foo">fooを入力してください
 *	</code>
 *
 *	@param	string	$name	フォーム項目名
 */
function smarty_function_message($params, &$smarty)
{
	$c =& Ethna_Controller::getInstance();

	extract($params);

	$action_error =& $c->getActionError();

	print htmlspecialchars($action_error->getMessage($name));
}

/**
 *	smarty function:ユニークIDを生成する(double postチェック用)
 *
 *	sample:
 *	<code>
 *	{uniqid}
 *	</code>
 *	<code>
 *	<input type="hidden" name="uniqid" value="a0f24f75e...e48864d3e">
 *	</code>
 *
 *	@param	string	$type	表示タイプ("get" or "post"−デフォルト="post")
 *	@see	isDuplicatePost
 */
function smarty_function_uniqid($params, &$smarty)
{
	extract($params);

	$uniqid = Ethna_Util::getRandom();
	if (isset($type) && $type == 'get') {
		print "uniqid=$uniqid";
	} else {
		print "<input type=\"hidden\" name=\"uniqid\" value=\"$uniqid\" />\n";
	}
}

/**
 *	smarty function:セレクトフィールド生成
 *
 *	@param	array	$list	選択肢一覧
 *	@param	string	$name	フォーム項目名
 *	@param	string	$value	セレクトボックスに渡されたフォーム値
 *	@param	string	$empty	空エントリ(「---選択して下さい---」等)
 */
function smarty_function_select($params, &$smarty)
{
	extract($params);

	print "<select name=\"$name\">\n";
	if ($empty) {
		printf("<option value=\"\">%s</option>\n", $empty);
	}
	foreach ($list as $id => $elt) {
		printf("<option value=\"%s\" %s>%s</option>\n", $id, $id == $value ? "selected" : "", $elt['name']);
	}
	print "</select>\n";
}

/**
 *	smarty function:チェックボックスフィルタ関数(配列対応)
 *
 *	@param	string	$form	チェックボックスに渡されたフォーム値
 *	@param	string	$key	評価対象の配列インデックス
 *	@param	string	$value	評価値
 */
function smarty_function_checkbox_list($params, &$smarty)
{
	extract($params);

	if (isset($key) == false) {
		$key = null;
	}
	if (isset($value) == false) {
		$value = null;
	}
	if (isset($checked) == false) {
		$checked = "checked";
	}

	if (is_null($key) == false) {
		if (isset($form[$key])) {
			if (is_null($value)) {
				print $checked;
			} else {
				if (strcmp($form[$key], $value) == 0) {
					print $checked;
				}
			}
		}
	} else if (is_null($value) == false) {
		if (is_array($form)) {
			if (in_array($value, $form)) {
				print $checked;
			}
		} else {
			if (strcmp($value, $form) == 0) {
				print $checked;
			}
		}
	}
}
?>
