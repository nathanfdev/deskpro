<?php

namespace Application\DevBundle\Controller;

use \Application\DeskPRO\Build\VersionReader;
use \Application\DeskPRO\Build\Upgrader;

use \Application\DeskPRO\App;

class MainController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	public function indexAction()
	{
		return $this->render('DevBundle:Main:index.php', array(

		));
	}

	public function seeFileAction()
	{
		$file = $_GET['file'];
		$file = \preg_replace('#^' . preg_quote(DP_ROOT, '#') . '/#', '', $file);
		$file = DP_ROOT . '/' . $file;

		if (!file_exists($file)) {
			return $this->createResponse('No such file', 404);
		}

		ob_start();
		highlight_file($file);
		$file = ob_get_clean();

		return $this->createResponse($file);
	}

	public function phpInfoAction()
	{
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();

		return $this->createResponse($phpinfo);
	}

	public function phpTestAction()
	{
		return $this->render('DevBundle:Main:php-test.php', array(

		));
	}

	public function phpTestRunAction()
	{
		if (!empty($_POST['code'])) {
			$php = $_POST['code'];
			$php = preg_replace('#^\s*<\?(php)?#', '', $php);

			eval($php);
		}

		return $this->createResponse('');
	}
}