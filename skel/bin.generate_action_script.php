<?php
// vim: foldmethod=marker
/**
 *	generate_action_script.php
 *
 *	@author		yourname
 *	@package	{$project_id}
 *	@version	$Id$
 */
chdir(dirname(__FILE__));
include_once('../app/{$project_id}_Controller.php');

ini_set('max_execution_time', 0);

// {{{ {$project_id}_Action_CliGenerateActionScript
class {$project_id}_Action_CliGenerateActionScript extends Ethna_CLI_ActionClass
{
	/**
	 *	cli_generate_action_scriptアクションの実行
	 *
	 *	@access	public
	 */
	function perform()
	{
		parent::perform();

		if (count($_SERVER['argv']) != 2) {
			return $this->_usage();
		}

		$action_name = $_SERVER['argv'][1];

		printf("generating action script for [%s]...\n", $action_name);

		$sg = new Ethna_SkeltonGenerator();
		$sg->generateActionSkelton($action_name);
	}

	/**
	 *	usageを表示する
	 *
	 *	@access	private
	 */
	function _usage()
	{
		printf("%s [action name]\n", $_SERVER['argv'][0]);
	}
}
// }}}

{$project_id}_Controller::main_CLI('{$project_id}_Controller', 'cli_generate_action_script');
?>
