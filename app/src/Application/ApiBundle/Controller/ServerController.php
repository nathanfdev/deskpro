<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Log\ErrorLog\ErrorLogReader;
use Application\DeskPRO\Server\ApcStatus;
use Application\DeskPRO\Server\CronStatus;
use Application\DeskPRO\ServerFileCheck\ServerFileCheck;
use Application\DeskPRO\ServerMysqlInfo\ServerMysqlInfo;
use Application\DeskPRO\ServerMysqlSortOrder\ServerMysqlSortOrder;
use Application\DeskPRO\ServerReportFile\ServerReportFile;

class ServerController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }


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
        $mysql_info = new ServerMysqlInfo($this->db);

        return $this->createApiResponse(array('server_mysql_info' => $mysql_info->getMysqlInfo()));
    }

    public function getMysqlSchemaDiffAction()
    {
        /**
         * @var \Application\DeskPRO\ServerMysqlInfo\ServerMysqlInfo $server_mysql_info
         */
        $mysql_info = new ServerMysqlInfo($this->db);

        return $this->createApiResponse(array('mysql_schema_diff' => $mysql_info->getSchemaDiff()));
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
    # get Mysql Sort Order Status
    ####################################################################################################################

    public function getMysqlSortOrderStatusAction()
    {
        $server_mysql_sort_order = new ServerMysqlSortOrder($this->settings);

        return $this->createApiResponse($server_mysql_sort_order->getUpdateStatus());
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
            throw ValidationException::create('server_error_logs.clear_all.file_not_writable');
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

        $returned_data         = $server_cron->getTimes();
        $returned_data['jobs'] = $server_cron->getAllForApi();

        return $this->createApiResponse(
            array(
                 'server_cron' => $returned_data
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

        // this is for case when we just cleared cron logs

        if ($page == 0) {

            $page = 1;
        }

        $returned_data['page']      = $page;
        $returned_data['num_pages'] = $server_cron->getPagesCount($job_id, $priority);
        $returned_data['priority']  = $priority;
        $returned_data['job_id']    = $job_id;

        $returned_data['logs'] = $server_cron->getLogs($job_id, $priority, $page);
        $returned_data['jobs'] = $server_cron->getAllForApi();

        return $this->createApiResponse(
            array(
                 'server_cron_logs' => $returned_data
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

        $returned_data['php_vars']                  = $server_file_uploads->getPhpVars();
        $returned_data['effective_max_upload_size'] = $server_file_uploads->getEffectiveMaxUploadSize();
        $returned_data['url_to_learn_php_ini']      = $server_file_uploads->getUrlToLearnPhpIni();
        $returned_data['php_ini_path']              = $server_file_uploads->getPhpIniPath();
        $returned_data['restrictions']              = $server_file_uploads->getRestrictions();
        $returned_data['file_uploader_url']         = $server_file_uploads->getFileUploaderUrl();
        $returned_data['filestorage_method']        = $server_file_uploads->getStorageMethod();
        $returned_data['file_storage_path']         = $server_file_uploads->getFileStoragePath();
        $returned_data['s3_bucket']                 = $this->container->getSetting('core.filestorage_s3_bucket');
        $returned_data['s3_key']                    = $this->container->getSetting('core.filestorage_s3_key');
        $returned_data['s3_secret']                 = $this->container->getSetting('core.filestorage_s3_secret');
        $returned_data['moving_files']              = $server_file_uploads->getMovingFiles();

        return $this->createApiResponse(
            array(
                 'server_file_uploads' => $returned_data
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
        $server_file_uploads->switchStorage($this->in->getArrayValue('options'));

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # switch file storage mechanism status
    ####################################################################################################################

    public function switchFileStorageStatusAction()
    {
        /**
         * @var \Application\DeskPRO\ServerFileUploads\ServerFileUploads $server_file_uploads
         */

        $server_file_uploads = $this->container->getSystemService('server_file_uploads');

        $server_file_uploads->switchStorageStatus();

        return $this->createApiResponse($server_file_uploads->switchStorageStatus());
    }

    ####################################################################################################################
    # list File Check
    ####################################################################################################################

    public function listFileCheckAction()
    {
        $server_file_check = new ServerFileCheck($this->em);

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
        $server_file_check = new ServerFileCheck($this->em);

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
        $server_report_file = new ServerReportFile($this->em);
        $server_report_file->createArchive();
        $server_report_file->outputArchive();
    }

    ####################################################################################################################
    # save integrity file check results
    ####################################################################################################################

    public function saveFileCheckResultsAction()
    {
        $server_report_file = new ServerReportFile($this->em);
        $file_check_results = $this->in->getValue('file_check_results', 'post');

        if (!empty($file_check_results)) {
            $server_report_file->saveFileCheckResults($file_check_results);
        }

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # cron-status
    ####################################################################################################################

    public function cronStatusAction()
    {
        $status = new CronStatus($this->db);

        return $this->createJsonResponse(array(
            'last_run_ts'         => $status->getLastRunTimestamp(),
            'last_run'            => $status->getLastRunDate() ? $status->getLastRunDate()->format('Y-m-d H:i:s') : null,
            'secs_since_last_run' => $status->getSecsSinceLastRun(),
            'is_problem'          => $status->guessIsProblem(),
            'cron_boot_errors'    => $status->getCronBootErrors()
        ));
    }

    ####################################################################################################################
    # error-status
    ####################################################################################################################

    public function errorStatusAction()
    {
        $err_reader = new ErrorLogReader(dp_get_log_dir() . '/error.log');
        $error_count = $err_reader->quickCount();

        $gateway_error_count = $this->em->getRepository('DeskPRO:EmailSource')->countErrorStatus(array('ticket', 'ticketmessage'));
        $sendmail_error_count = $this->db->fetchColumn("SELECT COUNT(*) FROM sendmail_queue WHERE status = 'error'");

        return $this->createJsonResponse(array(
            'error_count'          => $error_count,
            'gateway_error_count'  => $gateway_error_count,
            'sendmail_error_count' => $sendmail_error_count,
        ));
    }


    ####################################################################################################################
    # apc-info
    ####################################################################################################################

    public function apcStatusAction()
    {
        $status = new ApcStatus();
        $data = array(
            'is_enabled'          => $status->isEnabled(),
            'is_problem'          => $status->guessIsProblem(),
            'num_reqs'            => $status->getNumTotalReqs(),
            'num_hits'            => $status->getNumHits(),
            'num_misses'          => $status->getNumMisses(),
            'perc_hit'            => $status->getHitPercent(),
            'perc_hit_str'        => sprintf("%.1f", $status->getHitPercent()),
            'perc_miss'           => $status->getMissPercent(),
            'perc_miss_str'       => sprintf("%.1f", $status->getMissPercent()),
            'mem_total'           => $status->getMemTotal(),
            'mem_used'            => $status->getMemUsed(),
            'mem_free'            => $status->getMemFree(),
            'perc_mem_used'       => $status->getMemUsedPercent(),
            'perc_mem_used_str'   => sprintf("%.1f", $status->getMemUsedPercent()),
            'perc_mem_free'       => $status->getMemUsedPercent(),
            'perc_mem_free_str'   => sprintf("%.1f", $status->getMemFreePercent()),
            'hit_miss_chart_url'  => $status->getHitMissChartUrl(),
            'mem_chart_url'       => $status->getMemChartUrl(),
        );

        return $this->createJsonResponse(array('apc_info' => $data));
    }

    ####################################################################################################################
    # begin-automatic-update
    ####################################################################################################################

    public function beginAutomaticUpdateAction()
    {
        $update_time = $this->container->getSetting('core.upgrade_time');
        if ($update_time) {
            return $this->createApiErrorResponse('already_scheduled', 'An automatic update has already been scheduled. To rescheduled, abort the update first.');
        }

        $mins = $this->in->getUint('minutes');
        if (!$mins) $mins = 0;

        $future = time() + $mins * 60;
        $this->container->getSettingsHandler()->setSetting('core.upgrade_time', $future);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_set_at', time());
        $this->container->getSettingsHandler()->setSetting('core.upgrade_backup_files', $this->in->getInt('backup_files'));
        $this->container->getSettingsHandler()->setSetting('core.upgrade_backup_db', $this->in->getInt('backup_db'));
        $this->container->getSettingsHandler()->setSetting('core.upgrade_error_writeperm', null);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_started', null);

        $this->container->getSettingsHandler()->setSetting('core.helpdesk_disabled_message', $this->in->getString('user_message'));
        @file_put_contents(dp_get_data_dir() . '/helpdesk-offline-message.txt', $this->in->getString('user_message'));

        if ($mins) {
            $agent_chat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
            $agent_ids = array_keys($this->em->getRepository('DeskPRO:Person')->getAgents());
            $agent_chat->sendAgentMessage("Warning: The helpdesk will go down for maintenance in ".$mins." minutes.", $agent_ids, 0);
        }

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # automatic-update-status
    ####################################################################################################################

    public function getAutomaticUpdateStatusAction()
    {
        $update_time = $this->container->getSetting('core.upgrade_time');
        if (!$update_time) {
            return $this->createApiResponse(array('is_scheduled' => false));
        } else {
            return $this->createApiResponse(array(
                'is_scheduled'    => true,
                'start_time'      => $update_time,
                'scheduled_at'    => $this->container->getSetting('core.upgrade_set_at'),
                'backup_files'    => $this->container->getSetting('core.upgrade_backup_files'),
                'backup_db'       => $this->container->getSetting('core.upgrade_backup_db'),
                'is_started'      => $this->container->getSetting('core.upgrade_started'),
                'with_perm_error' => $this->container->getSetting('core.upgrade_error_writeperm')
            ));
        }
    }

    ####################################################################################################################
    # abort-automatic-update
    ####################################################################################################################

    public function abortAutomaticUpdateAction()
    {
        $waiting = $this->container->getSetting('core.upgrade_time');
        if ($waiting) {
            return $this->createApiErrorResponse('already_started', 'The upgrade has already started, it cannot be aborted from here.');
        }

        $this->container->getSettingsHandler()->setSetting('core.upgrade_time', null);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_set_at', null);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_backup_files', null);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_backup_db', null);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_error_writeperm', null);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_started', null);

        return $this->createApiSuccessResponse();
    }
}
