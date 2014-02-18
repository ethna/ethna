<?php
// vim: foldmethod=marker
/**
 *  ActionClass.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_ActionClass
/**
 *  action実行クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_ActionClass
{
    /**#@+
     *  @access private
     */

    /** @var Ethna_Backend $backend       backendオブジェクト */
    public $backend;

    /** @var Ethna_Config $config        設定オブジェクト    */
    public $config;

    /** @var Ethna_I18N $i18n          i18nオブジェクト */
    public $i18n;

    /** @var Ethna_ActionError $action_error   アクションエラーオブジェクト */
    public $action_error;

    /** @var Ethna_ActionError $ae   アクションエラーオブジェクト(省略形) */
    public $ae;

    /** @var Ethna_ActionForm $action_form    アクションフォームオブジェクト */
    public $action_form;

    /** @var Ethna_ActionForm $af   アクションフォームオブジェクト(省略形) */
    public $af;

    /** @var Ethna_Session $session       セッションオブジェクト */
    public $session;

    /** @public    object  Ethna_Plugin        プラグインオブジェクト */
    public $plugin;

    /** @var Ethna_Logger $logger    ログオブジェクト */
    public $logger;

    /** @var    array   Preload plugins definition  */
    public $plugins = array();

    /**#@-*/

    /**
     *  Ethna_ActionClassのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Backend   $backend    backendオブジェクト
     */
    public function __construct($backend)
    {
        $c = $backend->getController();
        $this->backend = $backend;
        $this->config = $this->backend->getConfig();
        $this->i18n = $this->backend->getI18N();

        $this->action_error = $this->backend->getActionError();
        $this->ae = $this->action_error;

        $this->action_form = $this->backend->getActionForm();
        $this->af = $this->action_form;

        $this->session = $this->backend->getSession();
        $this->plugin = $this->backend->getPlugin();
        $this->logger = $this->backend->getLogger();

        $this->preloadPlugin();
    }

    /**
     *  アクション実行前の認証処理を行う
     *
     *  @access public
     *  @return string  遷移名(nullなら正常終了, falseなら処理終了)
     */
    public function authenticate()
    {
        return null;
    }

    /**
     *  アクション実行前の処理(フォーム値チェック等)を行う
     *
     *  @access public
     *  @return string  遷移名(nullなら正常終了, falseなら処理終了)
     */
    public function prepare()
    {
        return null;
    }

    /**
     *  アクション実行
     *
     *  @access public
     *  @return string  遷移名(nullなら遷移は行わない)
     */
    public function perform()
    {
        return null;
    }

    /**
     *  get plugin object
     */
    public function preloadPlugin()
    {
        foreach ($this->plugins as $alias => $plugin) {
            $plugin_alias = $alias;
            if (is_int($alias)) {
                $plugin_alias = $plugin;
            }

            $plugin_name = explode('_', $plugin);
            if (count($plugin_name) === 1) {
                $plugin_alias[] = null;
            }

            $this->plugin->setPlugin($plugin_alias, $plugin_name);
        }
    }
}
// }}}
