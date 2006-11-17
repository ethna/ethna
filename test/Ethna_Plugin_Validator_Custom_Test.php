<?php
/**
 *  Ethna_Plugin_Validator_Custom_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Customクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Custom_Test extends Ethna_UnitTestBase
{
    function testCheckValidatorCustom()
    {
        $ctl =& Ethna_Controller::getInstance();
        $plugin =& $ctl->getPlugin();
        $vld = $plugin->getPlugin('Validator', 'Custom');


        // mailaddressカスタムチェックのテスト
        $form_string = array(
                             'type'          => VAR_TYPE_STRING,
                             'required'      => true,
                             'custom' => 'checkMailaddress',
                             );
        $vld->af->form_vars['namae_string'] = 'hoge@fuga.net';
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = '-hoge@fuga.net';
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = '.hoge@fuga.net';
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = '+hoge@fuga.net';
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        // @がない
        $vld->af->form_vars['namae_string'] = 'hogefuga.net';
        $this->assertFalse($vld->validate('namae_string', '', $form_string));

        // @の前に文字がない
        $vld->af->form_vars['namae_string'] = '@hogefuga.net';
        $this->assertFalse($vld->validate('namae_string', '', $form_string));

        // @の後に文字がない
        $vld->af->form_vars['namae_string'] = 'hogefuga.net@';
        $this->assertFalse($vld->validate('namae_string', '', $form_string));

        // 先頭文字が許されていない
        $vld->af->form_vars['namae_string'] = '%hoge@fuga.net';
        $this->assertFalse($vld->validate('namae_string', '', $form_string));

        // 末尾文字が許されていない
        $vld->af->form_vars['namae_string'] = 'hoge@fuga.net.';
        $this->assertFalse($vld->validate('namae_string', '', $form_string));



        $form_boolean = array(
                              'type'          => VAR_TYPE_BOOLEAN,
                              'required'      => true,
                              'custom' => 'checkBoolean',
                              );
        $vld->af->form_vars['namae_boolean'] = true;
        $this->assertTrue($vld->validate('namae_boolean', '', $form_boolean));

        $vld->af->form_vars['namae_boolean'] = false;
        $this->assertTrue($vld->validate('namae_boolean', '', $form_boolean));

        $vld->af->form_vars['namae_boolean'] = '';
        $this->assertTrue($vld->validate('namae_boolean', '', $form_boolean));

        $vld->af->form_vars['namae_boolean'] = array();
        $this->assertTrue($vld->validate('namae_boolean', '', $form_boolean));

        $vld->af->form_vars['namae_boolean'] = array(true);
        $this->assertTrue($vld->validate('namae_boolean', '', $form_boolean));

        // 0,1以外の値
        $vld->af->form_vars['namae_boolean'] = 3;
        $this->assertFalse($vld->validate('namae_boolean', '', $form_boolean));




        $form_url = array(
                          'type'          => VAR_TYPE_STRING,
                          'required'      => true,
                          'custom' => 'checkURL',
                          );
        $vld->af->form_vars['namae_url'] = 'http://uga.net';
        $this->assertTrue($vld->validate('namae_url', '', $form_url));

        $vld->af->form_vars['namae_url'] = 'https://uga.net';
        $this->assertTrue($vld->validate('namae_url', '', $form_url));

        $vld->af->form_vars['namae_url'] = 'ftp://uga.net';
        $this->assertTrue($vld->validate('namae_url', '', $form_url));

        $vld->af->form_vars['namae_url'] = 'http://';
        $this->assertTrue($vld->validate('namae_url', '', $form_url));

        $vld->af->form_vars['namae_url'] = '';
        $this->assertTrue($vld->validate('namae_url', '', $form_url));

        // '/'が足りない
        $vld->af->form_vars['namae_url'] = 'http:/';
        $this->assertFalse($vld->validate('namae_url', '', $form_url));

        // 接頭辞がない
        $vld->af->form_vars['namae_url'] = 'hoge@fuga.net';
        $this->assertFalse($vld->validate('namae_url', '', $form_url));




        $form_string = array(
                             'type'          => VAR_TYPE_STRING,
                             'required'      => true,
                             'custom' => 'checkVendorChar',
                             );
        $vld->af->form_vars['namae_string'] = 'http://uga.net';
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0x00);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0x79);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0x80);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0x8e);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0x8f);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0xae);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0xf8);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0xfd);
        $this->assertTrue($vld->validate('namae_string', '', $form_string));

        /* IBM拡張文字 / NEC選定IBM拡張文字 */
        //$c == 0xad || ($c >= 0xf9 && $c <= 0xfc)
        $vld->af->form_vars['namae_string'] = chr(0xad);
        $this->assertFalse($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0xf9);
        $this->assertFalse($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0xfa);
        $this->assertFalse($vld->validate('namae_string', '', $form_string));

        $vld->af->form_vars['namae_string'] = chr(0xfc);
        $this->assertFalse($vld->validate('namae_string', '', $form_string));


    }
}
?>
