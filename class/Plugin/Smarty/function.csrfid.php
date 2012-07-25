<?php
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
    $c = Ethna_Controller::getInstance();
    $name = $c->getConfig()->get('csrf');
    if (is_null($name)) {
        $name = 'Session';
    }
    $plugin = $c->getPlugin();
    $csrf = $plugin->getPlugin('Csrf', $name);
    $csrfid = $csrf->get();
    $token_name = $csrf->getTokenName();
    if (isset($params['type']) && $params['type'] == 'get') {
        return sprintf("%s=%s", $token_name, $csrfid);
    } else {
        return sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n", $token_name, $csrfid);
    }
}

