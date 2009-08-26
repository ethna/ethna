<?php
/**
 *  Ethna_MocktestManager.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com> 
 *  @package    Ethna 
 *  @version    $Id$
 */

/**
 *  Ethna_MocktestManager
 *  アプリケーションマネージャーテスト用のダミークラス
 */
class Ethna_MocktestManager extends Ethna_AppManager
{
    //  何も定義しない 
}

/**
 *  Ethna_Mocktest
 *  アプリケーションオブジェクトテスト用のダミークラス
 */
class Ethna_Mocktest extends Ethna_AppObject
{
    /**
     *  property display name getter.
     *
     *  @access public
     */
    function getName($key)
    {
        return $this->get($key);
    }
}

