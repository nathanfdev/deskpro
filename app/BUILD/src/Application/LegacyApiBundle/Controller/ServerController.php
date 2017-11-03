<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Email\EmailAccount\EmailAccountUtil;
use Application\DeskPRO\Encryption\DpEnc;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Log\ErrorLog\ErrorLogReader;
use Application\DeskPRO\Server\ApcStatus;
use Application\DeskPRO\Server\CronStatus;
use Application\DeskPRO\Server\ServerPhpInfo;
use Application\DeskPRO\ServerCron\ServerCron;
use Application\DeskPRO\ServerErrorLogs\ServerErrorLogs;
use Application\DeskPRO\ServerFileCheck\ServerFileCheck;
use Application\DeskPRO\ServerFileUploads\ServerFileUploads;
use Application\DeskPRO\ServerMysqlInfo\ServerMysqlInfo;
use Application\DeskPRO\ServerMysqlSortOrder\ServerMysqlSortOrder;
use Application\DeskPRO\ServerMysqlStatus\ServerMysqlStatus;
use Application\DeskPRO\ServerReportFile\ServerReportFile;
use Application\DeskPRO\ServerTaskQueue\ServerTaskQueue;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DpRun\DpEnv;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiModes("all")
 */
class ServerController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // get Server Reqs
    //###################################################################################################################

    public function getServerReqsAction(Request $request)
    {
        /* @var DpEnv $DP_ENV */
        global $DP_ENV;

        $auth = $DP_ENV->getDatManager()->readTxtFile('server_info_auth', '');
        $url  = $request->getUriForPath('/__serverinfo/check_requirements?auth='.$auth);

        return $this->createApiResponse(['check_requirements_url' => $url]);
    }

    //###################################################################################################################
    // get PHP Info
    //###################################################################################################################

    public function getPhpInfoAction()
    {
        /** @var ServerPhpInfo $serverPhpInfo */
        $serverPhpInfo = $this->container->getSystemService('server_php_info');

        return $this->createApiResponse(
            [
                'server_php_info' => $serverPhpInfo->getPhpInfo(),
            ]
        );
    }

    //###################################################################################################################
    // get MySQL Info
    //###################################################################################################################

    public function getMysqlInfoAction()
    {
        /** @var ServerMysqlInfo $mysqlInfo */
        $mysqlInfo = new ServerMysqlInfo($this->db, [
            'default' => $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            'sys'     => $this->getContainer()->get('doctrine.orm.system_entity_manager'),
        ]);

        return $this->createApiResponse(['server_mysql_info' => $mysqlInfo->getMysqlInfo()]);
    }

    public function getMysqlSchemaDiffAction()
    {
        /** @var ServerMysqlInfo $mysqlInfo */
        $mysqlInfo = new ServerMysqlInfo($this->db, [
            'default' => $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            'sys'     => $this->getContainer()->get('doctrine.orm.system_entity_manager'),
        ]);

        return $this->createApiResponse(['mysql_schema_diff' => $mysqlInfo->getSchemaDiff()]);
    }

    //###################################################################################################################
    // get MySQL Status
    //###################################################################################################################

    public function getMysqlStatusAction()
    {
        /** @var ServerMysqlStatus $serverMysqlStatus */
        $serverMysqlStatus = $this->container->getSystemService('server_mysql_status');

        return $this->createApiResponse(
            [
                'server_mysql_status' => $serverMysqlStatus->getMysqlStatus(),
            ]
        );
    }

    //###################################################################################################################
    // get Mysql Sort Order
    //###################################################################################################################

    public function getMysqlSortOrderAction()
    {
        $serverMysqlSortOrder = new ServerMysqlSortOrder($this->settings);

        return $this->createApiResponse(
            [
                'server_mysql_sort_order' => $serverMysqlSortOrder->toArray(),
                'all_collations'          => $serverMysqlSortOrder->getCollationsTable(),
            ]
        );
    }

    //###################################################################################################################
    // save Mysql Sort Order
    //###################################################################################################################

    public function saveMysqlSortOrderAction()
    {
        $serverMysqlSortOrder = new ServerMysqlSortOrder($this->settings);
        $serverMysqlSortOrder->setArray($this->in->getCleanValueArray('server_mysql_sort_order'));
        $serverMysqlSortOrder->save();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // get Mysql Sort Order Status
    //###################################################################################################################

    public function getMysqlSortOrderStatusAction()
    {
        $serverMysqlSortOrder = new ServerMysqlSortOrder($this->settings);

        return $this->createApiResponse($serverMysqlSortOrder->getUpdateStatus());
    }

    //###################################################################################################################
    // list Error Logs
    //###################################################################################################################

    public function listErrorLogsAction()
    {
        /** @var ServerErrorLogs $serverErrorLogs */
        $serverErrorLogs = $this->container->getSystemService('server_error_logs');

        return $this->createApiResponse(
            [
                'path'              => $this->container->get('deskpro.app_env')->getUserLogsDir(),
                'server_error_logs' => $serverErrorLogs->getAll(),
            ]
        );
    }

    //###################################################################################################################
    // get Error Logs
    //###################################################################################################################

    public function getErrorLogsAction($id)
    {
        /** @var ServerErrorLogs $serverErrorLogs */
        $serverErrorLogs = $this->container->getSystemService('server_error_logs');
        $serverErrorLog  = $serverErrorLogs->getById($id);

        if (!$serverErrorLog) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(
            [
                'server_error_log' => $serverErrorLog,
            ]
        );
    }

    //###################################################################################################################
    // remove Error Logs
    //###################################################################################################################

    public function removeErrorLogsAction()
    {
        /** @var ServerErrorLogs $serverErrorLogs */
        $serverErrorLogs = $this->container->getSystemService('server_error_logs');

        if (!$serverErrorLogs->clearAllErrors()) {
            throw ValidationException::create('server_error_logs.clear_all.file_not_writable');
        }

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // get Task Queue
    //###################################################################################################################

    public function getTaskQueueAction()
    {
        /** @var ServerTaskQueue $serverTaskQueue */
        $serverTaskQueue = $this->container->getSystemService('server_task_queue');

        return $this->createApiResponse(
            [
                'server_task_queue' => $serverTaskQueue->getInfo(),
            ]
        );
    }

    //###################################################################################################################
    // list Cron
    //###################################################################################################################

    public function listCronAction()
    {
        /** @var ServerCron $serverCron */
        $serverCron = $this->container->getSystemService('server_cron');

        $returnedData         = $serverCron->getTimes();
        $returnedData['jobs'] = $serverCron->getAllForApi();

        return $this->createApiResponse(
            [
                'server_cron' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // logs Cron
    //###################################################################################################################

    public function logsCronAction()
    {
        /** @var ServerCron $serverCron */
        $serverCron = $this->container->getSystemService('server_cron');

        $priority = $this->in->getUint('priority');
        $jobId    = $this->in->getString('job_id');
        $page     = $this->in->getUint('page');

        // this is for case when we just cleared cron logs

        if ($page == 0) {
            $page = 1;
        }

        $returnedData['page']      = $page;
        $returnedData['num_pages'] = $serverCron->getPagesCount($jobId, $priority);
        $returnedData['priority']  = $priority;
        $returnedData['job_id']    = $jobId;

        $returnedData['logs'] = $serverCron->getLogs($jobId, $priority, $page);
        $returnedData['jobs'] = $serverCron->getAllForApi();

        return $this->createApiResponse(
            [
                'server_cron_logs' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // remove Cron
    //###################################################################################################################

    public function removeCronAction()
    {
        /** @var ServerCron $serverCron */
        $serverCron = $this->container->getSystemService('server_cron');
        $serverCron->clearAllLogs();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // get File Uploads
    //###################################################################################################################

    public function getFileUploadsAction()
    {
        /** @var ServerFileUploads $serverFileUploads */
        $serverFileUploads = $this->container->getSystemService('server_file_uploads');

        $returnedData = [
            'php_vars'                  => $serverFileUploads->getPhpVars(),
            'effective_max_upload_size' => $serverFileUploads->getEffectiveMaxUploadSize(),
            'url_to_learn_php_ini'      => $serverFileUploads->getUrlToLearnPhpIni(),
            'php_ini_path'              => $serverFileUploads->getPhpIniPath(),
            'restrictions'              => $serverFileUploads->getRestrictions(),
            'file_uploader_url'         => $serverFileUploads->getFileUploaderUrl(),
            'filestorage_method'        => $serverFileUploads->getStorageMethod(),
            'file_storage_path'         => $serverFileUploads->getFileStoragePath(),
            's3_bucket'                 => $this->container->getSetting('core.filestorage_s3_bucket'),
            's3_key'                    => $this->container->getSetting('core.filestorage_s3_key'),
            's3_secret'                 => $this->container->getSetting('core.filestorage_s3_secret'),
            's3_region'                 => $this->container->getSetting('core.filestorage_s3_region'),
            's3_endpoint'               => $this->container->getSetting('core.filestorage_s3_endpoint'),
            'moving_files'              => $serverFileUploads->getMovingFiles(),
        ];

        return $this->createApiResponse(
            [
                'server_file_uploads' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // test upload
    //###################################################################################################################

    public function testFileUploadAction()
    {
        /** @var ServerFileUploads $serverFileUploads */
        $serverFileUploads = $this->container->getSystemService('server_file_uploads');

        $file = $this->request->files->get('file');

        return $this->createApiResponse(
            $serverFileUploads->getUploadResults($file)
        );
    }

    //###################################################################################################################
    // switch file storage mechanism
    //###################################################################################################################

    public function switchFileStorageAction()
    {
        /** @var ServerFileUploads $serverFileUploads */
        $serverFileUploads = $this->container->getSystemService('server_file_uploads');
        $serverFileUploads->switchStorage($this->in->getArrayValue('options'));

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // switch file storage mechanism status
    //###################################################################################################################

    public function switchFileStorageStatusAction()
    {
        /** @var ServerFileUploads $serverFileUploads */
        $serverFileUploads = $this->container->getSystemService('server_file_uploads');

        $serverFileUploads->switchStorageStatus();

        return $this->createApiResponse($serverFileUploads->switchStorageStatus());
    }

    //###################################################################################################################
    // list File Check
    //###################################################################################################################

    public function listFileCheckAction()
    {
        $serverFileCheck = new ServerFileCheck($this->em);

        return $this->createApiResponse(
            [
                'server_file_check' => $serverFileCheck->getCount(),
            ]
        );
    }

    //###################################################################################################################
    // get File Check
    //###################################################################################################################

    public function getFileCheckAction($id)
    {
        $serverFileCheck = new ServerFileCheck($this->em);

        return $this->createApiResponse(
            [
                'server_file_check' => $serverFileCheck->getById($id),
            ]
        );
    }

    //###################################################################################################################
    // get Report File
    //###################################################################################################################

    public function getReportFileAction()
    {
        $serverReportFile = new ServerReportFile($this->em, null, $this->get('deskpro.app_env'));
        $serverReportFile->setSystemEntityManager($this->get('doctrine.orm.system_entity_manager'));
        $serverReportFile->setInstructionGenerator($this->get('dp_sys.alerts.instructions_generator'));
        $serverReportFile->createArchive();
        $serverReportFile->outputArchive();
    }

    //###################################################################################################################
    // save integrity file check results
    //###################################################################################################################

    public function saveFileCheckResultsAction()
    {
        $serverReportFile = new ServerReportFile($this->em, null, $this->get('deskpro.app_env'));
        $fileCheckResults = $this->in->getValue('file_check_results', 'post');

        if (!empty($fileCheckResults)) {
            $serverReportFile->saveFileCheckResults($fileCheckResults);
        }

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // cron-status
    //###################################################################################################################

    public function cronStatusAction()
    {
        $status = new CronStatus($this->db);

        return $this->createJsonResponse([
            'last_run_ts'         => $status->getLastRunTimestamp(),
            'last_run'            => $status->getLastRunDate() ? $status->getLastRunDate()->format('Y-m-d H:i:s') : null,
            'secs_since_last_run' => $status->getSecsSinceLastRun(),
            'is_problem'          => $status->guessIsProblem(),
            'cron_boot_errors'    => $status->getCronBootErrors(),
        ]);
    }

    //###################################################################################################################
    // error-status
    //###################################################################################################################

    public function errorStatusAction()
    {
        $errorLogFile = $this->get('deskpro.app_env')->getUserLogsDir().'/error.log';
        $errReader    = new ErrorLogReader($errorLogFile);
        $errorLogSize = false;
        $maxSize      = SystemErrorHandler::$maxErrorLogFileSize;
        try {
            $errorLogSize = is_file($errorLogFile) && filesize($errorLogFile) > $maxSize;
        } catch (\Exception $e) {
        }
        $errorCount = $errReader->quickCount();

        $gatewayErrorCount  = $this->em->getRepository('DeskPRO:EmailSource')->countErrorStatus(['ticket', 'ticketmessage']);
        $sendmailErrorCount = $this->db->fetchColumn("SELECT COUNT(*) FROM sendmail_sources WHERE status = 'error'");

        return $this->createJsonResponse([
            'error_count'          => $errorCount,
            'error_log_size'       => $errorLogSize,
            'error_log_max_size'   => $maxSize,
            'gateway_error_count'  => $gatewayErrorCount,
            'sendmail_error_count' => $sendmailErrorCount,
        ]);
    }

    //###################################################################################################################
    // apc-info
    //###################################################################################################################

    public function apcStatusAction()
    {
        $status = new ApcStatus();
        $data   = [
            'is_enabled'         => $status->isEnabled(),
            'is_problem'         => $status->guessIsProblem(),
            'num_reqs'           => $status->getNumTotalReqs(),
            'num_hits'           => $status->getNumHits(),
            'num_misses'         => $status->getNumMisses(),
            'perc_hit'           => $status->getHitPercent(),
            'perc_hit_str'       => sprintf('%.1f', $status->getHitPercent()),
            'perc_miss'          => $status->getMissPercent(),
            'perc_miss_str'      => sprintf('%.1f', $status->getMissPercent()),
            'mem_total'          => $status->getMemTotal(),
            'mem_used'           => $status->getMemUsed(),
            'mem_free'           => $status->getMemFree(),
            'perc_mem_used'      => $status->getMemUsedPercent(),
            'perc_mem_used_str'  => sprintf('%.1f', $status->getMemUsedPercent()),
            'perc_mem_free'      => $status->getMemUsedPercent(),
            'perc_mem_free_str'  => sprintf('%.1f', $status->getMemFreePercent()),
            'hit_miss_chart_url' => $status->getHitMissChartUrl(),
            'mem_chart_url'      => $status->getMemChartUrl(),
        ];

        return $this->createJsonResponse(['apc_info' => $data]);
    }

    //###################################################################################################################
    // begin-automatic-update
    //###################################################################################################################

    public function beginAutomaticUpdateAction()
    {
        $updateTime = $this->container->getSetting('core.upgrade_time');
        if ($updateTime) {
            return $this->createApiErrorResponse('already_scheduled', 'An automatic update has already been scheduled. To rescheduled, abort the update first.');
        }

        $mins = $this->in->getUint('minutes');
        if (!$mins) {
            $mins = 0;
        }

        $future = time() + $mins * 60;
        $this->container->getSettingsHandler()->setSetting('core.upgrade_time', $future);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_set_at', time());
        $this->container->getSettingsHandler()->setSetting('core.upgrade_backup_files', $this->in->getInt('backup_files'));
        $this->container->getSettingsHandler()->setSetting('core.upgrade_backup_db', $this->in->getInt('backup_db'));
        $this->container->getSettingsHandler()->setSetting('core.upgrade_error_writeperm', null);
        $this->container->getSettingsHandler()->setSetting('core.upgrade_started', null);

        $this->container->getSettingsHandler()->setSetting('core.helpdesk_disabled_message', $this->in->getString('user_message'));
        @file_put_contents($this->container->getParameter('dp.user.cache_dir').'/helpdesk-offline-message.txt', $this->in->getString('user_message'));

        if ($mins) {
            $agentChat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
            $agentIds  = array_keys($this->em->getRepository('DeskPRO:Person')->getAgents());
            $agentChat->sendAgentMessage('Warning: The helpdesk will go down for maintenance in '.$mins.' minutes.', $agentIds, 0);
        }

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // automatic-update-status
    //###################################################################################################################

    public function getAutomaticUpdateStatusAction()
    {
        $updateTime = $this->container->getSetting('core.upgrade_time');
        if (!$updateTime) {
            return $this->createApiResponse(['is_scheduled' => false]);
        } else {
            return $this->createApiResponse([
                'is_scheduled'    => true,
                'start_time'      => $updateTime,
                'scheduled_at'    => $this->container->getSetting('core.upgrade_set_at'),
                'backup_files'    => $this->container->getSetting('core.upgrade_backup_files'),
                'backup_db'       => $this->container->getSetting('core.upgrade_backup_db'),
                'is_started'      => $this->container->getSetting('core.upgrade_started'),
                'with_perm_error' => $this->container->getSetting('core.upgrade_error_writeperm'),
            ]);
        }
    }

    //###################################################################################################################
    // abort-automatic-update
    //###################################################################################################################

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

    //###################################################################################################################
    // encryption-status
    //###################################################################################################################

    /**
     * @return array
     */
    private function getEncStatus()
    {
        $keyFile        = $this->container->get('deskpro.app_env')->findConfigFile('encryption-key.bin');
        $hasKeyFile     = file_exists($keyFile) && is_readable($keyFile);
        $isEnabled      = $this->container->getSetting('core.use_encryption');
        $canDisableFile = $this->container->get('deskpro.app_env')->findConfigFile('can-disable-encryption.txt');
        $canDisable     = is_file($canDisableFile);

        if (!extension_loaded('openssl')) {
            $isAble      = false;
            $isAbleError = 'Missing the OpenSSL PHP extension.';
        } elseif (!extension_loaded('mcrypt')) {
            $isAble      = false;
            $isAbleError = 'Missing the mcrypt PHP extension.';
        } else {
            $isAble      = true;
            $isAbleError = null;
            try {
                $key = \Crypto::CreateNewRandomKey();
            } catch (\Exception $e) {
                $isAbleError = 'The server was unable to use crypto: '.Util::getBaseClassname($e).' '.$e->getMessage();
                $isAble      = false;
            }
        }

        return [
            'is_able'          => $isAble,
            'unable_error'     => $isAbleError,
            'key_file'         => $keyFile,
            'has_key_file'     => $hasKeyFile,
            'is_enabled'       => (bool) ((int) $isEnabled),
            'can_disable_file' => $canDisableFile,
            'can_disable'      => $canDisable,
        ];
    }

    public function encryptionStatusAction()
    {
        return $this->createApiResponse($this->getEncStatus());
    }

    //###################################################################################################################
    // enable-encryption
    //###################################################################################################################

    public function enableEncryptionAction()
    {
        $status = $this->getEncStatus();

        if (!$status['is_able']) {
            return $this->createApiErrorResponse('unable', 'Your server does not have the required OpenSSL PHP extension');
        }

        if ($status['is_enabled']) {
            return $this->createApiErrorResponse('already_enabled', 'Encryption is already enabled.');
        }

        try {
            $key = base64_encode(\Crypto::CreateNewRandomKey());
        } catch (\Exception $e) {
            return $this->createApiErrorResponse('unable_perform', 'The server was unable to generate a secure key: '.Util::getBaseClassname($e).' '.$e->getMessage());
        }

        if (file_exists($status['key_file'])) {
            @rename($status['key_file'], $status['key_file'].'.old-'.time());
        }

        if (!@file_put_contents($status['key_file'], $key)) {
            return $this->createApiErrorResponse('write_error', 'Unable to write the keyfile (data/encryption-key.bin)');
        }

        @chmod($status['key_file'], 0444);

        $enc = new DpEnc(true, $status['key_file']);

        $accounts = $this->em->getRepository('DeskPRO:EmailAccount')->findAll();

        $this->db->beginTransaction();

        $this->container->getSettingsHandler()->setSetting('core.use_encryption', true);
        foreach ($accounts as $acc) {
            if ($acc->incoming_account) {
                $acc->incoming_account = EmailAccountUtil::encryptIncomingAccount($acc->incoming_account, $enc);
            }
            if ($acc->outgoing_account) {
                $acc->outgoing_account = EmailAccountUtil::encryptOutgoingAccount($acc->outgoing_account, $enc);
            }
            $this->em->persist($acc);
        }

        $this->em->flush();

        $this->db->commit();

        return $this->createApiResponse(['success' => true]);
    }

    public function disableEncryptionAction()
    {
        $status = $this->getEncStatus();

        if (!$status['is_enabled']) {
            return $this->createApiErrorResponse('not_enabled', 'Encryption is not even enabled.');
        }

        if (!$status['can_disable']) {
            return $this->createApiErrorResponse('cannot_disable', 'Missing the file that indicates that encryption can be disabled (data/can-disable-encryption.txt)');
        }

        $enc = new DpEnc(true, $status['key_file']);

        $accounts = $this->em->getRepository('DeskPRO:EmailAccount')->findAll();

        $this->db->beginTransaction();

        $this->container->getSettingsHandler()->setSetting('core.use_encryption', false);
        foreach ($accounts as $acc) {
            if ($acc->incoming_account) {
                $acc->incoming_account = EmailAccountUtil::decryptOutgoingAccount($acc->incoming_account, $enc);
            }
            if ($acc->outgoing_account) {
                $acc->outgoing_account = EmailAccountUtil::decryptOutgoingAccount($acc->outgoing_account, $enc);
            }
            $this->em->persist($acc);
        }

        $this->em->flush();

        $this->db->commit();

        return $this->createApiResponse(['success' => true]);
    }
}
