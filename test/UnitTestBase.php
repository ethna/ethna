<?php
/**
 *  UnitTestBase.php
 *
 *  @package    Ethna
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 */

/**
 *  Ethnaのテストケースの基底クラス
 */
class Ethna_UnitTestBase extends UnitTestCase
{
    /** @var    object  Ethna_Backend       backendオブジェクト */
    var $backend;

    /** @var    object  Ethna_Controller    コントローラオブジェクト */
    var $controller;

    /** @var    object  Ethna_Controller    コントローラオブジェクト($controllerの省略形) */
    var $ctl;

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト($action_formの省略形) */
    var $af;

    /** @var    object  Ethna_ActionError    アクションエラーオブジェクト($action_errorの省略形) */
    var $ae;

    public function __construct($label = false)
    {
        parent::__construct($label);

        // controller
        $this->ctl = Ethna_Controller::getInstance();
        if ($this->ctl === null) {
            $this->ctl = new Ethna_Controller();
        }
        $this->controller = $this->ctl;

        // backend
        $this->backend = $this->ctl->getBackend();

        // actionform, actionerror.
        if ($this->ctl->getActionForm() === null) {
            $this->ctl->setActionForm(new Ethna_ActionForm($this->ctl));
            $this->backend->setActionForm($this->ctl->getActionForm());
        }
        $this->af = $this->ctl->getActionForm();
        $this->ae = $this->ctl->getActionError();

        // viewclass
        if ($this->ctl->getView() === null) {
            $this->ctl->setView(new Ethna_ViewClass($this->backend, '', ''));
        }
    }

    protected function getNonpublicProperty($object, $property_name)
    {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $ref = new ReflectionProperty(get_class($object), $property_name);
            $ref->setAccessible(true);
            return $ref->getValue($object);
        } else {
            $arr = (array)$object;
            $key = $property_name;

            $ref = new ReflectionProperty(get_class($object), $property_name);
            if ($ref->isProtected()) {
                $key = "\0*\0".$key;
            } elseif ($ref->isPrivate()) {
                $key = "\0".get_class($object)."\0".$key;
            }

            return $arr[$key];
        }
    }
}

