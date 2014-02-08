<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Mock_ActionForm extends Ethna_ActionForm
{
    protected $form = array(
        "name" => array(
            'type' => VAR_TYPE_STRING,
            'name' => 'User Name',
        ),
    );
}
