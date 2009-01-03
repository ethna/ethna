<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm_MultiArray_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{  Ethna_MultiArrayTest_ActionForm
/**
 *  Test ActionForm (MultiDimentional Array) 
 *
 *  @access public
 */
class Ethna_MultiArrayTest_ActionForm extends Ethna_ActionForm
{
    var $form = array(
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

// {{{  Ethna_ActionForm_MultiArray_Test
class Ethna_ActionForm_MultiArray_Test extends Ethna_UnitTestBase
{
    var $local_af;

    function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->local_af = new Ethna_MultiArrayTest_ActionForm($this->ctl); 
        $this->local_af->clearFormVars();
        $this->ae->clear();
    }

    function tearDown()
    {
        $_SERVER['REQUEST_METHOD'] = NULL;
        $_POST = array();
        $this->local_af->clearFormVars();
    }

    // {{{  get
    function test_get()
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
        $this->local_af->setFormVars();

        $name = $this->local_af->get('User[name]');
        $phone_home = $this->local_af->get('User[phone][home]');
        $phone_mobile = $this->local_af->get('User[phone][mobile]');

        $this->assertEqual($name, '剛田たけし');
        $this->assertEqual($phone_home, '01-2345-6789');
        $this->assertEqual($phone_mobile, '090-1234-5678');

        //  2. 階層の途中から値を取り出す
        $var = $this->local_af->get('User[phone]');
        $this->assertEqual($var['home'], '01-2345-6789');
        $this->assertEqual($var['mobile'], '090-1234-5678');

        //  3. 親を指定してデータをすべて取り出す
        $var = $this->local_af->get('User');
        $this->assertEqual($var['name'], '剛田たけし');
        $this->assertEqual($var['phone']['home'], '01-2345-6789');
        $this->assertEqual($var['phone']['mobile'], '090-1234-5678');
    }
    // }}}

    // {{{  get(array)
    function test_get_array()
    {
        $_POST['Artist']['name'][] = '宮崎あおい';
        $_POST['Artist']['name'][] = 'PHPの貴公子';
        $_POST['Artist']['name'][] = '北海道の若頭';
        $this->local_af->setFormVars();

        //  1. 最下層のキーまで指定して値を取り出す
        $artist0 = $this->local_af->get('Artist[name][0]');
        $artist1 = $this->local_af->get('Artist[name][1]');
        $artist2 = $this->local_af->get('Artist[name][2]');

        $this->assertEqual($artist0, '宮崎あおい');
        $this->assertEqual($artist1, 'PHPの貴公子');
        $this->assertEqual($artist2, '北海道の若頭');
        
        //  2. 階層の途中から値を取り出す
        $artists = $this->local_af->get('Artist[name]');
        $this->assertEqual($artists[0], '宮崎あおい');
        $this->assertEqual($artists[1], 'PHPの貴公子');
        $this->assertEqual($artists[2], '北海道の若頭');

        //  3. 親を指定してデータをすべて取り出す
        $allartist = $this->local_af->get('Artist');
        $this->assertEqual($allartist['name'][0], '宮崎あおい');
        $this->assertEqual($allartist['name'][1], 'PHPの貴公子');
        $this->assertEqual($allartist['name'][2], '北海道の若頭');
    }
    // }}}

    // {{{  get(duplicate)
    function test_get_duplicate()
    {
        //  PHP 5.2.6, 4.4.9 では 「宮崎あおい」が優先された
        //  つまり、$_POST の中身は array('duplicate' => '宮崎あおい')
        $_POST['duplicate'] = '宮崎あおい';
        $_POST['duplicate']['abc'] = 'PHPの貴公子';
        $this->local_af->setFormVars();

        //
        //  setFormVars が実行された結果、[duplicate][abc] のフォーム定義が
        //  後で解釈され、$POST array('dupliate'=>array('abc' => NULL));
        //  として上書きされる。よって、どちらも値が取得できない
        //
        $this->assertNotEqual('宮崎あおい', $this->local_af->get('duplicate'));
        $this->assertNULL($this->local_af->get('duplicate[xxx]'));
    }
    // }}}

    // {{{  get(too deep)
    function test_get_too_deep()
    {
        //   10階層目, 11階層目に値を設定する
        $_POST['a']['b']['c']['d']['e']['f']['g']['h']['i']['j'] = '10階層';
        $_POST['a']['b']['c']['d']['e']['f']['g']['h']['i']['j']['k'] = '11階層';
        $this->local_af->setFormVars();

        //   深過ぎる階層の配列は、
        //   たとえ定義されていても、取得しようとしてもNULLになる
        $depth_10_val = $this->local_af->get('a[b][c][d][e][f][g][h][i][j]');
        $depth_11_val = $this->local_af->get('a[b][c][d][e][f][g][h][i][j][k]');

        $this->assertEqual($depth_10_val, '10階層');
        $this->assertNULL($depth_11_val);
    }
    // }}}

    // {{{  set 
    function test_set()
    {
        // 1. 最下層のキーまで指定して値をセットする
        $this->local_af->set('User[phone][home]', '01-2345-6789');
        $this->local_af->set('User[name]', '剛田武');

        $User = $this->local_af->get('User');
        $this->assertEqual($User['phone']['home'], '01-2345-6789');
        $this->assertEqual($User['name'], '剛田武');

        // 2. 階層の途中から値をセットする
        $this->local_af->clearFormVars();
        $phone = array(
            'home'   => '01-2345-6789',
            'mobile' => '090-1234-5678',
        );
        $this->local_af->set('User[phone]', $phone);
        $User = $this->local_af->get('User');
        $this->assertEqual($User['phone']['home'], '01-2345-6789');
        $this->assertEqual($User['phone']['mobile'], '090-1234-5678');

        //  3. 親を指定してまとめて値をセットする
        $this->local_af->clearFormVars();
        $user = array (
            'name' => '剛田武',
            'phone' => array (
                'home' => '01-2345-6789',
                'mobile' => '090-1234-5678',
            ),
        );
        $this->local_af->set('User', $user);
        $User = $this->local_af->get('User');
        $this->assertEqual($User['name'], '剛田武');
        $this->assertEqual($User['phone']['home'], '01-2345-6789');
        $this->assertEqual($User['phone']['mobile'], '090-1234-5678');
    }
    // }}}

    // {{{  set(array)
    function test_set_array()
    {
        $this->local_af->set('Artist[name][0]', '宮崎あおい');
        $this->local_af->set('Artist[name][1]', 'PHPの貴公子');
        $this->local_af->set('Artist[name][2]', '北海道の若頭');

        $artist0 = $this->local_af->get('Artist[name][0]');
        $artist1 = $this->local_af->get('Artist[name][1]');
        $artist2 = $this->local_af->get('Artist[name][2]');

        $this->assertEqual($artist0, '宮崎あおい');
        $this->assertEqual($artist1, 'PHPの貴公子');
        $this->assertEqual($artist2, '北海道の若頭');
    }
    // }}}

    // {{{  set(too deep)
    function test_set_too_deep()
    {
        //   深過ぎる階層の配列は、
        //   たとえ設定しようとしてもNULLになる
        $this->local_af->set('a[b][c][d][e][f][g][h][i][j]', '10階層');
        $this->local_af->set('a[b][c][d][e][f][g][h][i][j][k]', '11階層');

        $depth_10_val = $this->local_af->get('a[b][c][d][e][f][g][h][i][j]');
        $depth_11_val = $this->local_af->get('a[b][c][d][e][f][g][h][i][j][k]');

        $this->assertEqual($depth_10_val, '10階層');
        $this->assertNULL($depth_11_val);
    }
    // }}}

    // {{{ invalid multidimention def
    function test_invalid()
    {
        $_POST['invalid'][]['data1']['data2'] = '受け取れません';
        $this->local_af->setFormVars();
        $this->assertNULL($this->local_af->get('invalid[][data1][data2]'));
    }
    // }}}
}
// }}}

?>
