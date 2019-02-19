<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

class IncomingEmailSupervisor extends AbstractJob
{
    const DEFAULT_INTERVAL = 300;
    const TIMEOUT_INTERVAL = 1500;

    /**
     * @var array
     */
    private $report = [];

    /**
     * @var int
     */
    private $last_check = 0;

    public function run()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if (!$DP_ENV->getConfig('async_email_processing.collect') || !$DP_ENV->getConfig('async_email_processing.process')) {
            return;
        }

        $this->last_check = App::$container->getSetting('incoming_email_supervisor.last_run', 0);

        if ($this->last_check) {
            $this->_checkEmailAccounts();
            $this->_alertInserted();
            $this->_alertErrors();
            $this->_requeueRetries();

            if ($this->report) {
                $report = implode("\n", $this->report);
                $this->logger->logWarn($report);

                if (defined('DP_TECHNICAL_EMAIL') && DP_TECHNICAL_EMAIL) {
                    $message = App::getMailer()->createMessage();
                    $message->setTo(DP_TECHNICAL_EMAIL);
                    $message->setSubject('Warning: Mail processing notices');

                    $email_str = nl2br(htmlspecialchars($report, \ENT_QUOTES, 'UTF-8'));
                    $message->setBody($email_str, 'text/html');
                    try {
                        App::getMailer()->send($message);
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        $this->last_check = App::$container->getSettingsHandler()->setSetting('incoming_email_supervisor.last_run', time());
    }

    protected function _checkEmailAccounts()
    {
        $db = App::$container->getDb();

        //------------------------------
        // Check for accounts that arent running
        //------------------------------

        $cutoff = date('Y-m-d H:i:s', time() - 600);

        $ids = $db->fetchAllCol("
            SELECT id
            FROM email_accounts
            WHERE date_read_start < ? AND is_enabled = 1 AND account_type = 'tickets' AND is_read_active = 0
        ", [$cutoff]);

        if ($ids) {
            $this->report[] = '[WARNING] The following email accounts have not been read in >= 10 minutes: '.implode(', ', $ids).'. This could indicate that your mail collection routine is not running properly.';
        }

        //------------------------------
        // Check for timeouts
        //------------------------------

        $cutoff = date('Y-m-d H:i:s', time() - self::TIMEOUT_INTERVAL);

        $ids = $db->fetchAllCol('
            SELECT id
            FROM email_accounts
            WHERE is_read_active = 1 AND date_read_start < ?
        ', [$cutoff]);

        if ($ids) {
            $db->updateIn('email_accounts', [
                'is_read_active' => 0,
            ], $ids);
            $this->report[] = '[WARNING] The following email accounts timed-out during a collection task: '.implode(', ', $ids).'. The accounts were re-started.';
        }
    }

    protected function _alertInserted()
    {
        $db = App::$container->getDb();

        //------------------------------
        // Try to re-queue old inserted messages
        // as a way for error-retying
        //------------------------------

        $cutoff = date('Y-m-d H:i:s', $this->last_check - 360);

        $db->beginTransaction();

        $ids = $db->fetchAllCol("
            SELECT id
            FROM email_sources
            WHERE status = 'inserted' AND date_status <= ?
        ", [$cutoff]);

        if ($ids) {
            $db->updateIn('email_sources', [
                'status'      => 'retry',
                'date_status' => date('Y-m-d H:i:s'),
            ], $ids);
        }

        $db->commit();

        //------------------------------
        // Warn about emails taking too long
        //------------------------------

        $cutoff = date('Y-m-d H:i:s', $this->last_check - 900);

        $count = $db->fetchColumn("
            SELECT COUNT(*)
            FROM email_sources
            WHERE status = 'retry' AND date_status BETWEEN ? AND ? AND exec_count = 0
        ", [$cutoff, $this->last_check]);

        if ($count) {
            $this->report[] = "[WARNING] Detected {$count} emails that have been waiting for processing for more than 15 minutes. This could indicate a problem with your mail processing tasks.";
        }

        //------------------------------
        // Error-out emails waiting for too long
        //------------------------------

        $cutoff = date('Y-m-d H:i:s', $this->last_check - 2700);

        $ids = $db->fetchAllCol("
            SELECT id
            FROM email_sources
            WHERE status = 'retry' AND date_status <= ?
        ", [$cutoff]);

        if ($ids) {
            $db->updateIn('email_sources', [
                'status'      => 'error',
                'error_code'  => 'timeout',
                'date_status' => date('Y-m-d H:i:s'),
            ], $ids);

            $count          = count($ids);
            $this->report[] = "[WARNING] Detected {$count} emails that have been in the 'retry' status for a long time. They have been logged as an error::timeout status.";
        }
    }

    protected function _alertErrors()
    {
        $db = App::$container->getDb();

        $cutoff = date('Y-m-d H:i:s', $this->last_check - 600);

        $count = $db->fetchColumn("
            SELECT COUNT(*)
            FROM email_sources
            WHERE status = 'error' AND date_status BETWEEN ? AND ?
        ", [$cutoff, $this->last_check]);

        if ($count) {
            $this->report[] = "[WARNING] Detected {$count} emails marked with an 'error' status.";
        }
    }

    /**
     * Any emails with status 'retry' will be re-inserted into the queue.
     *
     * @param $last_check
     *
     * @throws \Exception
     */
    protected function _requeueRetries()
    {
        $db = App::$container->getDb();

        $cutoff = date('Y-m-d H:i:s', $this->last_check);

        $db->beginTransaction();

        $ids = $db->fetchAllCol("
            SELECT id FROM email_sources
            WHERE status = 'retry'
            AND date_status >= ?
        ", [$cutoff]);

        if ($ids) {
            $db->updateIn('email_sources', ['date_status' => date('Y-m-d H:i:s')], $ids);
        }

        $db->commit();

        if ($ids) {
            /** @var \Application\EmailBundle\Incoming\ProcQueue\ProcQueueInterface $proc */
            $proc    = App::getContainer()->get('in_email.proc_queue');
            $sources = App::$container->getEm()->getRepository('DeskPRO:EmailSource')->getByIds($ids);
            foreach ($sources as $s) {
                $proc->enqueueNewEmail($s);
            }
        }
    }
}
