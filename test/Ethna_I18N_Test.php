<?php
// vim: foldmethod=marker
/**
 *  Ethna_I18N_Test.php
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
        $ctl =& Ethna_Controller::getInstance();
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
    }
    // }}}
}
// }}}

?>
