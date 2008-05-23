<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Test
/**
 *  Test Case For Ethna_ActionForm(Mainly Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->form = array();
        $this->ae->clear();
    }
}
// }}}

?>
