<?php
// vim: foldmethod=marker
/**
 *	soap_wsdl.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_WsdlGenerator
/**
 *	指定されたコントローラに対応するWSDLを生成するクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_WsdlGenerator
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	Ethna_Controller	controllerオブジェクト
	 */
	var	$controller;

	/**
	 *	@var	object	Ethna_Config		設定オブジェクト
	 */
	var	$config;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var	$action_error;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト(省略形)
	 */
	var	$ae;

	/**
	 *	@var	string		WSDL
	 */
	var	$wsdl;

	/**
	 *	@var	string		ゲートウェイクラスコード
	 */
	var	$gateway;

	/**
	 *	@var	string		ゲートウェイクラス識別名
	 */
	var	$name;

	/**
	 *	@var	string		ゲートウェイクラスネームスペース
	 */
	var	$namespace;

	/**#@-*/

	/**
	 *	Ethna_WsdlGeneratorクラスのコンストラクタ
	 */
	function Ethna_WsdlGenerator($gateway)
	{
		$this->controller =& $GLOBALS['controller'];
		$this->config =& $this->controller->getConfig();
		$this->action_error = null;
		$this->ae =& $this->action_error;
		$this->wsdl = "";
		$this->name = $this->controller->getAppId();
		$this->namespace = $this->_getNameSpace();
		$this->gateway = $gateway;
	}

	/**
	 *	WSDLを生成する
	 *
	 *	@access	public
	 *	@return	string	WSDL
	 */
	function generate()
	{
		$current_type = $this->controller->getClientType();
		$this->controller->setClientType(CLIENT_TYPE_SOAP);

		$this->wsdl .= $this->_getHeader();
		$this->wsdl .= $this->_getTypes();
		$this->wsdl .= $this->_getMessage();
		$this->wsdl .= $this->_getPortType();
		$this->wsdl .= $this->_getBinding();
		$this->wsdl .= $this->_getService();
		$this->wsdl .= $this->_getFooter();

		$this->controller->setClientType($current_type);

		return $this->wsdl;
	}

	/**
	 *	WSDL(ヘッダ部分)を取得する
	 *
	 *	@access	private
	 *	@return	string	WSDL(ヘッダ部分)
	 */
	function _getHeader()
	{
		$header = <<< EOD
<?xml version="1.0" encoding="utf-8"?>
<definitions xmlns:http="http://schemas.xmlsoap.org/wsdl/http/"
	xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
	xmlns:s="http://www.w3.org/2001/XMLSchema"
	xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
	xmlns:tm="http://microsoft.com/wsdl/mime/textMatching/"
	xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/"
	xmlns:tns="%s"
	targetNamespace="%s"
	name="%s"
	xmlns="http://schemas.xmlsoap.org/wsdl/">\n\n
EOD;
		return sprintf($header, $this->namespace, $this->namespace, $this->name);
	}

	/**
	 *	WSDL(型定義部分)を取得する
	 *
	 *	@access	private
	 *	@return	string	WSDL(型定義部分)
	 */
	function _getTypes()
	{
		$types = sprintf(" <types>\n  <s:schema targetNamespace=\"%s\">\n", $this->namespace);

		// 基本型
		$types .= <<< EOD
   <s:complexType name="ArrayOfInt">
    <s:complexContent mixed="false">
     <s:restriction base="soapenc:Array">
      <s:attribute d7p1:arrayType="s:int[]" ref="soapenc:arrayType" xmlns:d7p1="http://schemas.xmlsoap.org/wsdl/" />
     </s:restriction>
    </s:complexContent>
   </s:complexType>
   <s:complexType name="ArrayOfString">
    <s:complexContent mixed="false">
     <s:restriction base="soapenc:Array">
      <s:attribute d7p1:arrayType="s:string[]" ref="soapenc:arrayType" xmlns:d7p1="http://schemas.xmlsoap.org/wsdl/" />
     </s:restriction>
    </s:complexContent>
   </s:complexType>
   <s:complexType name="Result">
    <s:sequence>
     <s:element name="errormessage" type="s:string" />
     <s:element name="errorcode" type="s:int" />
    </s:sequence>
   </s:complexType>\n
EOD;
		
		// アクション固有
		foreach ($this->controller->soap_action as $k => $v) {
			$action_form_name = $this->controller->getActionFormName($k);
			$form =& new $action_form_name($this->controller);
			if ($form->retval == null) {
				continue;
			}

			// デフォルトエントリを追加
			Ethna_SoapUtil::fixRetval($form->retval);

			// シリアライズ
			$retval_name = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($k)) . "Result";
			$types .= $this->_serializeTypes($form->retval, $retval_name);
		}

		return $types . "  </s:schema>\n </types>\n\n";
	}

	/**
	 *	WSDL(Message部分)を取得する
	 *
	 *	@access	private
	 *	@return	string	WSDL(Message部分)
	 *	@todo	respect access controlls
	 */
	function _getMessage()
	{
		$n = 1;
		$message = "";
		foreach ($this->controller->soap_action as $k => $v) {
			$message .= $this->_serializeMessage($k, $n);
			$n++;
		}

		return $message . "\n";
	}

	/**
	 *	WSDL(PortType部分)を取得する
	 *
	 *	@access	private
	 *	@return	string	WSDL(PortType部分)
	 */
	function _getPortType()
	{
		$port_type = sprintf(" <portType name=\"%sSoap\">\n", $this->name);

		$n = 1;
		foreach ($this->controller->soap_action as $k => $v) {
			$port_type .= $this->_serializePortType($k, $n);
			$n++;
		}

		$port_type .= " </portType>\n\n";

		return $port_type;
	}

	/**
	 *	WSDL(Binding部分)を取得する
	 *
	 *	@access	private
	 *	@return string	WSDL(Binding部分)
	 */
	function _getBinding()
	{
		$namespace = "urn:" . $this->name;
		$binding = " <binding name=\"" . $this->name . "Soap\" type=\"tns:" . $this->name . "Soap\">\n";
		$binding .= "  <soap:binding style=\"rpc\" transport=\"http://schemas.xmlsoap.org/soap/http\" />\n";

		$n = 1;
		foreach ($this->controller->soap_action as $k => $v) {
			$binding .= "  <operation name=\"$k\">\n";
			$binding .= "   <soap:operation soapAction=\"$k\" style=\"rpc\" />\n";
			$binding .= "   <input name=\"${k}${n}SoapIn\">\n";
			$binding .= "    <soap:body use=\"encoded\" namespace=\"$namespace\" encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\" />\n";
			$binding .= "   </input>\n";
			$binding .= "   <output name=\"${k}${n}SoapOut\">\n";
			$binding .= "    <soap:body use=\"encoded\" namespace=\"$namespace\" encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\" />\n";
			$binding .= "   </output>\n";
			$binding .= "  </operation>\n";
			$n++;
		}
		$binding .= " </binding>\n";

		return $binding;
	}

	/**
	 *	WSDL(Service部分)を取得する
	 *
	 *	@access	private
	 *	@return	string	WSDL(Service部分)
	 */
	function _getService()
	{
		$name = $this->name;
		$gateway= $this->gateway;
		$service = " <service name=\"$name\">\n";
		$service .= "  <port name=\"${name}Soap\" binding=\"tns:${name}Soap\">\n";
		$service .= "   <soap:address location=\"$gateway\" />\n";
		$service .= "  </port>\n";
		$service .= " </service>\n";

		return $service;
	}

	/**
	 *	WSDL(フッタ部分)を取得する
	 *
	 *	@access	private
	 *	@return	string	WSDL(フッタ部分)
	 */
	function _getFooter()
	{
		return "</definitions>\n";
	}

	/**
	 *	ネームスペースを取得する
	 *
	 *	@access	private
	 *	@return	string	ネームスペース
	 */
	function _getNameSpace()
	{
		return sprintf("%s/%s", $this->config->get('url'), $this->name);
	}

	/**
	 *	型のシリアライズ
	 *
	 *	@access	private
	 *	@param	array	$def	型定義
	 *	@param	string	$name	変数名
	 *	@return	string	シリアライズされた型定義
	 */
	function _serializeTypes($def, $name)
	{
		if (is_array($def) == false) {
			// nothing to do
			return;
		}

		$types = $this->__serializeTypes($def, $name);

		foreach ($def as $k => $v) {
			if (is_array($def[$k]) == false || Ethna_SoapUtil::isArrayOfScalar($def[$k])) {
				continue;
			}
			$types .= $this->_serializeTypes($def[$k], $k);
		}

		return $types;
	}

	/**
	 *	型のシリアライズ(エレメント対応)
	 *
	 *	@access	private
	 *	@param	array	$def	型定義
	 *	@param	string	$name	変数名
	 *	@return	string	シリアライズされた型定義(各要素)
	 */
	function __serializeTypes($def, $name)
	{
		$keys = array_keys($def);

		if (Ethna_SoapUtil::isArrayOfObject($def)) {
			$array_name = sprintf("ArrayOf%s", $keys[0]);
			$name = $keys[0];
			$types = "   <s:complexType name=\"$array_name\">\n";
			$types .= "    <s:complexContent mixed=\"false\">\n";
			$types .= "     <s:restriction base=\"soapenc:Array\">\n";
			$types .= "      <s:attribute d7p1:arrayType=\"tns:$name" . "[]" . "\" " .
				"ref=\"soapenc:arrayType\" xmlns:d7p1=\"http://schemas.xmlsoap.org/wsdl/\" />\n";
			$types .= "     </s:restriction>\n";
			$types .= "    </s:complexContent>\n";
			$types .= "   </s:complexType>\n";
			return $types;
		}

		$types = "   <s:complexType name=\"$name\">\n";
		$types .= "    <s:sequence>\n";
		foreach ($keys as $key) {
			if (is_array($def[$key])) {
				$inner_keys = array_keys($def[$key]);
				if (is_array($def[$key][$inner_keys[0]])) {
					$inner_name = sprintf("ArrayOf%s", $inner_keys[0]);
					$types .= "     <s:element name=\"$key\" type=\"tns:$inner_name\" />\n";
				} else {
					$type_name = "tns:" . Ethna_SoapUtil::getArrayTypeName($def[$key][$inner_keys[0]]);
					$types .= "     <s:element name=\"$key\" type=\"$type_name\" />\n";
				}
			} else {
				$type_name = Ethna_SoapUtil::getScalarTypeName($def[$key]);
				$types .= "     <s:element name=\"$key\" type=\"s:$type_name\" />\n";
			}
		}
		$types .= "    </s:sequence>\n";
		$types .= "   </s:complexType>\n";

		return $types;
	}

	/**
	 *	Messageのシリアライズ
	 *
	 *	@access	private
	 *	@param	string	$name	message名
	 *	@param	int		$serno	message連番
	 *	@return	string	シリアライズされたmessage
	 */
	function _serializeMessage($name, $serno)
	{
		$action_form_name = $this->controller->getActionFormName($name);
		$form =& new $action_form_name($this->controller);

		/* SoapIn */
		$message = " <message name=\"${name}${serno}SoapIn\">\n";
		$keys = array();
		if (is_array($form->form)) {
			$keys = array_keys($form->form);
		}
		foreach ($keys as $key) {
			$type_id =& $form->form[$key]['type'];
			if (is_array($type_id)) {
				$type_keys = array_keys($type_id);
				$ttype = "tns:" . Ethna_SoapUtil::getArrayTypeName($type_id[$type_keys[0]]);
			} else {
				$type = "s:" . Ethna_SoapUtil::getScalarTypeName($type_id);
			}
			$message .= "  <part name=\"$key\" type=\"$type\" />\n";
		}
		$message .= " </message>\n";

		/* SoapOut */
		$message .= " <message name=\"${name}${serno}SoapOut\">\n";
		if ($form->retval == null) {
			$type = "tns:Result";
		} else {
			$type = "tns:${name}Result";
		}
		$message .= "  <part name=\"result\" type=\"$type\" />\n";
		$message .= " </message>\n";

		return $message;
	}

	/**
	 *	PortTypeのシリアライズ
	 *
	 *	@access	private
	 *	@param	string	$name	porttype名
	 *	@param	int		$serno	porttype連番
	 *	@return	string	シリアライズされたporttype
	 */
	function _serializePortType($name, $serno)
	{
		$action_form_name = $this->controller->getActionFormName($name);
		$form =& new $action_form_name($this->controller);

		$args = null;
		if (is_array($form->form)) {
			$args = implode(' ', array_keys($form->form));
		}

		$port_type = "  <operation name=\"$name\" parameterOrder=\"$args\">\n";
		$port_type .= "   <input name=\"${name}${serno}SoapIn\" message=\"tns:${name}${serno}SoapIn\" />\n";
		$port_type .= "   <output name=\"${name}${serno}SoapOut\" message=\"tns:${name}${serno}SoapOut\" />\n";
		$port_type .= "  </operation>\n";

		return $port_type;
	}
}
// }}}
?>
