<?php
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

