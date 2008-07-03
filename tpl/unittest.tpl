<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset="UTF-8">
  <meta http-equiv="Content-Style-Type" content="text/css">
    <title>{$app.app_id} - Ethna UnitTest</title>
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
            .vf {background-color: #cccccc; color: red;}
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
</head>
  <body>
    <div class="center">
      <div class="header"><h1>{$app.app_id}</h1></div>
      <h2>Report</h2>
      <table border="0" cellpadding="2" width="600">
      {foreach from=$app.report key="key" item="item"}
        {if $item.type=='Pass'}
          <tr>
            <th class="e">{$item.test}</th>
            <td class="v">{$item.message}</td>
          </tr>
        {elseif $item.type=='CaseEnd'}
        {elseif $item.type=='CaseStart'}
          <tr class="h">
            <th colspan="2">{$item.test_name}</th>
          </tr>
        {elseif $item.type=='Exception'}
          <tr>
            <th class="e">{$item.test}</th>
            <td class="vf">Exception 
              <ul>{foreach from=$item.breadcrumb item="crumb"}<li>{$crumb}</li>{/foreach}</ul><strong>{$message|escape:"html"}</strong><br />
            </td>
          </tr>
        {elseif $item.type=='Fail'}
          <tr>
            <th class="e">{$item.test}</th>
            <td class="vf"><strong>Fail</strong> {$item.message}</td>
          </tr>
        {else}
          <tr class="v">
            <td colspan="2">{$item.message}</td>
          </tr>
        {/if} 
      {foreachelse}
          <tr>
            <td>You don't create any Testcase.</td>
          </tr>
          <tr>
            <td>
              <div class="header">
                <p>
                  you can generate testcase with the following commands.
                </p>
                <p>
                  ethna add-action-test [testcasename]<br>
                  ethna add-view-test   [testcasename]<br>
                  ethna add-test        [testcasename]
                </p>
              </div>
          </tr>
      {/foreach}
      </table>
      <h2>Result</h2>
      <p>
        {$app.result.TestCaseProgress}/{$app.result.TestCaseCount} test cases complete:
        <strong>{$app.result.PassCount}</strong> passes, 
        <strong>{$app.result.FailCount}</strong> fails and 
        <strong>{$app.result.ExceptionCount}</strong> exceptions.
      </p>
      <br />
      <hr />
      powered by <a href="http://ethna.jp/">Ethna {$app.ethna_version}</a>
      <hr />
    </div>
  </body>
</html>
