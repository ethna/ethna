<?php
/**
 *  Ethna_SmartyPlugin.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ smarty_modifier_number_format
/**
 *  smarty modifier:number_format()
 *
 *  number_format()関数のwrapper
 *
 *  sample:
 *  <code>
 *  {"12345"|number_format}
 *  </code>
 *  <code>
 *  12,345
 *  </code>
 *
 *  @param  string  $string フォーマット対象文字列
 *  @return string  フォーマット済み文字列
 */
function smarty_modifier_number_format($string)
{
    if ($string === "" || $string == null) {
        return "";
    }
    return number_format($string);
}
// }}}

// {{{ smarty_modifier_strftime
/**
 *  smarty modifier:strftime()
 *
 *  strftime()関数のwrapper
 *
 *  sample:
 *  <code>
 *  {"2004/01/01 01:01:01"|strftime:"%Y年%m月%d日"}
 *  </code>
 *  <code>
 *  2004年01月01日
 *  </code>
 *
 *  @param  string  $string フォーマット対象文字列
 *  @param  string  $format 書式指定文字列(strftime()関数参照)
 *  @return string  フォーマット済み文字列
 */
function smarty_modifier_strftime($string, $format)
{
    if ($string === "" || $string == null) {
        return "";
    }
    return strftime($format, strtotime($string));
}
// }}}

// {{{ smarty_modifier_count
/**
 *  smarty modifier:count()
 *
 *  count()関数のwrapper
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array", array(1, 2, 3));
 *
 *  {$array|@count}
 *  </code>
 *  <code>
 *  3
 *  </code>
 *
 *  @param  array   $array  対象となる配列
 *  @return int     配列の要素数
 */
function smarty_modifier_count($array)
{
    return count($array);
}
// }}}

// {{{ smarty_modifier_join
/**
 *  smarty modifier:join()
 *
 *  join()関数のwrapper
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array", array(1, 2, 3));
 *
 *  {$array|@join:":"}
 *  </code>
 *  <code>
 *  1:2:3
 *  </code>
 *
 *  @param  array   $array  join対象の配列
 *  @param  string  $glue   連結文字列
 *  @return string  連結後の文字列
 */
function smarty_modifier_join($array, $glue)
{
    if (is_array($array) == false) {
        return $array;
    }
    return implode($glue, $array);
}
// }}}

// {{{ smarty_modifier_filter
/**
 *  smarty modifier:filter()
 *
 *  指定された連想配列のうち$keyで指定された要素のみを配列に再構成する
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array", array(
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 2, "bar" => 5),
 *      array("foo" => 3, "bar" => 6),
 *  ));
 *
 *  {$array|@filter:"foo"|@join:","}
 *  </code>
 *  <code>
 *  1,2,3
 *  </code>
 *  
 *  @param  array   $array  filter対象となる配列
 *  @param  string  $key    抜き出して配列を構成する連想配列のキー
 *  @return array   再構成された配列
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
// }}}

// {{{ smarty_modifier_unique
/**
 *  smarty modifier:unique()
 *
 *  unique()関数のwrapper
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array1", array("a", "a", "b", "a", "b", "c"));
 *  $smarty->assign("array2", array(
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 2, "bar" => 5),
 *      array("foo" => 3, "bar" => 6),
 *      array("foo" => 2, "bar" => 5),
 *  ));
 *
 *  {$array1|@unique}
 *  {$array2|@unique:"foo"}
 *  </code>
 *  <code>
 *  abc
 *  123
 *  </code>
 *  
 *  @param  array   $array  処理対象となる配列
 *  @param  key     $key    処理対象となるキー(nullなら配列要素)
 *  @return array   再構成された配列
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
// }}}

// {{{ smarty_modifier_wordwrap_i18n
/**
 *  smarty modifier:文字列のwordwrap処理
 *
 *  sample:
 *  <code>
 *  {"あいうaえaおaかきaaaくけこ"|wordrap_i18n:8}
 *  </code>
 *  <code>
 *  あいうa
 *  えaおaか
 *  きaaaく
 *  けこ
 *  </code>
 *
 *  @param  string  $string wordwrapする文字列
 *  @param  string  $break  改行文字
 *  @param  int     $width  wordwrap幅(半角$width文字でwordwrapする)
 *  @param  int     $indent インデント幅(半角$indent文字)
 *                          数値を指定するが、はじめの行はインデントされない
 *  @return string  wordwrap処理された文字列
 */
function smarty_modifier_wordwrap_i18n($string, $width, $break = "\n", $indent = 0)
{
    $ctl =& Ethna_Controller::getInstance();
    $client_enc = $ctl->getClientEncoding(); 

    //    いわゆる半角を単位にしてwrapする位置を測るため、いったん
    //    EUC_JP に変換する
    $euc_string = mb_convert_encoding($string, 'EUC_JP', $client_enc);

    $r = "";
    $i = "$break" . str_repeat(" ", $indent);
    $tmp = $euc_string;
    do {
        $n = strpos($tmp, $break);
        if ($n !== false && $n < $width) {
            $s = substr($tmp, 0, $n);
            $r .= $s . $i;
            $tmp = substr($tmp, strlen($s) + strlen($break));
            continue;
        }

        $s = mb_strimwidth($tmp, 0, $width, "", 'EUC_JP');

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

    //    最後に、クライアントエンコーディングに変換
    $r = mb_convert_encoding($r, $client_enc, 'EUC_JP');

    return $r;
}
// }}}

// {{{ smarty_modifier_truncate_i18n
/**
 *  smarty modifier:文字列切り詰め処理(i18n対応)
 *
 *  sample:
 *  <code>
 *  {"日本語です"|truncate_i18n:5:"..."}
 *  </code>
 *  <code>
 *  日本...
 *  </code>
 *
 *  @param  int     $len        最大文字幅
 *  @param  string  $postfix    末尾に付加する文字列
 */
function smarty_modifier_truncate_i18n($string, $len = 80, $postfix = "...")
{
    return mb_strimwidth($string, 0, $len, $postfix);
}
// }}}

// {{{ smarty_modifier_i18n
/**
 *  smarty modifier:i18nフィルタ
 *
 *  sample:
 *  <code>
 *  {"english"|i18n}
 *  </code>
 *  <code>
 *  英語
 *  </code>
 *
 *  @param  string  $string i18n処理対象の文字列
 *  @return string  ロケールに対応したメッセージ
 */
function smarty_modifier_i18n($string)
{
    $c =& Ethna_Controller::getInstance();

    $i18n =& $c->getI18N();

    return $i18n->get($string);
}
// }}}

// {{{ smarty_modifier_checkbox
/**
 *  smarty modifier:チェックボックス用フィルタ
 *
 *  sample:
 *  <code>
 *  <input type="checkbox" name="test" {""|checkbox}>
 *  <input type="checkbox" name="test" {"1"|checkbox}>
 *  </code>
 *  <code>
 *  <input type="checkbox" name="test">
 *  <input type="checkbox" name="test" checkbox>
 *  </code>
 *
 *  @param  string  $string チェックボックスに渡されたフォーム値
 *  @return string  $stringが空文字列あるいは0以外の場合は"checked"
 */
function smarty_modifier_checkbox($string)
{
    if ($string != "" && $string != 0) {
        return "checked";
    }
}
// }}}

// {{{ smarty_modifier_select
/**
 *  smarty modifier:セレクトボックス用フィルタ
 *
 *  単純なセレクトボックスの場合はsmarty関数"select"を利用することで
 *  タグを省略可能
 *
 *  sample:
 *  <code>
 *  $smarty->assign("form", 1);
 *
 *  <option value="1" {$form|select:"1"}>foo</option>
 *  <option value="2" {$form|select:"2"}>bar</option>
 *  </code>
 *  <code>
 *  <option value="1" selected>foo</option>
 *  <option value="2" >bar</option>
 *  </code>
 *
 *  @param  string  $string セレクトボックスに渡されたフォーム値
 *  @param  string  $value  <option>タグに指定されている値
 *  @return string  $stringが$valueにマッチする場合は"selected"
 */
function smarty_modifier_select($string, $value)
{
    //    標準に合わせる
    //    @see http://www.w3.org/TR/html401/interact/forms.html#adef-selected
    //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd
    //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd
    //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-frameset.dtd
    //    @see http://www.w3.org/TR/xhtml-modularization/abstract_modules.html#s_sformsmodule
    if ($string == $value) {
        return 'selected="selected"';
    }
}
// }}}

// {{{ smarty_modifier_form_value
/**
 *  smarty modifier:フォーム値出力フィルタ
 *
 *  フォーム名を変数で指定してフォーム値を取得したい場合に使用する
 *
 *  sample:
 *  <code>
 *  $this->af->set('foo', 'bar);
 *  $smarty->assign('key', 'foo');
 *  {$key|form_value}
 *  </code>
 *  <code>
 *  bar
 *  </code>
 *
 *  @param  string  $string フォーム項目名
 *  @return string  フォーム値
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

    return htmlspecialchars($r, ENT_QUOTES);
}
// }}}

// {{{ smarty_function_is_error
/**
 *  smarty function:指定されたフォーム項目でエラーが発生しているかどうかを返す
 *  NOTE: {if is_error('name')} は Ethna_Util.php の is_error() であって、
 *        smarty_function_is_error() ではないことに注意
 *
 *  @param  string  $name   フォーム項目名
 */
function smarty_function_is_error($params, &$smarty)
{
    $name = isset($params['name']) ? $params['name'] : null;
    return is_error($name);
}
// }}}

// {{{ smarty_function_message
/**
 *  smarty function:指定されたフォーム項目に対応するエラーメッセージを出力する
 *
 *  sample:
 *  <code>
 *  <input type="text" name="foo">{message name="foo"}
 *  </code>
 *  <code>
 *  <input type="text" name="foo">fooを入力してください
 *  </code>
 *
 *  @param  string  $name   フォーム項目名
 */
function smarty_function_message($params, &$smarty)
{
    if (isset($params['name']) === false) {
        return '';
    }

    $c =& Ethna_Controller::getInstance();
    $action_error =& $c->getActionError();

    $message = $action_error->getMessage($params['name']);
    if ($message === null) {
        return '';
    }

    $id = isset($params['id']) ? $params['id']
        : str_replace("_", "-", "ethna-error-" . $params['name']);
    $class = isset($params['class']) ? $params['class'] : "ethna-error";
    return sprintf('<span class="%s" id="%s">%s</span>',
        $class, $id, htmlspecialchars($message));
}
// }}}

// {{{ smarty_function_uniqid
/**
 *  smarty function:ユニークIDを生成する(double postチェック用)
 *
 *  sample:
 *  <code>
 *  {uniqid}
 *  </code>
 *  <code>
 *  <input type="hidden" name="uniqid" value="a0f24f75e...e48864d3e">
 *  </code>
 *
 *  @param  string  $type   表示タイプ("get" or "post"−デフォルト="post")
 *  @see    isDuplicatePost
 */
function smarty_function_uniqid($params, &$smarty)
{
    $uniqid = Ethna_Util::getRandom();
    if (isset($params['type']) && $params['type'] == 'get') {
        return "uniqid=$uniqid";
    } else {
        return "<input type=\"hidden\" name=\"uniqid\" value=\"$uniqid\" />\n";
    }
}
// }}}

// {{{ smarty_function_select
/**
 *  smarty function:セレクトフィールド生成
 *
 *  @param  array   $list   選択肢一覧
 *  @param  string  $name   フォーム項目名
 *  @param  string  $value  セレクトボックスに渡されたフォーム値
 *  @param  string  $empty  空エントリ(「---選択して下さい---」等)
 *  @deprecated
 */
function smarty_function_select($params, &$smarty)
{
    extract($params);

    print "<select name=\"$name\">\n";
    if ($empty) {
        printf("<option value=\"\">%s</option>\n", $empty);
    }
    foreach ($list as $id => $elt) {
        printf("<option value=\"%s\" %s>%s</option>\n", $id, $id == $value ? 'selected="true"' : '', $elt['name']);
    }
    print "</select>\n";
}
// }}}

// {{{ smarty_function_checkbox_list
/**
 *  smarty function:チェックボックスフィルタ関数(配列対応)
 *
 *  @param  string  $form   チェックボックスに渡されたフォーム値
 *  @param  string  $key    評価対象の配列インデックス
 *  @param  string  $value  評価値
 *  @deprecated
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
// }}}

// {{{ smarty_function_url
/**
 *  smarty function:url生成
 */
function smarty_function_url($params, &$smarty)
{
    $action = $path = $path_key = null;
    $query = $params;

    foreach (array('action', 'anchor', 'scheme') as $key) {
        if (isset($params[$key])) {
            ${$key} = $params[$key];
        } else {
            ${$key} = null;
        }
        unset($query[$key]);
    }

    $c =& Ethna_Controller::getInstance();
    $config =& $c->getConfig();
    $url_handler =& $c->getUrlHandler();
    list($path, $path_key) = $url_handler->actionToRequest($action, $query);

    if ($path != "") {
        if (is_array($path_key)) {
            foreach ($path_key as $key) {
                unset($query[$key]);
            }
        }
    } else {
        $query = $url_handler->buildActionParameter($query, $action);
    }
    $query = $url_handler->buildQueryParameter($query);

    $url = sprintf('%s%s', $config->get('url'), $path);

    if (preg_match('|^(\w+)://(.*)$|', $url, $match)) {
        if ($scheme) {
            $match[1] = $scheme;
        }
        $match[2] = preg_replace('|/+|', '/', $match[2]);
        $url = $match[1] . '://' . $match[2];
    }

    $url .= $query ? "?$query" : "";
    $url .= $anchor ? "#$anchor" : "";

    return $url;
}
// }}}

// {{{ smarty_function_form_name
/**
 *  smarty function:フォーム表示名生成
 *
 *  @param  string  $name   フォーム項目名
 */
function smarty_function_form_name($params, &$smarty)
{
    // name
    if (isset($params['name'])) {
        $name = $params['name'];
        unset($params['name']);
    } else {
        return null;
    }

    // view object
    $c =& Ethna_Controller::getInstance();
    $view =& $c->getView();
    if ($view === null) {
        return null;
    }

    // action
    $action = null;
    if (isset($params['action'])) {
        $action = $params['action'];
        unset($params['action']);
    } else {
        for ($i = count($smarty->_tag_stack); $i >= 0; --$i) {
            if ($smarty->_tag_stack[$i][0] === 'form') {
                if (isset($smarty->_tag_stack[$i][1]['ethna_action'])) {
                    $action = $smarty->_tag_stack[$i][1]['ethna_action'];
                }
                break;
            }
        }
    }
    if ($action !== null) {
        $view->addActionFormHelper($action);
    }

    return $view->getFormName($name, $action, $params);
}
// }}}

// {{{ smarty_function_form_submit
/**
 *  smarty function:フォームのsubmitボタン生成
 *
 *  @param  string  $submit   フォーム項目名
 */
function smarty_function_form_submit($params, &$smarty)
{
    $c =& Ethna_Controller::getInstance();
    $view =& $c->getView();
    if ($view === null) {
        return null;
    }
    return $view->getFormSubmit($params);
}
// }}}

// {{{ smarty_function_form_input
/**
 *  smarty function:フォームタグ生成
 *
 *  @param  string  $name   フォーム項目名
 */
function smarty_function_form_input($params, &$smarty)
{
    // name
    if (isset($params['name'])) {
        $name = $params['name'];
        unset($params['name']);
    } else {
        return null;
    }

    // view object
    $c =& Ethna_Controller::getInstance();
    $view =& $c->getView();
    if ($view === null) {
        return null;
    }

    // 現在の{form_input}を囲むform blockがあればパラメータを取得しておく
    $block_params = null;
    for ($i = count($smarty->_tag_stack); $i >= 0; --$i) {
        if ($smarty->_tag_stack[$i][0] === 'form') {
            $block_params = $smarty->_tag_stack[$i][1];
            break;
        }
    }

    // action
    $action = null;
    if (isset($params['action'])) {
        $action = $params['action'];
        unset($params['action']);
    } else if (isset($block_params['ethna_action'])) {
        $action = $block_params['ethna_action'];
    }
    if ($action !== null) {
        $view->addActionFormHelper($action);
    }

    // default
    if (isset($params['default'])) {
        // {form_input default=...}が指定されていればそのまま

    } else if (isset($block_params['default'])) {
        // 外側の {form default=...} ブロック
        if (isset($block_params['default'][$name])) {
            $params['default'] = $block_params['default'][$name];
        }
    }

    // 現在のアクションで受け取ったフォーム値
    $af =& $c->getActionForm();
    $val = $af->get($name);
    if ($val !== null) {
        $params['default'] = $val;
    }

    return $view->getFormInput($name, $action, $params);
}
// }}}

// {{{ smarty_block_form
/**
 *  smarty block:フォームタグ出力プラグイン
 */
function smarty_block_form($params, $content, &$smarty, &$repeat)
{
    if ($repeat) {
        // {form}: ブロック内部に進む前の処理

        // default
        if (isset($params['default']) === false) {
            // 指定なしのときは $form を使う
            $c =& Ethna_Controller::getInstance();
            $af =& $c->getActionForm();

            // c.f. http://smarty.php.net/manual/en/plugins.block.functions.php
            $smarty->_tag_stack[count($smarty->_tag_stack)-1][1]['default']
                =& $af->getArray(false);
        }

        // ここで返す値は出力されない
        return '';

    } else {
        // {/form}: ブロック全体を出力

        $c =& Ethna_Controller::getInstance();
        $view =& $c->getView();
        if ($view === null) {
            return null;
        }

        // ethna_action
        if (isset($params['ethna_action'])) {
            $ethna_action = $params['ethna_action'];
            unset($params['ethna_action']);

            $view->addActionFormHelper($ethna_action);
            $hidden = $c->getActionRequest($ethna_action, 'hidden');
            $content = $hidden . $content;
        }

        // enctype の略称対応
        if (isset($params['enctype'])) {
            if ($params['enctype'] == 'file'
                || $params['enctype'] == 'multipart') {
                $params['enctype'] = 'multipart/form-data';
            } else if ($params['enctype'] == 'url') {
                $params['enctype'] = 'application/x-www-form-urlencoded';
            }
        }

        // defaultはもう不要
        if (isset($params['default'])) {
            unset($params['default']);
        }

        // $contentを囲む<form>ブロック全体を出力
        return $view->getFormBlock($content, $params);
    }
}
// }}}

// {{{ smarty_function_csrfid
/**
 *  smarty function: 正当なポストであることを保証するIDを出力する
 *
 *  sample:
 *  <code>
 *  {csrfid}
 *  </code>
 *  <code>
 *  <input type="hidden" name="csrfid" value="a0f24f75e...e48864d3e">
 *  </code>
 *
 *  @param  string  $type   表示タイプ("get" or "post"−デフォルト="post")
 *  @see    isRequestValid
 */
function smarty_function_csrfid($params, &$smarty)
{
    $c =& Ethna_Controller::getInstance();
    $name = $c->config->get('csrf');
    if (is_null($name)) {
        $name = 'Session';
    }
    $plugin =& $c->getPlugin();
    $csrf = $plugin->getPlugin('Csrf', $name);
    $csrfid = $csrf->get();
    $token_name = $csrf->getName();
    if (isset($params['type']) && $params['type'] == 'get') {
        return sprintf("%s=%s", $token_name, $csrfid);
    } else {
        return sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n", $token_name, $csrfid);
    }
}
// }}}

?>
