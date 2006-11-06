<?php
/**
 *  Ethna_Plugin_Generator_Project_Test.php
 */

/**
 *  Ethna_Plugin_Generator_Projectクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Generator_Project_Test extends UnitTestCase
{
	function rm($path){
		if (is_dir($path)) {
			if ($handle = opendir($path)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$this->rm("$path/$file");
					}
				}
				closedir($handle);
			}
			if (!rmdir($path)) {
				printf("fail rmdir[$path]\n");
			}
		} else {
			if (!unlink($path)) {
				printf("fail unlink[$path]\n");
			}
		}
	}

    function testGeneratorProject()
    {
		$ctl =& Ethna_Controller::getInstance();
		$plugin =& $ctl->getPlugin();
		$gen = $plugin->getPlugin('Generator', 'Project');

		$id = 'idnet';
		$basedir = "bd";
		if (is_dir($basedir)) {	// 新規ディレクトリでテストするため
			$this->rm($basedir);
		}
		mkdir($basedir, 0775);
        ob_start();
        $ret = $gen->generate($id, $basedir);
        $ob = ob_get_clean();
		$this->assertTrue($ret);

		$this->assertTrue(Ethna_Controller::checkAppId($id));
		$this->assertTrue(is_dir("$basedir/$id"));

        $dir_list = array(
            array("app", 0755),
            array("app/action", 0755),
            array("app/action_cli", 0755),
            array("app/action_xmlrpc", 0755),
            array("app/filter", 0755),
            array("app/plugin", 0755),
            array("app/plugin/Filter", 0755),
            array("app/plugin/Validator", 0755),
            array("app/view", 0755),
            array("bin", 0755),
            array("etc", 0755),
            array("lib", 0755),
            array("locale", 0755),
            array("locale/ja", 0755),
            array("locale/ja/LC_MESSAGES", 0755),
            array("log", 0777),
            array("schema", 0755),
            array("skel", 0755),
            array("template", 0755),
            array("template/ja", 0755),
            array("tmp", 0777),
            array("www", 0755),
            array("www/css", 0755),
            array("www/js", 0755),
        );

		// 作成したディレクトリのパーミッションを確認
		foreach ($dir_list as $dir) {
			$dirname = "$basedir/$id/$dir[0]";
			$stat = stat($dirname);
			$this->assertEqual($stat["mode"] & 0000777, $dir[1]);
		}

		$macro['application_id'] = strtoupper($id);
        $macro['project_id'] = ucfirst($id);
        $macro['project_prefix'] = strtolower($id);
        $macro['basedir'] = realpath($basedir);

        $macro['action_class'] = '{$action_class}';
        $macro['action_form'] = '{$action_form}';
        $macro['action_name'] = '{$action_name}';
        $macro['action_path'] = '{$action_path}';
        $macro['forward_name'] = '{$forward_name}';
        $macro['view_name'] = '{$view_name}';
        $macro['view_path'] = '{$view_path}';

        $user_macro = $gen->_getUserMacro();
        $default_macro = $macro;
        $macro = array_merge($macro, $user_macro);

		$dirpath = "$basedir/$id";

		$skel_array =
			array(
				  "$dirpath/www/index.php",
				  "$dirpath/www/info.php",
				  "$dirpath/www/unittest.php",
				  "$dirpath/www/xmlrpc.php",
				  "$dirpath/www/css/ethna.css",
				  "$dirpath/.ethna",
				  sprintf("$dirpath/app/%s_Controller.php", $macro['project_id']),
				  sprintf("$dirpath/app/%s_Error.php", $macro['project_id']),
				  sprintf("$dirpath/app/%s_ActionClass.php", $macro['project_id']),
				  sprintf("$dirpath/app/%s_ActionForm.php", $macro['project_id']),
				  sprintf("$dirpath/app/%s_ViewClass.php", $macro['project_id']),
				  "$dirpath/app/action/Index.php",
				  sprintf("$dirpath/app/plugin/Filter/%s_Plugin_Filter_ExecutionTime.php", $macro['project_id']),
				  "$dirpath/app/view/Index.php",
				  sprintf("$dirpath/app/%s_UnitTestManager.php", $macro['project_id']),
				  sprintf("$dirpath/app/%s_UrlHandler.php", $macro['project_id']),
				  sprintf("$dirpath/etc/%s-ini.php", $macro['project_prefix']),
				  sprintf("$dirpath/skel/skel.action.php"),
				  sprintf("$dirpath/skel/skel.action_cli.php"),
				  sprintf("$dirpath/skel/skel.action_test.php"),
				  sprintf("$dirpath/skel/skel.app_object.php"),
				  sprintf("$dirpath/skel/skel.cli.php"),
				  sprintf("$dirpath/skel/skel.view.php"),
				  sprintf("$dirpath/skel/skel.template.tpl"),
				  sprintf("$dirpath/skel/skel.view_test.php"),
				  sprintf("$dirpath/template/ja/index.tpl"),
				  );

		// ファイルが作成されたことをテスト
		foreach ($skel_array as $skel) {
			$this->assertTrue(is_file($skel));
		}

		// 後片付け
		$this->rm($basedir);

		// ディレクトリ作成の質問に'y'以外で答えた場合
        ob_start();
		$error = $gen->generate($id, $basedir);
        $ob = ob_get_clean();
		$this->assertTrue(is_a($error, 'Ethna_Error'));
		$this->assertEqual($error->getMessage(),
						   'aborted by user');

	}
}
?>
