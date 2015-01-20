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

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;

/**
 * We have loggers for the main mailer, the transport used to save sources,
 * and then on each individual transport.
 *
 * This log collector handles messages from all of these loggers so we
 * can easily fetch messages for a given message (e.g., debug text).
 */
class LogCollector implements HandlerInterface, LogCollectorInterface
{
    static private $line_id = 0;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var array
     */
    private $processors = array();

    /**
     * If a message is currently being sent, this is the message ID key
     * @var string|null
     */
    private $active_message_id = null;

    /**
     * Keeps the ID of the last mailer. We prepend mailer init messages
     * when formulating the full log for a message.
     *
     * @var null|int
     */
    private $active_mailer_id = null;

    /**
     * Log lines from the mailer during init.
     * @var array
     */
    private $mailer_init_lines = array();

    /**
     * Log lines that happen while sending a message.
     * This is an array of messageId=>array(lines)
     * @var array
     */
    private $msg_lines = array();

    /**
     * Array of message lines that happen outside of the above scopes.
     * These lines are prepended to msg_lines when a new message starts
     *
     * @var array
     */
    private $noscope_lines = array();

    public function __construct()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
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
        $record['extra']['dp_line_id'] = ++self::$line_id;

        $oid = null;
        if ((isset($record['context']['mailer']) || isset($record['context']['transport'])) && isset($record['context']['stage']) && $record['context']['stage'] == 'init') {
            if (isset($record['context']['mailer'])) {
                $oid = 'MAILER';
            } else {
                $oid = spl_object_hash($record['context']['transport']);
            }
        }

        if (isset($record['context']['transport'])) {
            $this->active_mailer_id = $oid;
        }

        if (isset($record['context']['source_id'])) {
            $this->initMessageId($record['context']['source_id']);
        }

        if ($oid) {
            if (!isset($this->mailer_init_lines[$oid])) {
                $this->mailer_init_lines[$oid] = array();
            }
            $this->mailer_init_lines[$oid][] = $record;

            if ($this->active_message_id) {
                $this->addMessageLine($record);
            }
        } else {
            $this->addMessageLine($record);
        }

        if (isset($record['context']['message_done'])) {
            $this->active_message_id = null;
            $this->active_mailer_id  = null;
        }
    }

    private function addMessageLine(array $record)
    {
        if ($this->active_message_id) {
            $this->msg_lines[$this->active_message_id][] = $record;
        } else {
            // some actions take place before we have saved a record (e.g., the source hasnt been inserted yet)
            // so these keep track of those lines so they can be prepended to the log correctly once we have an id
            $this->noscope_lines[] = $record;
        }
    }

    private function initMessageId($message_id)
    {
        $this->active_message_id = $message_id;

        if (!isset($this->msg_lines[$message_id])) {
            $this->msg_lines[$message_id] = array_merge(
                !empty($this->mailer_init_lines['MAILER']) ? $this->mailer_init_lines['MAILER'] : array(),
                $this->active_mailer_id && !empty($this->mailer_init_lines[$this->active_mailer_id]) ? $this->mailer_init_lines[$this->active_mailer_id] : array(),
                $this->noscope_lines
            );

            usort($this->msg_lines[$message_id], function($a, $b) {
                return $a['extra']['dp_line_id'] < $b['extra']['dp_line_id'] ? -1 : 1;
            });

            $this->noscope_lines = array();
        }
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