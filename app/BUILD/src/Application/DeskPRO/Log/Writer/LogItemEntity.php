<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Log\Writer;

use Application\DeskPRO\App;
use DpSys\LowError\SystemErrorHandler;

class LogItemEntity extends \Orb\Log\Writer\AbstractWriter
{
    private $rows_info     = [];
    private $auto_flush_at = 50;

    /**
     * @return int
     */
    private function getMaxSize()
    {
        static $max_size = null;
        if ($max_size === null) {
            $max_size = App::getDb()->getMaxPacketSize();
        }

        return $max_size;
    }

    public function _write(\Orb\Log\LogItem $log_item)
    {
        try {
            $message     = $log_item->getMessage();
            $message_len = strlen($message);

            $data     = $log_item->getExtra() ? serialize($log_item->getExtra()) : null;
            $data_len = $data ? strlen($data) : 0;

            $max_size = $this->getMaxSize();
            if (($message_len + $data_len) * 2 >= $max_size) {
                $message     = substr($message, 0, ($max_size - 50) / 2);
                $message_len = strlen($message);

                if (($message_len + $data_len) * 2 >= $max_size) {
                    $data = null;
                }
            }

            $this->rows_info[] = ['size' => $data_len + $message_len, 'row' => [
                'log_name'      => $log_item->getLogName(),
                'session_name'  => $log_item->getSessionName(),
                'message'       => $message,
                'priority'      => $log_item->getPriority(),
                'priority_name' => $log_item->getPriorityName(),
                'date_created'  => $log_item->getDatetime()->format('Y-m-d H:i:s'),
                'flag'          => $log_item->getFlag() ?: null,
                'data'          => $data,
            ]];

            if (isset($this->rows_info[$this->auto_flush_at])) {
                $this->flush();
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, false);
        }
    }

    public function flush()
    {
        try {
            $max_size = $this->getMaxSize();

            $rows            = array_reverse($this->rows_info);
            $this->rows_info = [];

            while ($rows) {
                $size  = 0;
                $count = 0;
                $batch = [];
                do {
                    $r = array_pop($rows);
                    if (!$r) {
                        break;
                    }
                    $batch[] = $r['row'];
                    ++$count;
                    $size += $r['size'];
                } while (($size + ($count * 255)) < $max_size && $count <= 60);

                if ($batch) {
                    App::getDb()->batchInsert('log_items', $batch);
                }
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, false);
        }
    }
}
