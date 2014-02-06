<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ActionForm_Dummy extends Ethna_ActionForm
{
    protected $form = array(
        'test' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test',
        ),

        'no_name' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
        ),

        'test_array' => array(
            'type' => array(VAR_TYPE_STRING),
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test array',
        ),

    );


    public function getFormValue($name)
    {
        if (isset($this->form[$name])) {
            return $this->form[$name];
        }

        return false;
    }
}
