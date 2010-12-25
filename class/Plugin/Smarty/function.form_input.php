<?php
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
    $c = Ethna_Controller::getInstance();
    $view = $c->getView();
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
        $view->addActionFormHelper($action, true);
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

    // 現在のアクションで受け取ったフォーム値を補正する
    // 補正できるのは、以下の場合のみ
    //
    // 1. {form name=...} の値が設定されていないか、submitされていないとき
    // 2. {form name=...} の値と、submitされたそれが等しいとき
    $af = $c->getActionForm();
    $val = $af->get($name);
    $form_id = $block_params['name'];     // {form name=... }
    $cur_form_id = $af->get('ethna_fid'); // submitされたフォームID
    $can_fill = ($cur_form_id == null
              || $form_id == null
              || $form_id == $cur_form_id);
    if ($can_fill && $val !== null) {
        $params['default'] = $val;
    }

    return $view->getFormInput($name, $action, $params);
}

