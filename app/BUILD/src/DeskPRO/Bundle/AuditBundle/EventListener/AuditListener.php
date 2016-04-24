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

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AuditListener.
 */
class AuditListener
{
    const INSERT = 'insert';
    const UPDATE = 'update';
    const REMOVE = 'remove';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UnitOfWork
     */
    private $uow;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * AuditListener constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em  = $eventArgs->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        $this->processScheduledInserts();
        $this->processScheduledUpdates();
    }

    /**
     *
     */
    private function processScheduledInserts()
    {
        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            $this->uow->getEntityChangeSet($entity);
        }
    }

    /**
     *
     */
    private function processScheduledUpdates()
    {
        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            $changeSet = $this->uow->getEntityChangeSet($entity);

            $logEvent = new LogEvent($entity, self::UPDATE, $changeSet);

            $this->dispatcher->dispatch(LogEvent::PRE_LOG_EVENT, $logEvent);
            if ($logEvent->isShouldLog()) {
                $logEvent = new LogEvent($entity, self::UPDATE, $changeSet); // need this, cause propagation is stopped
                $log      = new AuditLog();

                /** @var ClassMetadataInfo $metadata */
                $metadata = $this->em
                    ->getMetadataFactory()
                    ->getMetadataFor(ClassUtils::getRealClass(get_class($entity)));

                $logEvent->setLog($log)->setMetadata($metadata);

                $this->dispatcher->dispatch(LogEvent::START_LOG_EVENT, $logEvent);
                $log->setApiKey(0);

                $this->dispatcher->dispatch(LogEvent::FINISH_LOG_EVENT, $logEvent);
            }
        }
    }
}
