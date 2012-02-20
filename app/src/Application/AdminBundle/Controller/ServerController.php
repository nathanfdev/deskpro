<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Orb\Util\Numbers;

/**
 * Server info
 */
class ServerController extends AbstractController
{
	############################################################################
	# phpinfo
	############################################################################

	public function phpinfoAction()
	{
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();

		preg_match('#<body>(.*?)</body>#ms', $phpinfo, $m);
		preg_match('#<style(.*?)</style>#ms', $phpinfo, $m2);

		$phpinfo = $m[1];

		return $this->render('AdminBundle:Server:phpinfo.html.twig', array(
			'phpinfo' => $phpinfo,
		));
	}


	############################################################################
	# mysqlinfo
	############################################################################

	public function mysqlinfoAction()
	{
		try {
			$mysqlinfo = App::getDb()->fetchAllKeyValue("SHOW VARIABLES", array(), 0, 1);
		} catch (\Exception $e) {
			$mysqlinfo = null;
		}

		return $this->render('AdminBundle:Server:mysqlinfo.html.twig', array(
			'mysqlinfo' => $mysqlinfo
		));
	}


	############################################################################
	# mysqlstatus
	############################################################################

	public function mysqlstatusAction()
	{
		try {
			$mysqlprocs = App::getDb()->fetchAll("SHOW PROCESSLIST");
		} catch (\Exception $e) {
			$mysqlprocs = null;
		}

		try {
			$mysqlstatus = App::getDb()->fetchAllKeyValue("SHOW STATUS", array(), 0, 1);
		} catch (\Exception $e) {
			$mysqlstatus = null;
		}

		return $this->render('AdminBundle:Server:mysqlstatus.html.twig', array(
			'mysqlprocs' => $mysqlprocs,
			'mysqlstatus' => $mysqlstatus
		));
	}


	############################################################################
	# error-logs
	############################################################################

	public function errorLogsAction()
	{
		$page = max(1, $this->in->getUint('p'));

		$logs_count = App::getEntityRepository('DeskPRO:LogItem')->getErrorLogsCount($page);
		$logs = App::getEntityRepository('DeskPRO:LogItem')->getErrorLogs($page, 25);
		$pagination = Numbers::getPaginationPages($logs_count, $page, 25, 5);

		return $this->render('AdminBundle:Server:error-logs.html.twig', array(
			'logs_count' => $logs_count,
			'logs' => $logs,
			'pagination' => $pagination
		));
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

		return $this->render('AdminBundle:Server:error-view.html.twig', array(
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

		return $this->render('AdminBundle:Server:error-view.html.twig', array(
			'log' => $log,
			'data_structure' => $data_structure
		));
	}

	public function errorLogsClearAllAction()
	{
		$this->ensureRequestToken('clear_error_logs', 'x');
		$this->db->exec("DELETE FROM log_items WHERE log_name = 'error_log'");

		return $this->redirectRoute('admin_server_error_logs');
	}

	############################################################################
	# test-attachments
	############################################################################

	public function testAttachmentsAction()
	{
		$php_vars = array();

		foreach (array('file_uploads', 'upload_tmp_dir', 'upload_max_filesize', 'post_max_size') as $var) {
			$php_vars[$var] = @ini_get($var);
		}

		$failed = false;
		$attach = false;
		$has_uploaded = false;
		if ($this->in->getBool('test')) {
			$has_uploaded = true;
			$file = $this->request->files->get('file');
			$accept = $this->container->getAttachmentAccepter();

			$error = $accept->getError($file, 'agent');
			if ($error) {
				$failed = $this->container->getTranslator()->phrase('agent.attach_error_' . $error['error_code'], $error);
			} else {
				$attach = $accept->accept($file);
			}
		}

		return $this->render('AdminBundle:Server:test-attachments.html.twig', array(
			'php_vars' => $php_vars,
			'has_uploaded' => $has_uploaded,
			'attach' => $attach,
			'failed' => $failed
		));
	}
}
