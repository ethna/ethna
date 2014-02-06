<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_MultiArrayTest_ActionForm extends Ethna_ActionForm
{
    protected $form = array(
        //  多次元配列(値はスカラー)
        'User[name]' => array(
            'name'          => '名前',
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
        ),
        'User[phone][home]' => array(
            'name'          => '自宅電話番号',
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
        ),
        'User[phone][mobile]' => array(
            'name'          => '携帯電話番号',
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
        ),

        //  多次元配列(値は配列)
        'Artist[name]' => array(
            'name'          => '好きなキャラクター',
            'type'          => array(VAR_TYPE_STRING),
            'form_type'     => FORM_TYPE_TEXT,
        ),

        //  10階層(1番上の "a" も含む)
        'a[b][c][d][e][f][g][h][i][j]' => array(
            'name'          => '10階層の多次元配列',
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
        ),

        //  11階層(1番上の "a" も含む)
        'a[b][c][d][e][f][g][h][i][j][k]' => array(
            'name'          => '11階層の多次元配列',
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
        ),

        //  自動で番号を割り当てるフォームには
        //  対応していない
        //
        //  [] でフォーム定義をすると、数字としては解釈されず、
        //  空のキーとして解釈される
        //  数値を補正しようとすると、他に[]として定義されている
        //  全フォーム定義を調べなければならなくなる上、順番も保証
        //  できないため、対応していないのは仕様とする
        'invalid[][data1][data2]' => array(
            'name'          => '対応していない多次元フォーム定義',
            'required'      => true,
            'type'          => VAR_TYPE_INT,
            'form_type'     => FORM_TYPE_TEXT,
        ),

        //  重複したフォーム定義
        //  この場合は、どちらの値も思ったように受け取れない可能性が
        //  高い（フォーム定義、GET, POSTされる値の順によっては、片方が
        //  正しく受け取れる場合もある)
        //
        //  なぜなら、$_POST($_GET)に、PHP内で後から解釈した値で
        //  上書きされる上、Ethna_ActionForm#setFormVars 内で、
        //  後で解釈されたフォーム定義の値で上書きされるからである。
        //  どちらの値が後に来るかはブラウザ依存だし、PHP内でどちら
        //  が後に解釈されるかも分からないので、このような定義は避けること
        //
        'duplicate' => array(
            'name'          => '文字列(一次元)',
            'required'      => true,
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
        ),
        'duplicate[abc]' => array(
            'name'          => '文字列(多次元)',
            'required'      => true,
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
        ),
    );
}
// }}}

class Ethna_ActionForm_Validator_MultiDimensionArrayTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->ae = $this->controller->getActionError();
        $this->af = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->af);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->af = new Ethna_MultiArrayTest_ActionForm($this->controller);
        $this->af->clearFormVars();
        $this->ae->clear();
    }

    public function tearDown()
    {
        $_SERVER['REQUEST_METHOD'] = NULL;
        $_POST = array();
        $this->af->clearFormVars();
    }

    public function test_get()
    {
        //
        //   $_POST, $_GET に設定されるデータの方式は
        //   PHP の仕様に従う
        //
        //   http://www.php.net/manual/ja/language.variables.external.php
        //

        //  1. 最下層のキーまで指定して値を取り出す
        $_POST['User']['name'] = '剛田たけし';
        $_POST['User']['phone']['home'] = '01-2345-6789';
        $_POST['User']['phone']['mobile'] = '090-1234-5678';
        $this->af->setFormVars();

        $name = $this->af->get('User[name]');
        $phone_home = $this->af->get('User[phone][home]');
        $phone_mobile = $this->af->get('User[phone][mobile]');

        $this->assertEquals($name, '剛田たけし');
        $this->assertEquals($phone_home, '01-2345-6789');
        $this->assertEquals($phone_mobile, '090-1234-5678');

        //  2. 階層の途中から値を取り出す
        $var = $this->af->get('User[phone]');
        $this->assertEquals($var['home'], '01-2345-6789');
        $this->assertEquals($var['mobile'], '090-1234-5678');

        //  3. 親を指定してデータをすべて取り出す
        $var = $this->af->get('User');
        $this->assertEquals($var['name'], '剛田たけし');
        $this->assertEquals($var['phone']['home'], '01-2345-6789');
        $this->assertEquals($var['phone']['mobile'], '090-1234-5678');
    }

    public function test_get_array()
    {
        $_POST['Artist']['name'][] = '宮崎あおい';
        $_POST['Artist']['name'][] = 'PHPの貴公子';
        $_POST['Artist']['name'][] = '北海道の若頭';
        $this->af->setFormVars();

        //  1. 最下層のキーまで指定して値を取り出す
        $artist0 = $this->af->get('Artist[name][0]');
        $artist1 = $this->af->get('Artist[name][1]');
        $artist2 = $this->af->get('Artist[name][2]');

        $this->assertEquals($artist0, '宮崎あおい');
        $this->assertEquals($artist1, 'PHPの貴公子');
        $this->assertEquals($artist2, '北海道の若頭');

        //  2. 階層の途中から値を取り出す
        $artists = $this->af->get('Artist[name]');
        $this->assertEquals($artists[0], '宮崎あおい');
        $this->assertEquals($artists[1], 'PHPの貴公子');
        $this->assertEquals($artists[2], '北海道の若頭');

        //  3. 親を指定してデータをすべて取り出す
        $allartist = $this->af->get('Artist');
        $this->assertEquals($allartist['name'][0], '宮崎あおい');
        $this->assertEquals($allartist['name'][1], 'PHPの貴公子');
        $this->assertEquals($allartist['name'][2], '北海道の若頭');
    }

    public function test_set()
    {
        // 1. 最下層のキーまで指定して値をセットする
        $this->af->set('User[phone][home]', '01-2345-6789');
        $this->af->set('User[name]', '剛田武');

        $User = $this->af->get('User');
        $this->assertEquals($User['phone']['home'], '01-2345-6789');
        $this->assertEquals($User['name'], '剛田武');

        // 2. 階層の途中から値をセットする
        $this->af->clearFormVars();
        $phone = array(
            'home'   => '01-2345-6789',
            'mobile' => '090-1234-5678',
        );
        $this->af->set('User[phone]', $phone);
        $User = $this->af->get('User');
        $this->assertEquals($User['phone']['home'], '01-2345-6789');
        $this->assertEquals($User['phone']['mobile'], '090-1234-5678');

        //  3. 親を指定してまとめて値をセットする
        $this->af->clearFormVars();
        $user = array (
            'name' => '剛田武',
            'phone' => array (
                'home' => '01-2345-6789',
                'mobile' => '090-1234-5678',
            ),
        );
        $this->af->set('User', $user);
        $User = $this->af->get('User');
        $this->assertEquals($User['name'], '剛田武');
        $this->assertEquals($User['phone']['home'], '01-2345-6789');
        $this->assertEquals($User['phone']['mobile'], '090-1234-5678');
    }

    public function test_set_array()
    {
        $this->af->set('Artist[name][0]', '宮崎あおい');
        $this->af->set('Artist[name][1]', 'PHPの貴公子');
        $this->af->set('Artist[name][2]', '北海道の若頭');

        $artist0 = $this->af->get('Artist[name][0]');
        $artist1 = $this->af->get('Artist[name][1]');
        $artist2 = $this->af->get('Artist[name][2]');

        $this->assertEquals($artist0, '宮崎あおい');
        $this->assertEquals($artist1, 'PHPの貴公子');
        $this->assertEquals($artist2, '北海道の若頭');
    }

    public function test_set_too_deep()
    {
        //   深過ぎる階層の配列は、
        //   たとえ設定しようとしてもNULLになる
        $this->af->set('a[b][c][d][e][f][g][h][i][j]', '10階層');
        $this->af->set('a[b][c][d][e][f][g][h][i][j][k]', '11階層');

        $depth_10_val = $this->af->get('a[b][c][d][e][f][g][h][i][j]');
        $depth_11_val = $this->af->get('a[b][c][d][e][f][g][h][i][j][k]');

        $this->assertEquals($depth_10_val, '10階層');
        $this->assertNULL($depth_11_val);
    }

    public function test_invalid()
    {
        $_POST['invalid'][]['data1']['data2'] = '受け取れません';
        $this->af->setFormVars();
        $this->assertNULL($this->af->get('invalid[][data1][data2]'));
    }
}

