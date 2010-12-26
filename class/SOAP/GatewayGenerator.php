<?php
// vim: foldmethod=marker
/**
 *  GatewayGenerator.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_SOAP_GatewayGenerator
/**
 *  指定されたコントローラに対応するゲートウェイクラスコードを生成するクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna   
 */
class Ethna_SOAP_GatewayGenerator
{
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    var $controller;

    /** @var    object  Ethna_Config        設定オブジェクト */
    var $config;

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト */
    var $action_error;

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト(省略形) */
    var $ae;

    /** @var    string      ゲートウェイクラスコード */
    var $gateway;

    /** @var    string      ゲートウェイクラス識別名 */
    var $name;

    /** @var    string      ゲートウェイクラスネームスペース */
    var $namespace;

    /**#@-*/

    /**
     *  Ethna_SOAP_GatewayGeneratorクラスのコンストラクタ
     *
     *  @access public
     */
    public function __construct()
    {
        $this->controller = Ethna_Controller::getInstance();
        $this->config = $this->controller->getConfig();
        $this->action_error = null;
        $this->ae = $this->action_error;
        $this->gateway = "";
        $this->name = $this->controller->getAppId();
        $this->namespace = $this->_getNameSpace();
    }

    /**
     *  ゲートウェイクラスコードを生成する
     *
     *  @access public
     *  @return string  ゲートウェクラスコード
     */
    function generate()
    {
        $prev_type = $this->controller->getClientType();
        $this->controller->setClientType(CLIENT_TYPE_SOAP);

        $this->gateway .= $this->_getHeader();
        $this->gateway .= $this->_getEntry();
        $this->gateway .= $this->_getFooter();

        $this->controller->setClientType($prev_type);

        return $this->gateway;
    }

    /**
     *  ゲートウェイクラスのクラス名を取得する
     *
     *  @access public
     *  @return string  ゲートウェイクラスのクラス名
     */
    function getClassName()
    {
        return sprintf("Ethna_SOAP_%sGateway", $this->name);
    }

    /**
     *  ゲートウェイクラスコード(ヘッダ部分)を取得する
     *
     *  @access private
     *  @return string  ゲートウェイクラスコード(ヘッダ部分)
     */
    function _getHeader()
    {
        $header = sprintf("class Ethna_SOAP_%sGateway extends Ethna_SOAP_Gateway {\n", $this->name);

        return $header;
    }

    /**
     *  ゲートウェイクラスコード(メソッドエントリ部分)を取得する
     *
     *  @access private
     *  @return string  ゲートウェイクラスコード(メソッドエントリ部分)
     */
    function _getEntry()
    {
        $entry = "";
        foreach ($this->controller->soap_action as $k => $v) {
            $action_form_name = $this->controller->getActionFormName($k);
            $form = new $action_form_name($this->controller);
            $arg_list = array_keys($form->form);

            $entry .= "  function $k(";
            for ($i = 0; $i < count($arg_list); $i++) {
                if ($i > 0) {
                    $entry .= ", ";
                }
                $entry .= "\$" . $arg_list[$i];
            }
            $entry .= ") {\n";

            $entry .= "    \$_SERVER['REQUEST_METHOD'] = 'post';\n";
            $entry .= "    \$_POST['action_$k'] = 'dummy';\n";
            foreach ($arg_list as $arg) {
                $entry .= "    \$_POST['$arg'] = \$$arg;\n";
            }
            
            $entry .= "    \$this->dispatch();\n";

            $entry .= "    \$app = \$this->getApp();\n";
            $entry .= "    \$errorcode = \$this->getErrorCode();\n";
            $entry .= "    \$errormessage = \$this->getErrorMessage();\n";
            $entry .= "    \$retval = array();\n";
            foreach ($form->retval as $k => $v) {
                $entry .= "    \$retval['$k'] = \$app['$k'];\n";
            }
            $entry .= "    \$retval['errorcode'] = \$errorcode;\n";
            $entry .= "    \$retval['errormessage'] = \$errormessage;\n";

            $entry .= "    return \$retval;\n";
            $entry .= "  }\n";
        }
        return $entry;
    }

    /**
     *  ゲートウェイクラスコード(フッタ部分)を取得する
     *
     *  @access private
     *  @return string  ゲートウェイクラスコード(フッタ部分)
     */
    function _getFooter()
    {
        $footer = "}\n";

        return $footer;
    }

    /**
     *  ネームスペースを取得する
     *
     *  @access private
     *  @return string  ネームスペース
     */
    function _getNameSpace()
    {
        return sprintf("%s/%s", $this->config->get('url'), $this->name);
    }
}
// }}}
