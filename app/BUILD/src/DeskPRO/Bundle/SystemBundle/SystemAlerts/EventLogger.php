<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\ExceptionEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use Doctrine\ORM\EntityManager;

/**
 * Class EventLogger.
 */
class EventLogger
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * EventLogger constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Exception|Event $event_or_exception
     * @param bool             $aloud              Whether to print the event description
     * @param bool|null        $halt               Whether to halt execution
     */
    public function log($event_or_exception, $aloud = false, $halt = null)
    {
        if ($event_or_exception instanceof SuccessEvent) {
            return $this->logSuccess($event_or_exception);
        }

        $event = $this->ensureEvent($event_or_exception);
        $this->em->persist($event);
        $this->em->flush($event);

        if ($aloud) {
            echo (string) $event, "\n";
        }

        !is_null($halt) or $halt = $aloud;
        if ($halt) {
            die();
        }
    }

    /**
     * @param \Exception|Event $event_or_exception
     */
    public function logAloud($event_or_exception)
    {
        $this->log($event_or_exception, true, false);
    }

    /**
     * @param \Exception|Event $event_or_exception
     */
    public function halt($event_or_exception)
    {
        $this->log($event_or_exception, true, true);
    }

    /**
     * Conditionally logs a success event.
     *
     * While events log primarily exists to track failures, it also needs to track those successful events which
     * break series of failures, so that we can determine if an issue was fixed. So as we don't want to track all
     * successful events, but need them only if there was a failure, this method checks which one was the last in
     * the events log and stores an event only if the previous one was a failure.
     *
     * -----------------------------------------------------------------------------------------------------------------
     * For future: this method is going to be called frequently, so worth trying to change it to a single conditional
     * insert for performance reasons.
     *
     * Something like:
     *
     * INSERT INTO `events` (type, date_created)
     *     SELECT ($success_type, $now)
     *     FROM `events`
     *     WHERE (
     *         (SELECT MAX(id) FROM `events` WHERE type = $failure_type)
     *         >
     *         (SELECT MAX(id) FROM `events` WHERE type = $success_type)
     *     )
     *     LIMIT 1
     * -----------------------------------------------------------------------------------------------------------------
     *
     * @param SuccessEvent $event
     */
    private function logSuccess(SuccessEvent $event)
    {
        $success_type = get_class($event);
        $failure_type = $event->getFailureType();
        $success      = $this->em->getRepository($success_type)->findOneBy([], ['id' => 'desc']);
        $failure      = $this->em->getRepository($failure_type)->findOneBy([], ['id' => 'desc']);

        if (($failure && !$success) || ($failure && $success && ($failure->getId() > $success->getId()))) {
            $this->em->persist($event);
            $this->em->flush($event);
        }
    }

    /**
     * @param \Exception|Event $event_or_exception
     *
     * @throws \Exception
     *
     * @return Event
     */
    private function ensureEvent($event_or_exception)
    {
        if ($event_or_exception instanceof \Exception) {
            $event = new ExceptionEvent($event_or_exception);
        } elseif ($event_or_exception instanceof Event) {
            $event = $event_or_exception;
        } else {
            if ($type = gettype($event_or_exception) === 'object') {
                $type = get_class($event_or_exception);
            }
            throw new \Exception(
                'EventLogger::log() can accept either an Exception or Event instance, got '.$type);
        }

        return $event;
    }
}
