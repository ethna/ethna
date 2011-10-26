<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
        <style type="text/css">
        <!--
        {literal}
            body {
                margin: auto;
                background-color: #ffffff;
                color: #000000;
            }
            body, td, th, h1, h2 {font-family: sans-serif;}
            pre {margin: 0px; font-family: monospace;}
            a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
            a:hover {text-decoration: underline;}
            table { margin: auto; border-collapse: collapse;}
            table table { margin: 5px;}
            .center {text-align: center;}
            .center table { text-align: left;}
            .center th { text-align: center !important; }
            td, th { border: 1px solid #000000; font-size: 75%; vertical-align: top;}
            h1 {font-size: 150%;}
            h2 {font-size: 125%;}
            .p {text-align: left;}
            .e {background-color: #ccccff; font-weight: bold; color: #000000;}
            .h {background-color: #9999cc; font-weight: bold; color: #000000;}
            .v {background-color: #cccccc; color: #000000;}
            i {color: #666666; background-color: #cccccc;}
            img {float: right; border: 0px;}
            hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
            .header {
                margin: auto;
                width:600px;
                background-color: #9999cc;
                padding:0.2em;
                border:solid 1px black;
            }
        {/literal}
        //-->
        </style>
        <title>{$app.app_id} - Ethna Info</title>
    </head>
    <body>
        <div class="center">
            <div class="header">
                <h1>{$app.app_id}</h1>
            </div>
            <br />
            <hr />
            Contents
            <hr />
            <table border="0" cellpadding="3" width="600">
                <tr>
                    <td style="border:0px">
                        <ul>
                            <li><a href="#actions">Actions</a></li>
                                <ol>
                                {foreach from=$app.action_list key=action_name item=action}
                                    <li><a href="#action_{$action_name}">{$action_name}</a></li>
                                {/foreach}
                                </ol>
                            <li><a href="#forwards">Forwards</a></li>
                                <ol>
                                {foreach from=$app.forward_list key=forward_name item=forward}
                                    <li><a href="#forward_{$forward_name}">{$forward_name}</a></li>
                                {/foreach}
                                </ol>
                            <li><a href="#configuration">Configuration</a></li>
                            <li><a href="#plugins">Plugins</a></li>
                                <ol>
                                {foreach from=$app.plugin_list key=plugin_type item=plugin}
                                    <li><a href="#plugin_{$plugin_type}">{$plugin_type}</a></li>
                                {/foreach}
                                </ol>
                        </ul>
                    </td>
                </tr>
            </table>
            <br />
            <a name="actions"></a>
            <h1>Actions</h1>

            <table border="0" cellpadding="3" width="600">
                {foreach from=$app.action_list key=action_name item=action}
                <tr class="h">
                    <th colspan="3"><a name="action_{$action_name}"></a>{$action_name}</th>
                </tr>
                <tr>
                    <td class="e">アクションクラス</td>
                    <td class="v" colspan="2">
                        {$action.action_class}{if $action.action_class_info.undef}<i>(未定義)</i>{/if}
                    </td>
                </tr>
                <tr>
                    <td class="e">アクションフォーム</td>
                    <td class="v" colspan="2">{$action.action_form|default:"<i>(未定義)</i>"}{if $action.action_form_info.undef}<i>(未定義)</i>{/if}</td>
                </tr>
                <tr>
                    {if $action.action_form_info.form|@count == 0}
                    <td class="e">フォーム値</td>
                    {else}
                    <td class="e" rowspan="{$action.action_form_info.form|@count}">フォーム値</td>
                    {/if}
                    {foreach name="form" from=$action.action_form_info.form key=form_name item=form}
                        {if !$smarty.foreach.form.first}<tr>{/if}
                        <td class="v">{$form_name}</td>
                        <td class="v">
                            <table cellpadding="1">
                                <tr>
                                    <td style="border:0px; font-size:100%;">表示名</td>
                                    <td style="border:0px; font-size:100%;">{$form.name|default:"<i>未定義</i>"}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">必須</td>
                                    <td style="border:0px; font-size:100%;">{$form.required|default:"false"}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">最大値</td>
                                    <td style="border:0px; font-size:100%;">{$form.max|default:"<i>未定義</i>"}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">最小値</td>
                                    <td style="border:0px; font-size:100%;">{$form.min|default:"<i>未定義</i>"}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">正規表現</td>
                                    <td style="border:0px; font-size:100%;">{$form.regexp|default:"<i>未定義</i>"}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">チェックメソッド</td>
                                    <td style="border:0px; font-size:100%;">{$form.custom|default:"<i>未定義</i>"}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">変換フィルタ</td>
                                    <td style="border:0px; font-size:100%;">{$form.filter|default:"<i>未定義</i>"|nl2br}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">フォームタイプ</td>
                                    <td style="border:0px; font-size:100%;">{$form.form_type|default:"<i>未定義</i>"}</td>
                                </tr>
                                <tr>
                                    <td style="border:0px; font-size:100%;">タイプ</td>
                                    <td style="border:0px; font-size:100%;">{$form.type|default:"<i>未定義</i>"}</td>
                                </tr>
                            </table>
                        </td>
                        {if !$smarty.foreach.form.last}</tr>{/if}
                    {foreachelse}
                        <td class="v" colspan="2"></td>
                    {/foreach}
                </tr>
                <tr>
                    {if $action.action_class_info.return.prepare|@count == 0}
                    <td class="e">遷移先(prepare)</td>
                    {else}
                    <td class="e" rowspan="{$action.action_class_info.return.prepare|@count}">遷移先(prepare)</td>
                    {/if}
                    {foreach name="return_prepare" from=$action.action_class_info.return.prepare item=forward}
                        {if !$smarty.foreach.return_prepare.first}<tr>{/if}
                        <td class="v" colspan="2">{$forward}</td>
                        {if !$smarty.foreach.return_prepare.last}</tr>{/if}

                    {foreachelse}

                        <td class="v" colspan="2"></td>

                    {/foreach}

                </tr>
                <tr>
                    {if $action.action_class_info.return.perform|@count == 0}
                    <td class="e" >遷移先(perform)</td>
                    {else}
                    <td class="e" rowspan="{$action.action_class_info.return.perform|@count}">遷移先(perform)</td>
                    {/if}
                    {foreach name="return_perform" from=$action.action_class_info.return.perform item=forward}
                        {if !$smarty.foreach.return_perform.first}<tr>{/if}
                        <td class="v" colspan="2">{$forward}</td>
                        {if !$smarty.foreach.return_perform.last}</tr>{/if}
                    {foreachelse}
                        <td class="v" colspan="2"></td>
                    {/foreach}
                </tr>
                {/foreach}
            </table>
            <br />

            <a name="forwards"></a>
            <h1>Forwards</h1>

            <table border="0" cellpadding="3" width="600">
                {foreach from=$app.forward_list key=forward_name item=forward}
                <tr class="h">
                    <th colspan="2">
                        <a name="forward_{$forward_name}"></a>
                        {$forward_name}
                    </th>
                </tr>
                <tr>
                    <td class="e">ビュークラス</td>
                    <td class="v">{$forward.view_class|default:"<i>未定義</i>"}</td>
                </tr>
                <tr>
                    <td class="e">テンプレートファイル</td>
                    <td class="v">{$forward.template_file}</td>
                </tr>
                {/foreach}
            </table>
            <br />

            <a name="configuration"></a>
            <h1>Configuration</h1>

            <table border="0" cellpadding="3" width="600">
            {foreach from=$app.configuration key=section_name item=section}
                {if $section_name}
                    <tr class="h">
                        <th class="h" colspan="2">{$section_name}</th>
                    </tr>
                {/if}
                {if count($section) == 0}
                    <tr>
                        <td class="v" colspan="2">N/A</td>
                    </tr>
                {else}
                    {foreach from=$section key=entry_name item=entry}
                    <tr>
                        <td class="e">{$entry_name}</td>
                        <td class="v">{$entry|nl2br}</td>
                    </tr>
                    {/foreach}
                {/if}
            {/foreach}
            </table>

            <br />

            <a name="plugins"></a>
            <h1>Plugins</h1>

            <table border="0" cellpadding="3" width="600">
                {foreach from=$app.plugin_list key=plugin_type item=plugin}
                <tr class="h">
                    <th colspan="2">
                        <a name="plugin_{$plugin_type}"></a>
                        {$plugin_type}
                    </th>
                </tr>
                    {foreach from=$plugin key=plugin_name item=plugin_class}
                    <tr>
                        <td class="e">{$plugin_name}</td>
                        <td class="v">{$plugin_class}</td>
                    </tr>
                    {/foreach}
                {/foreach}
            </table>
            <br />

            <hr />
            powered by <a href="http://ethna.jp/">Ethna {$app.ethna_version}</a> (experimental)
            <hr />
        </div>
    </body>
</html>
