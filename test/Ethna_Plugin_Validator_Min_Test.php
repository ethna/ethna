<?php
/**
 *  Ethna_Plugin_Validator_Min_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Minクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Min_Test extends Ethna_UnitTestBase
{
    function testCheckValidatorMin()
    {
		$ctl =& Ethna_Controller::getInstance();
		$plugin =& $ctl->getPlugin();
		$vld = $plugin->getPlugin('Validator', 'Min');


		$form_int = array(
						  'type'          => VAR_TYPE_INT,
						  'required'      => true,
						  'min'           => '10',
						  'error'         => '{form}には10以上の数字(整数)を入力して下さい'
						  );
		$vld->af->setDef('namae_int', $form_int);

		$pear_error = $vld->validate('namae_int', 12, $form_int);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_int', 10, $form_int);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_int', '', $form_int);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_int', 10.5, $form_int);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		// minより小さい値
		$pear_error = $vld->validate('namae_int', -2, $form_int);
		$this->assertTrue(is_a($pear_error, 'PEAR_Error'));
		$this->assertEqual(E_FORM_MIN_INT,$pear_error->getCode());
		$this->assertEqual($form_int['error'], $pear_error->getMessage());



		$form_float = array(
							'type'          => VAR_TYPE_FLOAT,
							'required'      => true,
							'min'           => '10.000000',
							'error'         => '{form}には10.000000以上の数字(小数)を入力して下さい'
							);
		$vld->af->setDef('namae_float', $form_float);

		$pear_error = $vld->validate('namae_float', 10.0, $form_float);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_float', '', $form_float);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		// minより小さい値
		$pear_error = $vld->validate('namae_float', 9.11, $form_float);
		$this->assertTrue(is_a($pear_error, 'PEAR_Error'));
		$this->assertEqual(E_FORM_MIN_FLOAT, $pear_error->getCode());
		$this->assertEqual($form_float['error'], $pear_error->getMessage());

		// minより小さい値
		$pear_error = $vld->validate('namae_float', 9, $form_float);
		$this->assertTrue(is_a($pear_error, 'PEAR_Error'));
		$this->assertEqual(E_FORM_MIN_FLOAT, $pear_error->getCode());
		$this->assertEqual($form_float['error'], $pear_error->getMessage());



		$form_string = array(
							 'type'          => VAR_TYPE_STRING,
							 'required'      => true,
							 'min'           => '2',
							 'error'         => '{form}は全角2文字以上(半角1文字以上)で入力して下さい'
							 );
		$vld->af->setDef('namae_string', $form_string);

		$pear_error = $vld->validate('namae_string', 'ddd', $form_string);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_string', '', $form_string);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_string', 'd', $form_string);
		$this->assertTrue(is_a($pear_error, 'PEAR_Error'));
		$this->assertEqual(E_FORM_MIN_STRING, $pear_error->getCode());
		$this->assertEqual($form_string['error'], $pear_error->getMessage());

		// minを短い文字列長
		$pear_error = $vld->validate('namae_string', 8, $form_string);
		$this->assertTrue(is_a($pear_error, 'PEAR_Error'));
		$this->assertEqual(E_FORM_MIN_STRING, $pear_error->getCode());
		$this->assertEqual($form_string['error'], $pear_error->getMessage());



		$form_datetime = array(
							   'type'          => VAR_TYPE_DATETIME,
							   'required'      => true,
							   'min'           => '-1 day',
							   'error'         => '{form}には-1 day以降の日付を入力して下さい'
							   );
		$vld->af->setDef('namae_datetime', $form_datetime);

		$pear_error = $vld->validate('namae_datetime', '+2 day', $form_datetime);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_datetime', '-1 day', $form_datetime);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		$pear_error = $vld->validate('namae_datetime', '', $form_datetime);
		$this->assertFalse(is_a($pear_error, 'PEAR_Error'));

		// minより古い日付
		$pear_error = $vld->validate('namae_datetime', '-3 day', $form_datetime);
		$this->assertTrue(is_a($pear_error, 'PEAR_Error'));
		$this->assertEqual(E_FORM_MIN_DATETIME, $pear_error->getCode());
		$this->assertEqual($form_datetime['error'], $pear_error->getMessage());

	}
}
?>
