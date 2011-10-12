<?php
// vim: foldmethod=marker
/**
 *  Smarty.php
 *
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Renderer_Smarty
/**
 *  Smarty rendere class
 *
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Renderer_Smarty extends Ethna_Renderer
{
    /** @private    string compile directory  */
    private $compile_dir;

    /** @protected  engine path (library) */
    protected $engine_path = 'Smarty/Smarty.class.php';

    /**
     *  Ethna_Renderer_Smartyクラスのコンストラクタ
     *
     *  @access public
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        // get renderer config
        $smarty_config = isset($this->config['smarty'])
            ? $this->config['smarty']
            : array();

        // load template engine
        $this->loadEngine($smarty_config);

        $this->engine = new Smarty;

        // ディレクトリ関連は Controllerによって実行時に設定
        // TODO: iniファイルによって上書き可にするかは要検討
        $template_dir = $controller->getTemplatedir();
        $compile_dir = $controller->getDirectory('template_c');

        $this->setTemplateDir($template_dir);
        $this->compile_dir = $compile_dir;
        $this->engine->template_dir = $this->template_dir;
        $this->engine->compile_dir = $this->compile_dir;
        $this->engine->compile_id = md5($this->template_dir);

        // delimiter setting
        if (array_key_exists('left_delimiter', $smarty_config)) {
            $this->engine->left_delimiter = $smarty_config['left_delimiter'];
        }
        if (array_key_exists('right_delimiter', $smarty_config)) {
            $this->engine->right_delimiter = $smarty_config['right_delimiter'];
        }

        // コンパイルディレクトリは必須なので一応がんばってみる
        if (is_dir($this->engine->compile_dir) === false) {
            Ethna_Util::mkdir($this->engine->compile_dir, 0755);
        }

        $this->engine->plugins_dir = array_merge(
            $controller->getDirectory('plugins'),
            array(ETHNA_BASE . '/class/Plugin/Smarty', SMARTY_DIR . 'plugins')
        );
    }

    /**
     *  ビューを出力する
     *
     *  @param  string  $template   テンプレート名
     *  @param  bool    $capture    true ならば出力を表示せずに返す
     *
     *  @access public
     */
    function perform($template = null, $capture = false)
    {
        if ($template === null && $this->template === null) {
            return Ethna::raiseWarning('template is not defined');
        }

        if ($template !== null) {
            $this->template = $template;
        }

        if ((is_absolute_path($this->template) && is_readable($this->template))
            || is_readable($this->template_dir . $this->template)) {
                if ($capture === true) {
                    $captured = $this->engine->fetch($this->template);
                    return $captured;
                } else {
                    $this->engine->display($this->template);
                }
        } else {
            return Ethna::raiseWarning('template not found ' . $this->template, 500);
        }
    }

    /**
     * テンプレート変数を取得する
     *
     *  @param string $name  変数名
     *
     *  @return mixed　変数
     *
     *  @access public
     */
    function getProp($name = null)
    {
        $property = $this->engine->get_template_vars($name);

        if ($property !== null) {
            return $property;
        }
        return null;
    }

    /**
     *  テンプレート変数を削除する
     *
     *  @param name    変数名
     *
     *  @access public
     */
    function removeProp($name)
    {
        $this->engine->clear_assign($name);
    }

    /**
     *  テンプレート変数に配列を割り当てる
     *
     *  @param array $array
     *
     *  @access public
     */
    function setPropArray($array)
    {
        $this->engine->assign($array);
    }

    /**
     *  テンプレート変数に配列を参照として割り当てる
     *
     *  @param array $array
     *
     *  @access public
     */
    function setPropArrayByRef(&$array)
    {
        $this->engine->assign_by_ref($array);
    }

    /**
     *  テンプレート変数を割り当てる
     *
     *  @param string $name 変数名
     *  @param mixed $value 値
     *
     *  @access public
     */
    function setProp($name, $value)
    {
        $this->engine->assign($name, $value);
    }

    /**
     *  テンプレート変数に参照を割り当てる
     *
     *  @param string $name 変数名
     *  @param mixed $value 値
     *
     *  @access public
     */
    function setPropByRef($name, &$value)
    {
        $this->engine->assign_by_ref($name, $value);
    }

    /**
     *  プラグインをセットする
     *
     *  @param string $name　プラグイン名
     *  @param string $type プラグインタイプ
     *  @param mixed $plugin プラグイン本体
     *
     *  @access public
     */
    function setPlugin($name, $type, $plugin)
    {
        //プラグイン関数の有無をチェック
        if (is_callable($plugin) === false) {
            return Ethna::raiseWarning('Does not exists.');
        }

        //プラグインの種類をチェック
        $register_method = 'register_' . $type;
        if (method_exists($this->engine, $register_method) === false) {
            return Ethna::raiseWarning('This plugin type does not exist');
        }

        // フィルタは名前なしで登録
        if ($type === 'prefilter' || $type === 'postfilter' || $type === 'outputfilter') {
            parent::setPlugin($name, $type, $plugin);
            $this->engine->$register_method($plugin);
            return;
        }

        // プラグインの名前をチェック
        if ($name === '') {
            return Ethna::raiseWarning('Please set plugin name');
        }

        // プラグインを登録する
        parent::setPlugin($name, $type, $plugin);
        $this->engine->$register_method($name, $plugin);
    }
}
// }}}
