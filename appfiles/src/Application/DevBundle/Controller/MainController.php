<?php

namespace Application\DevBundle\Controller;

use \Application\DeskPRO\Build\VersionReader;
use \Application\DeskPRO\Build\Upgrader;

use \Application\DeskPRO\App;

class MainController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	public function indexAction()
	{
		return $this->render('DevBundle:Main:index.php.html', array(

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
		return $this->render('DevBundle:Main:php-test.php.html', array(

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

	public function runWorkerJobAction()
	{
		$worker_job = App::getEntityRepository('DeskPRO:WorkerJob')->find(@$_GET['id']);

		$runner = new \Application\DeskPRO\WorkerProcess\Runner\Standard();
		
		$fp = fopen('php://memory', 'r+');
		$runner->setCustomLoggerInit(function ($logger) use ($fp) {
			$out_writer = new \Orb\Log\Writer\Stream($fp);
			$logger->addWriter($out_writer);
		});

		$runner->runJobs(array($worker_job));

		rewind($fp);
		$log_result = stream_get_contents($fp);

		return $this->createResponse('<pre>' . htmlspecialchars($log_result) . '</pre>');
	}
}