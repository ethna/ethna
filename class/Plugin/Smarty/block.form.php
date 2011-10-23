<?php
/**
 *  smarty block:フォームタグ出力プラグイン
 */
function smarty_block_form($params, $content, &$smarty, &$repeat)
{
    if ($repeat) {
        // {form}: ブロック内部に進む前の処理

        // 配列指定のフォームヘルパ用カウンタをリセットする
        $c = Ethna_Controller::getInstance();
        $view = $c->getView();
        $view->resetFormCounter();

        $tag_stack = $smarty->_tag_stack;
        // {form default=... }
        if (isset($params['default']) === false) {
            // 指定なしのときは $form を使う
            // 1テンプレートに複数 {form} を指定する場合は、
            // default を指定することが必要
            $af = $c->getActionForm();

            // c.f. http://smarty.net/manual/en/plugins.block.functions.php
            $tag_stack[count($tag_stack)-1][1]['default'] = $af->getArray(false);
            $smarty->_tag_stack = $tag_stack;
        }

        // {form name=... }
        // 複数 {form} が置かれた場合に、それぞれを識別する役割を果たす
        if (isset($params['name']) === false) {
            // c.f. http://smarty.php.net/manual/en/plugins.block.functions.php
            $tag_stack[count($tag_stack)-1][1]['name'] = 'default';
            $smarty->_tag_stack = $tag_stack;
        }

        // 動的フォームヘルパを呼ぶ
        if (isset($params['ethna_action'])) {
            $ethna_action = $params['ethna_action'];
            $view = $c->getView();
            $view->addActionFormHelper($ethna_action, true);
        }

        // ここで返す値は出力されない
        return '';

    } else {
        // {/form}: ブロック全体を出力

        $c = Ethna_Controller::getInstance();
        $view = $c->getView();
        if ($view === null) {
            return null;
        }

        // {form ethna_action=... }
        if (isset($params['ethna_action'])) {
            $ethna_action = $params['ethna_action'];
            unset($params['ethna_action']);

            $view->addActionFormHelper($ethna_action);
            $hidden = $c->getActionRequest($ethna_action, 'hidden');
            $content = $hidden . $content;
        }

        //  {form name=... }
        //  指定された場合は、submitされた {form}を識別する
        //  id をhiddenタグで指定する
        //
        //  $params['name'] は formタグのnameタグになるため
        //  unset してはいけない
        $name = $params['name'];
        if ($name != 'default') {
            $name_hidden = sprintf('<input type="hidden" name="ethna_fid" value="%s" />',
                                   htmlspecialchars($name, ENT_QUOTES)
                           );
            $content = $name_hidden . $content;
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

