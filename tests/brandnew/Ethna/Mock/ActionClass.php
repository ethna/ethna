<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Mock_ActionClass extends Ethna_ActionClass
{
    public function prepare()
    {
        return null;
    }

    public function authenticate()
    {
        return null;
    }

    public function perform()
    {
        return "index";
    }
}