<?php
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

