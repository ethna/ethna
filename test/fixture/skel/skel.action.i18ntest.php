<?php
/**
 *  {$action_path}
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/**
 *  {$action_name} Form implementation.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$action_form} extends {$project_id}_ActionForm
{
    /**
     *  @access private
     *  @var    array   form definition.
     */
    protected $form = array(
       'i18n_sample_name' => array(
           'type' => VAR_TYPE_STRING,
           'name' => 'name_i18n',
       ),
       'i18n_sample_required_error' => array(
           'type' => VAR_TYPE_STRING,
           'required_error' => 'required_error_i18n',
       ),
       'i18n_sample_type_error' => array(
           'type' => VAR_TYPE_STRING,
           'type_error' => 'type_error_i18n',
       ),
       'i18n_sample_min_error' => array(
           'type' => VAR_TYPE_STRING,
           'min_error' => 'min_error_i18n',
       ),
       'i18n_sample_max_error' => array(
           'type' => VAR_TYPE_STRING,
           'max_error' => 'max_error_i18n',
       ),
       'i18n_sample_regexp_error' => array(
           'type' => VAR_TYPE_STRING,
           'regexp_error' => 'regexp_error_i18n',
       ),
       'i18n_sample_all' => array(
           'type' => VAR_TYPE_STRING,
           'name' => 'name_i18n_all',
           'required_error' => 'required_error_i18n_all',
           'type_error' => 'type_error_i18n_all',
           'min_error' => 'min_error_i18n_all',
           'max_error' => 'max_error_i18n_all',
           'regexp_error' => 'regexp_error_i18n_all',
       ),
    );

    function _filter_sample($value)
    {
        _et('actionform filter');
    }
}

/**
 *  {$action_name} action implementation.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$action_class} extends {$project_id}_ActionClass
{
    /**
     *  preprocess of {$action_name} Action.
     *
     *  @access public
     *  @return string    forward name(null: success.
     *                                false: in case you want to exit.)
     */
    public function prepare()
    {
        _et('action prepare');
        _et("action
prepare
 multiple
  line");
        return null;
    }

    /**
     *  {$action_name} action implementation.
     *
     *  @access public
     *  @return string  forward name.
     */
    public function perform()
    {
        _et("action perform");
        return '{$action_name}';
    }
}

