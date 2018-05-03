<?php

/**
 * Orb.
 */

namespace Orb\Mail;

use Orb\Mail\QueueProcessor\QueueProcessorInterface;

/**
 * Queue mail transport.
 */
class QueueTransport extends \Orb\Mail\Transport\QueueTransport
{
    /**
     * @param QueueProcessorInterface $queue_processor
     */
    public function __construct(QueueProcessorInterface $queue_processor)
    {
        static $done_reg = false;
        if (!$done_reg) {
            $done_reg = true;
            Swift_DependencyContainer::getInstance()->register('transport.queue')
                ->asNewInstanceOf('Orb\\Mail\\Transport\\QueueTransport')
                ->withDependencies(['transport.eventdispatcher']);
        }

        $arguments = \Swift_DependencyContainer::getInstance()->createDependenciesFor('transport.queue');
        array_unshift($arguments, $queue_processor);

        call_user_func_array([$this, 'Orb\\Mail\\Transport\\QueueTransport::__construct'], $arguments);
    }

    /**
     * @param QueueProcessorInterface $queue_processor
     *
     * @return QueueTransport
     */
    public static function newInstance(QueueProcessorInterface $queue_processor)
    {
        return new self($queue_processor);
    }
}
