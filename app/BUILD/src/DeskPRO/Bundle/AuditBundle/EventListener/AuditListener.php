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

use DeskPRO\Bundle\AuditBundle\Configuration\AuditContext;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Bundle\AuditBundle\Log\AuditLogHelper;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
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
     * @var AuditLogHelper
     */
    private $auditLogHelper;

    /**
     * @var array
     */
    private $insertions = [];

    /**
     * @var array
     */
    private $updates = [];

    /**
     * @var array
     */
    private $deletions = [];

    /**
     * AuditListener constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param AuditLogHelper           $auditLogHelper
     */
    public function __construct(EventDispatcherInterface $dispatcher, AuditLogHelper $auditLogHelper)
    {
        $this->dispatcher     = $dispatcher;
        $this->auditLogHelper = $auditLogHelper;
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em  = $eventArgs->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        $this->insertions = $this->uow->getScheduledEntityInsertions();
        $this->updates    = $this->uow->getScheduledEntityUpdates();
        $this->deletions  = $this->uow->getScheduledEntityDeletions();
        $this->processDeletions();
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $this->processInsertions();
        $this->processUpdates();
    }

    /**
     * Because we want to log entities are really inserted into DB to know their ID.
     */
    private function processInsertions()
    {
        foreach ($this->insertions as $entity) {
            $this->doProcess(self::INSERT, $entity);
        }
    }

    /**
     *
     */
    private function processDeletions()
    {
        foreach ($this->deletions as $entity) {
            $this->doProcess(self::REMOVE, $entity);
        }
    }

    /**
     *
     */
    private function processUpdates()
    {
        foreach ($this->updates as $entity) {
            $this->doProcess(self::UPDATE, $entity);
        }
    }

    /**
     * @param $action
     * @param $entity
     *
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    private function doProcess($action, $entity)
    {
        $changeSet = $this->getChangesSet($action, $entity);

        $context  = $this->createContext($entity, $action, $changeSet);
        $logEvent = new LogEvent($context);

        $this->dispatcher->dispatch(LogEvent::PRE_LOG_EVENT, $logEvent);
        if ($logEvent->isShouldLog()) {
            $logEvent = new LogEvent($context); // need this, cause propagation is stopped
            $log      = $this->auditLogHelper->createAuditLog();

            /** @var ClassMetadataInfo $metadata */
            $metadata = $this->em
                ->getMetadataFactory()
                ->getMetadataFor(ClassUtils::getRealClass(get_class($entity)));

            $logEvent->setLog($log)->setMetadata($metadata);

            $this->dispatcher->dispatch(LogEvent::START_LOG_EVENT, $logEvent);
            $this->dispatcher->dispatch(LogEvent::FINISH_LOG_EVENT, $logEvent);
        }
    }

    /**
     * @param $action
     * @param $entity
     *
     * @return array
     */
    private function getChangesSet($action, $entity)
    {
        switch ($action) {
            case self::INSERT:
            case self::UPDATE:
                return $this->uow->getEntityChangeSet($entity);
            case self::REMOVE:
                $data      = $this->uow->getOriginalEntityData($entity);
                $changeSet = array_filter(
                    $data,
                    function ($item) {
                        return is_scalar($item);
                    }
                );
                foreach ($changeSet as &$item) {
                    $item = [$item, null];
                }

                return $changeSet;
            default:
                throw new \LogicException('The action you want to process in unsupported!');
        }
    }

    /**
     * @param object $entity    this could be any object Doctrine trying to persist
     * @param string $action
     * @param array  $changeSet
     *
     * @return AuditContext
     */
    private function createContext($entity, $action, $changeSet = [])
    {
        return new AuditContext($action, $this->auditLogHelper->getPerformer(), $entity, $changeSet);
    }
}
