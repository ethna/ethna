<?php
class {$app_manager}Manager extends Ethna_AppManager
{
}

class {$app_object} extends Ethna_AppObject
{
    /**
     *  @var    array   テーブル定義
     */
    var $table_def = 
        array(
              '{$table}' => 
              array(
                    'primary' => true
                    ),
              );
    
    /**
     *  @var    array   プロパティ定義
     */
    var $prop_def = array(
        {$prop_def}
              );
    
    function getName($key)
    {
        return $this->get($key);
    }
}
?>
