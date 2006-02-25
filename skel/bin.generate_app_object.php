<?php
// vim: foldmethod=marker
/**
 *  generate_app_object.php
 *
 *  @author     yourname
 *  @package    {$project_id}
 *  @version    $Id$
 */
chdir(dirname(__FILE__));
include_once('../app/{$project_id}_Controller.php');

ini_set('max_execution_time', 0);

// {{{ {$project_id}_SkeltonGenerator
/**
 *  {$project_id}_SkeltonGenerator
 *
 *
 */
class {$project_id}_SkeltonGenerator extends Ethna_SkeltonGenerator
{

    function generateAppObjectSkelton($table_name)
    {

        $c =& Ethna_Controller::getInstance();
        $app_id = $c->getAppId();
        $backend =& $c->getBackend();
        $db = & $backend->getDB();

        $r = $db->db->tableInfo($table_name);
        if(Ethna::isError($r)){
            die($r->getMessage().": $table_name \n");
        }

        $macro = array();
        $macro['project_id'] = $app_id;
        $macro['prop_def'] = $this->_getPropDef($r);
        $macro['table']    = $table_name;
        $macro['app_manager'] = ucfirst($app_id) . '_'.$this->_ucfirst($table_name);
        $macro['app_object']  = ucfirst($app_id) . '_'.$this->_ucfirst($table_name);

        $dir  = $c->getBasedir().'/lib/';
        $file = $dir. ucfirst($app_id). '_'.$this->_ucfirst($table_name).'.php';

        if($this->_generateFile('skel.app_object.php', $file, $macro)){
            printf("app_object script successfully created [%s]\n", $file);
        }

    }

    function _ucfirst($str)
    {
        return str_replace(' ','', ucwords(str_replace('_',' ',$str)));
    }
    
    function _getPrimary($field)
    {
        if(strpos($field['flags'],"primary_key") === false){
            return "false";
        }else{
            return "true";
        }
    }

    function _getKey($field)
    {
        if(strpos($field['flags'],"key") === false){
            return "false";
        }else{
            return "true";
        }
    }

    function _getType($field)
    {
        switch($field['type']){
        case 'int':
            return 'VAR_TYPE_INT';
        case 'datetime':
            return 'VAR_TYPE_DATETIME';
        case 'blob':
        default:
            return 'VAR_TYPE_STRING';
        }
    }

    function _getPropDef($tableInfo)
    {

        $res = '';
        foreach($tableInfo as $i => $field){

            $primary = $this->_getPrimary($field);
            $key     = $this->_getKey($field);
            $type    = $this->_getType($field);
            
            $str = <<<HHH
        '{$field['name']}' => 
                array(
                      'primary'   => $primary, 
                      'key'       => $key, 
                      'type'      => $type,
                      'form_name' => '{$field['name']}',
                      ),

HHH;
                
            $res .= $str;

        }
        return $res;
    }
}

// }}}

// {{{ {$project_id}_Action_CliGenerateAppObject
class {$project_id}_Action_CliGenerateAppObject extends Ethna_CLI_ActionClass
{

    /**
     *  cli_generate_app_objectアクションの実行
     *
     *  @access public
     */
    function perform()
    {

        parent::perform();

        if (count($_SERVER['argv']) != 2) {
            return $this->_usage();
        }

        $table_name = $_SERVER['argv'][1];

        $sg = new {$project_id}_SkeltonGenerator();
        $sg->generateAppObjectSkelton($table_name);

    }

    /**
     *  usageを表示する
     *
     *  @access private
     */
    function _usage()
    {
        printf("%s [table name]\n", $_SERVER['argv'][0]);
    }
}
// }}}

{$project_id}_Controller::main_CLI('{$project_id}_Controller', 'cli_generate_app_object');
?>

