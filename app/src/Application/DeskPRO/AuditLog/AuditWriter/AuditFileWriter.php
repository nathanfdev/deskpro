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

namespace Application\DeskPRO\AuditLog\AuditWriter;

use Application\DeskPRO\Entity\AuditLog;

class AuditFileWriter implements AuditWriterInterface
{
    /**
     * @var string
     */
    private $file_path;

    /**
     * @param string $file_path
     */
    public function __construct($file_path)
    {
        $this->file_path = $file_path;
    }


    /**
     * Write a log entry
     *
     * @param  \Application\DeskPRO\Entity\AuditLog[] $log
     * @throws \Exception
     * @return void
     */
    public function writeLogs(array $logs)
    {
        $fp = fopen($this->file_path, 'a');
        if (!$fp) {
            throw new \Exception("Could not open log file for writing: " . $this->file_path);
        }

        foreach ($logs as $log) {
            $str = $this->_formatLog($log);
            fwrite($fp, $str);
            fwrite($fp, "\n");
        }

        fclose($fp);
    }


    /**
     * @param  AuditLog $log
     * @return string
     */
    private function _formatLog(AuditLog $log)
    {
        $str = '[' . date('Y-m-d H:i:s') . '] ' . $log->getObjectName() . ' ' . $log->op . ' by ' . $log->person_name;
        if ($log->data) {
            foreach ($log->data as $row) {
                $new_val = isset($row['new_val']) && $row['new_val'] ? $row['new_val'] : 'none';
                if ($row['type'] == AuditLog::UPDATE && $row['old_val']) {
                    $str .= "\n\t{$row['field_id']} = {$new_val} (from {$row['old_val']})";
                } elseif ($row['type'] == AuditLog::UPDATE) {
                    $str .= "\n\t{$row['field_id']} = {$new_val}";
                }
            }
        }
        $str = trim($str);

        return $str;
    }
}
