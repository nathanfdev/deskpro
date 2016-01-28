<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace Application\DeskPRO\Log\Writer;

use Application\DeskPRO\App;
use DeskPRO\Kernel\KernelErrorHandler;

class LogItemEntity extends \Orb\Log\Writer\AbstractWriter
{
    private $rows_info     = array();
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

            $this->rows_info[] = array('size' => $data_len + $message_len, 'row' => array(
                'log_name'      => $log_item->getLogName(),
                'session_name'  => $log_item->getSessionName(),
                'message'       => $message,
                'priority'      => $log_item->getPriority(),
                'priority_name' => $log_item->getPriorityName(),
                'date_created'  => $log_item->getDatetime()->format('Y-m-d H:i:s'),
                'flag'          => $log_item->getFlag() ?: null,
                'data'          => $data,
            ));

            if (isset($this->rows_info[$this->auto_flush_at])) {
                $this->flush();
            }
        } catch (\Exception $e) {
            KernelErrorHandler::logException($e, false);
        }
    }

    public function flush()
    {
        try {
            $max_size = $this->getMaxSize();

            $rows            = array_reverse($this->rows_info);
            $this->rows_info = array();

            while ($rows) {
                $size  = 0;
                $count = 0;
                $batch = array();
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
            KernelErrorHandler::logException($e, false);
        }
    }
}
