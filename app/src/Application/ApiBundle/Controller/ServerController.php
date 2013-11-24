<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\ServerMysqlSortOrder\ServerMysqlSortOrder;
use Application\DeskPRO\Exception\ValidationException;

class ServerController extends AbstractController
{
	####################################################################################################################
	# get Server Reqs
	####################################################################################################################

	public function getServerReqsAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerReqs\ServerReqs $server_reqs
		 */

		$server_reqs = $this->container->getSystemService('server_reqs');

		return $this->createApiResponse(
			array(
				 'server_reqs' => array(
					 'web_checks' => $server_reqs->getWebChecks(),
					 'cli_checks' => $server_reqs->getCliChecks(),
				 ),
			)
		);
	}

	####################################################################################################################
	# get PHP Info
	####################################################################################################################

	public function getPhpInfoAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerPhpInfo\ServerPhpInfo $server_php_info
		 */

		$server_php_info = $this->container->getSystemService('server_php_info');

		return $this->createApiResponse(
			array(
				 'server_php_info' => $server_php_info->getPhpInfo(),
			)
		);
	}

	####################################################################################################################
	# get MySQL Info
	####################################################################################################################

	public function getMysqlInfoAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerMysqlInfo\ServerMysqlInfo $server_mysql_info
		 */

		$server_mysql_info = $this->container->getSystemService('server_mysql_info');

		return $this->createApiResponse(
			array(
				 'server_mysql_info' => $server_mysql_info->getMysqlInfo(),
			)
		);
	}

	####################################################################################################################
	# get MySQL Status
	####################################################################################################################

	public function getMysqlStatusAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerMysqlStatus\ServerMysqlStatus $server_mysql_status
		 */

		$server_mysql_status = $this->container->getSystemService('server_mysql_status');

		return $this->createApiResponse(
			array(
				 'server_mysql_status' => $server_mysql_status->getMysqlStatus(),
			)
		);
	}

	####################################################################################################################
	# get Mysql Sort Order
	####################################################################################################################

	public function getMysqlSortOrderAction()
	{
		$server_mysql_sort_order = new ServerMysqlSortOrder($this->settings);

		return $this->createApiResponse(
			array(
				 'server_mysql_sort_order' => $server_mysql_sort_order->toArray(),
				 'all_collations'          => $server_mysql_sort_order->getCollationsTable(),
			)
		);
	}

	####################################################################################################################
	# save Mysql Sort Order
	####################################################################################################################

	public function saveMysqlSortOrderAction()
	{
		$server_mysql_sort_order = new ServerMysqlSortOrder($this->settings);
		$server_mysql_sort_order->setArray($this->in->getCleanValueArray('server_mysql_sort_order'));
		$server_mysql_sort_order->save();

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# list Error Logs
	####################################################################################################################

	public function listErrorLogsAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerErrorLogs\ServerErrorLogs $server_error_logs
		 */

		$server_error_logs = $this->container->getSystemService('server_error_logs');

		return $this->createApiResponse(
			array(
				 'server_error_logs' => $server_error_logs->getAll()
			)
		);
	}

	####################################################################################################################
	# get Error Logs
	####################################################################################################################

	public function getErrorLogsAction($id)
	{
		/**
		 * @var \Application\DeskPRO\ServerErrorLogs\ServerErrorLogs $server_error_logs
		 */

		$server_error_logs = $this->container->getSystemService('server_error_logs');
		$server_error_log  = $server_error_logs->getById($id);

		if (!$server_error_log) {

			throw $this->createNotFoundException();
		}


		return $this->createApiResponse(
			array(
				 'server_error_log' => $server_error_log
			)
		);
	}

	####################################################################################################################
	# remove Error Logs
	####################################################################################################################

	public function removeErrorLogsAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerErrorLogs\ServerErrorLogs $server_error_logs
		 */

		$server_error_logs = $this->container->getSystemService('server_error_logs');

		if (!$server_error_logs->clearAllErrors()) {

			ValidationException::create('server_error_logs.clear_all.file_not_writable');
		}

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# get Task Queue
	####################################################################################################################

	public function getTaskQueueAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerTaskQueue\ServerTaskQueue $server_task_queue
		 */

		$server_task_queue = $this->container->getSystemService('server_task_queue');

		return $this->createApiResponse(
			array(
				 'server_task_queue' => $server_task_queue->getInfo(),
			)
		);
	}

	####################################################################################################################
	# list Cron
	####################################################################################################################

	public function listCronAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerCron\ServerCron $server_cron
		 */

		$server_cron = $this->container->getSystemService('server_cron');

		$returnedData         = $server_cron->getTimes();
		$returnedData['jobs'] = $server_cron->getAllForApi();

		return $this->createApiResponse(
			array(
				 'server_cron' => $returnedData
			)
		);
	}

	####################################################################################################################
	# logs Cron
	####################################################################################################################

	public function logsCronAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerCron\ServerCron $server_cron
		 */

		$server_cron = $this->container->getSystemService('server_cron');

		$priority = $this->in->getUint('priority');
		$job_id   = $this->in->getString('job_id');
		$page     = $this->in->getUint('page');

		$returnedData['page']      = $page;
		$returnedData['num_pages'] = $server_cron->getPagesCount($job_id, $priority);
		$returnedData['priority']  = $priority;
		$returnedData['job_id']    = $job_id;

		$returnedData['logs'] = $server_cron->getLogs($job_id, $priority, $page);
		$returnedData['jobs'] = $server_cron->getAllForApi();

		return $this->createApiResponse(
			array(
				 'server_cron_logs' => $returnedData
			)
		);
	}

	####################################################################################################################
	# remove Cron
	####################################################################################################################

	public function removeCronAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerCron\ServerCron $server_cron
		 */

		$server_cron = $this->container->getSystemService('server_cron');
		$server_cron->clearAllLogs();

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# get File Uploads
	####################################################################################################################

	public function getFileUploadsAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerFileUploads\ServerFileUploads $server_file_uploads
		 */

		$server_file_uploads = $this->container->getSystemService('server_file_uploads');

		$returnedData['php_vars']                  = $server_file_uploads->getPhpVars();
		$returnedData['effective_max_upload_size'] = $server_file_uploads->getEffectiveMaxUploadSize();
		$returnedData['url_to_learn_php_ini']      = $server_file_uploads->getUrlToLearnPhpIni();
		$returnedData['php_ini_path']              = $server_file_uploads->getPhpIniPath();
		$returnedData['restrictions']              = $server_file_uploads->getRestrictions();
		$returnedData['file_uploader_url']         = $server_file_uploads->getFileUploaderUrl();
		$returnedData['using_file_system']         = $server_file_uploads->isUsingFileSystem();
		$returnedData['file_storage_path']         = $server_file_uploads->getFileStoragePath();
		$returnedData['moving_files']              = $server_file_uploads->getMovingFiles();

		return $this->createApiResponse(
			array(
				 'server_file_uploads' => $returnedData
			)
		);
	}

	####################################################################################################################
	# test upload
	####################################################################################################################

	public function testFileUploadAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerFileUploads\ServerFileUploads $server_file_uploads
		 */

		$server_file_uploads = $this->container->getSystemService('server_file_uploads');

		$file = $this->request->files->get('file');

		return $this->createApiResponse(
			$server_file_uploads->getUploadResults($file)
		);
	}

	####################################################################################################################
	# switch file storage mechanism
	####################################################################################################################

	public function switchFileStorageAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerFileUploads\ServerFileUploads $server_file_uploads
		 */

		$server_file_uploads = $this->container->getSystemService('server_file_uploads');

		$server_file_uploads->switchStorage();

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# list File Check
	####################################################################################################################

	public function listFileCheckAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerFileCheck\ServerFileCheck $server_file_check
		 */

		$server_file_check = $this->container->getSystemService('server_file_check');

		return $this->createApiResponse(
			array(
				 'server_file_check' => $server_file_check->getCount()
			)
		);
	}

	####################################################################################################################
	# get File Check
	####################################################################################################################

	public function getFileCheckAction($id)
	{
		/**
		 * @var \Application\DeskPRO\ServerFileCheck\ServerFileCheck $server_file_check
		 */

		$server_file_check = $this->container->getSystemService('server_file_check');

		return $this->createApiResponse(
			array(
				 'server_file_check' => $server_file_check->getById($id)
			)
		);
	}

	####################################################################################################################
	# get Report File
	####################################################################################################################

	public function getReportFileAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerReportFile\ServerReportFile $server_report_file
		 */

		$server_report_file = $this->container->getSystemService('server_report_file');

		$server_report_file->createArchive();
		$server_report_file->outputArchive();
	}

	####################################################################################################################
	# save integrity file check results
	####################################################################################################################

	public function saveFileCheckResultsAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerReportFile\ServerReportFile $server_report_file
		 */

		$server_report_file = $this->container->getSystemService('server_report_file');
		$file_check_results = $this->in->getValue('file_check_results', 'post');

		if (!empty($file_check_results)) {

			$server_report_file->saveFileCheckResults($file_check_results);
		}

		return $this->createSuccessResponse();
	}
}