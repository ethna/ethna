<?php
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

    $c = Ethna_Controller::getInstance();
    $action_error = $c->getActionError();

    $message = $action_error->getMessage($params['name']);
    if ($message === null) {
        return '';
    }

    $id = isset($params['id']) ? $params['id']
        : str_replace("_", "-", "ethna-error-" . $params['name']);
    $class = isset($params['class']) ? $params['class'] : "ethna-error";
    return sprintf('<span class="%s" id="%s">%s</span>',
        $class, $id, htmlspecialchars($message, null, $c->getClientEncoding()));
}

