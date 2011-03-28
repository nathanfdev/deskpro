<?php

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;

use Orb\Util\Numbers;

class LogsController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('AdminBundle:Logs:index.html.twig', array());
	}

    public function errorLogsAction($page)
	{
		$page = max(1, $page);

		$logs_count = App::getEntityRepository('DeskPRO:LogItem')->getErrorLogsCount($page);
		$logs = App::getEntityRepository('DeskPRO:LogItem')->getErrorLogs($page, 25);
		$pagination = Numbers::getPaginationPages($logs_count, $page, 25, 5);

		return $this->render('AdminBundle:Logs:error-logs.html.twig', array(
			'logs_count' => $logs_count,
			'logs' => $logs,
			'pagination' => $pagination
		));
	}

	public function errorLogsClearAllAction()
	{
		App::getEntityRepository('DeskPRO:LogItem')->clearAllErrorLogs();

		return $this->redirectRoute('admin_logs_errors');
	}

	public function viewSnAction($log_sn)
	{
		$log_sn = trim(preg_replace('#^SN#', '', $log_sn));
		
		$log = App::getEntityRepository('DeskPRO:LogItem')->findBySn($log_sn);
		if (!$log) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_log");
		}

		$data_structure = '';
		if ($log['data']) {
			$data_structure = print_r($log['data'], true);
		}

		return $this->render('AdminBundle:Logs:view.html.twig', array(
			'log' => $log,
			'data_structure' => $data_structure
		));
	}

	public function viewAction($log_id)
	{
		$log = App::getEntityRepository('DeskPRO:LogItem')->find($log_id);
		if (!$log) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_log");
		}

		$data_structure = '';
		if ($log['data']) {
			$data_structure = print_r($log['data'], true);
		}

		return $this->render('AdminBundle:Logs:view.html.twig', array(
			'log' => $log,
			'data_structure' => $data_structure
		));
	}
}
