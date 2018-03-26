<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Monolog;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Log\Loggable;
use Monolog\Handler\TestHandler;
use Monolog\Logger as BaseLogger;

class Logger extends BaseLogger
{
    /**
     * @var TestHandler
     */
    private $test_handler;

    /**
     * @return bool
     */
    public function hasEnabledSavedMessages()
    {
        return $this->test_handler ? true : false;
    }

    /**
     * @return TestHandler
     */
    private function _createTestHandler()
    {
        $test_handler = new TestHandler();

        return $test_handler;
    }

    /**
     * Enables a local copy of all messages so you can easily fetch messages after
     * (e.g., to save to a separate log after-the-fact).
     *
     * @throws \LogicException
     */
    public function enableSavedMessages()
    {
        if ($this->test_handler) {
            throw new \LogicException('Saved messages has already been enabled.');
        }

        $this->test_handler = $this->_createTestHandler();
        $this->pushHandler($this->test_handler);
    }

    /**
     * Clears any saved messages.
     *
     * @throws \LogicException
     */
    public function clearSavedMessges()
    {
        if (!$this->test_handler) {
            throw new \LogicException('Saved messages has not been enabled.');
        }

        $k                  = array_search($this->test_handler, $this->handlers, true);
        $this->test_handler = $this->_createTestHandler();
        $this->handlers[$k] = $this->test_handler;
    }

    /**
     * Gets a string of all the logged messages.
     *
     * @throws \LogicException
     *
     * @return string
     */
    public function getSavedMessages()
    {
        if (!$this->test_handler) {
            throw new \LogicException('Saved messages has not been enabled.');
        }

        $log = [];
        foreach ($this->test_handler->getRecords() as $record) {
            if (!empty($record['formatted'])) {
                $log[] = $record['formatted'];
            } elseif (!empty($record['message'])) {
                $log[] = $record['message'];
            }
        }

        $log = trim(implode('', $log));

        return $log;
    }

    /**
     * Gets an array of raw records.
     *
     * @throws \LogicException
     *
     * @return array
     */
    public function getSavedMessagesRaw()
    {
        if (!$this->test_handler) {
            throw new \LogicException('Saved messages has not been enabled.');
        }

        return $this->test_handler->getRecords();
    }

    /**
     * {@inheritdoc}
     *
     * @param int    $level
     * @param string $message
     * @param array  $context
     *
     * @return bool|void
     */
    public function addRecord($level, $message, array $context = [])
    {
        if ($message instanceof Loggable) {
            $context = array_merge($context, $message->context());
        }

        if ($message instanceof DomainObject) {
            $context['_entity'] = $message;
        }

        return parent::addRecord($level, $message, $context);
    }
}
