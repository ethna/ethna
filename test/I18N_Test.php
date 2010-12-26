<?php
// vim: foldmethod=marker
/**
 *  I18N_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

//{{{    Ethna_I18N_Test
/**
 *  Test Case For Ethna_I18N class
 *
 *  @access public
 */
class Ethna_I18N_Test extends Ethna_UnitTestBase
{
    var $i18n;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
        $this->i18n = $ctl->getI18N();
    }

    // {{{  test_get_ja_JP
    function test_get_ja_JP()
    {
        //  デフォルトは日本語のメッセージが返ってくる
        $this->assertEqual($this->i18n->get('Backend'), 'バックエンド');
        $this->assertEqual($this->i18n->get('Could not write uploaded file to disk.'), 'ディスクへの書き込みに失敗しました。');
        $this->assertEqual($this->i18n->get('Filter(%d)'), 'フィルタ(%d)');
        $this->assertEqual($this->i18n->get('Heisei'), '平成');
        $this->assertEqual($this->i18n->get('%Y/%m/%d %H:%M:%S'), '%Y年%m月%d日 %H時%M分%S秒');

        //  カタログにないメッセージはそのまま返ってくる 
        $this->assertEqual($this->i18n->get('foo'), 'foo');
        $this->assertEqual($this->i18n->get('www.example.com'), 'www.example.com');
    }
    // }}}

    // {{{  test_get_fallback_locale
    function test_get_fallback_locale()
    {
        //  ロケール切り替え
        $this->i18n->setLanguage('en_US', 'ASCII', 'ASCII');

        //  メッセージカタログファイルがないロケールの場合は、
        //  skel/locale/ethna_sysmsg.ini にあるメッセージが返ってくる
        $this->assertEqual($this->i18n->get('Backend'), 'Backend');
        $this->assertEqual($this->i18n->get('Could not write uploaded file to disk.'),
                           'Could not write uploaded file to disk.'
        );
        $this->assertEqual($this->i18n->get('Filter(%d)'), 'Filter(%d)');
        $this->assertEqual($this->i18n->get('Heisei'), 'Heisei');
        $this->assertEqual($this->i18n->get('%Y/%m/%d %H:%M:%S'),
                           '%Y/%m/%d %H:%M:%S'
        );

        //  カタログにないメッセージはそのまま返ってくる 
        $this->assertEqual($this->i18n->get('foo'), 'foo');
        $this->assertEqual($this->i18n->get('www.example.com'), 'www.example.com');

        //    ロケールを再切り替え
        $this->i18n->setLanguage('ja_JP', 'UTF-8', 'UTF-8');

        $this->assertEqual($this->i18n->get('Heisei'), '平成');
        $this->assertEqual($this->i18n->get('foo'), 'foo');

        //  ロケール再再切り替え
        $this->i18n->setLanguage('en_US', 'ASCII', 'ASCII');
        $this->assertEqual($this->i18n->get('foo'), 'foo');
        $this->assertEqual($this->i18n->get('Heisei'), 'Heisei');

        //  他のテストもあるので元に戻しておく
        $this->i18n->setLanguage('ja_JP', 'UTF-8', 'UTF-8');
    }
    // }}} 

    // {{{ test_parseEthnaMsgCatalog
    function test_parseEthnaMsgCatalog()
    {
        $file = ETHNA_BASE . '/test/test_message_catalog.ini';
        $messages = $this->i18n->parseEthnaMsgCatalog($file);

        //   正常な翻訳行 (1行) 
        $expected = '{form}に機種依存文字が入力されています';
        $actual = $messages['{form} contains machine dependent code.'];
        $this->assertEqual($expected, $actual); 

        //   parse_ini_file 関数でパースできない値
        $expected = 'はい';
        $actual = $messages['yes'];
        $this->assertEqual($expected, $actual); 

        $expected = 'いいえ';
        $actual = $messages['no'];
        $this->assertEqual($expected, $actual); 

        $expected = '開き括弧左';
        $actual = $messages['{'];
        $this->assertEqual($expected, $actual); 

        $expected = '開き括弧右';
        $actual = $messages['}'];
        $this->assertEqual($expected, $actual); 

        $expected = 'アンパサンド';
        $actual = $messages['&'];
        $this->assertEqual($expected, $actual); 

        $expected = 'チルダ';
        $actual = $messages['~'];
        $this->assertEqual($expected, $actual); 

        $expected = 'ビックリマーク';
        $actual = $messages['!'];
        $this->assertequal($expected, $actual); 

        //   別の記号類 
        $expected = '%Y年%m月%d日 " %H時%M分%S秒';
        $actual = $messages['%Y/%m/%d "%H:%M:%S'];
        $this->assertequal($expected, $actual); 

        //   複数行に跨がる翻訳行
        $expected = "  \nfuga    ";
        $actual = $messages["\nhoge"];
        $this->assertequal($expected, $actual); 

        $expected = "あいうえお \"\n かきくけこ \nさしすせそ";
        $actual = $messages["abcd\"efg\n hijklmn"];
        $this->assertequal($expected, $actual); 

        //  ダブルクォート連続
        $expected = " ab\n\n\n cdefg ";
        $actual = $messages['""""""'];
        $this->assertequal($expected, $actual); 
    }

    function test_setTimeZone()
    {
        $expected = 'GMT';
        Ethna_I18N::setTimeZone($expected);
        $actual = ini_get('date.timezone');
        if (version_compare(PHP_VERSION, '5.1.0') === 1) { 
            $this->assertequal($expected, $actual); 
        } else {
            $this->assertTrue(empty($actural));
        }
    }
}
// }}}

