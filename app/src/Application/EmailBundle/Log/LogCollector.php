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
 * @subpackage
 */

namespace Application\EmailBundle\Log;

use Application\EmailBundle\Queue\QueueProc;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;

/**
 * The log collector intercepts log messages and sorts them into an array keyed
 * by a sendmail_source id. The ID should be in the 'sendmail_source_id'
 * key of the extra array.
 */
class LogCollector implements HandlerInterface, LogCollectorInterface
{
    /**
     * How many messages to keep in memory at once. Usually you only need
     * one (because it is saved right after).
     */
    private $max_msg_keep = 5;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var array
     */
    private $processors = array();

    /**
     * Log lines that happen while sending a message.
     * This is an array of messageId=>array(lines)
     * @var array
     */
    private $msg_lines = array();

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (empty($record['extra']['sendmail_source_id'])) {
            if (QueueProc::$__dp_current_sendmail) {
                $record['extra']['sendmail_source_id'] = QueueProc::$__dp_current_sendmail['id'];
            }
        }

        if (empty($record['extra']['sendmail_source_id'])) {
            return false;
        }

        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

        return false;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        $id = $record['extra']['sendmail_source_id'];

        if (!isset($this->msg_lines[$id])) {
            $this->msg_lines[$id] = array();
            while (count($this->msg_lines) > $this->max_msg_keep) {
                array_shift($this->msg_lines[$id]);
            }
        }

        $this->msg_lines[$id][] = $record;
    }

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        return $record;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        if (empty($record['extra']['sendmail_source_id']) && !QueueProc::$__dp_current_sendmail) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    /**
     * Closes the handler.
     *
     * This will be called automatically when the object is destroyed
     */
    public function close()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function pushProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }
        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function popProcessor()
    {
        if (!$this->processors) {
            throw new \LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    /**
     * Gets the default formatter.
     *
     * @return FormatterInterface
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");
    }

    /**
     * Get the log for a message ref.
     *
     * @param $source_id
     * @return string
     */
    public function getLogForMessage($source_id)
    {
        if (isset($this->msg_lines[$source_id])) {
            $ret = implode("\n", array_map(function($r) {
                return trim($r['formatted']);
            }, $this->msg_lines[$source_id]));
            return $ret;
        } else {
            return "";
        }
    }
}